<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpVendorAttributeManager
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
declare(strict_types=1);

namespace Webkul\MpVendorAttributeManager\Model\Resolver;

use Magento\CustomerGraphQl\Model\Customer\CreateCustomerAccount;
use Magento\CustomerGraphQl\Model\Customer\ExtractCustomerData;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Newsletter\Model\Config;
use Magento\Store\Model\ScopeInterface;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\Customer\Mapper;
use Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAttribute\CollectionFactory;

/**
 * Create customer account resolver
 */
class CreateCustomer implements ResolverInterface
{
    /**
     * @var ExtractCustomerData
     */
    private $extractCustomerData;

    /**
     * @var CreateCustomerAccount
     */
    private $createCustomerAccount;

    /**
     * @var Config
     */
    private $newsLetterConfig;

    /**
     * @var \Webkul\MpVendorAttributeManager\Helper\Data
     */
    protected $helper;

    /**
     * @var /Magento\Customer\Api\Data\CustomerInterfaceFactory
     */
    protected $_customerDataFactory;

    /**
     * @var /Magento\Framework\Api\DataObjectHelper
     */
    protected $_dataObjectHelper;

    /**
     * @var \Magento\Customer\Model\Customer\Mapper
     */
    protected $_customerMapper;

    /**
     * @var \Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAttribute\CollectionFactory
     */
    protected $vendorAttributeCollectionFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * CreateCustomer constructor.
     *
     * @param ExtractCustomerData $extractCustomerData
     * @param CreateCustomerAccount $createCustomerAccount
     * @param Config $newsLetterConfig
     * @param \Webkul\MpVendorAttributeManager\Helper\Data $helper
     * @param CustomerInterfaceFactory $customerDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param Mapper $customerMapper
     * @param CustomerRepositoryInterface $customerRepository
     * @param CollectionFactory $vendorAttributeCollectionFactory
     */
    public function __construct(
        ExtractCustomerData $extractCustomerData,
        CreateCustomerAccount $createCustomerAccount,
        Config $newsLetterConfig,
        \Webkul\MpVendorAttributeManager\Helper\Data $helper,
        CustomerInterfaceFactory $customerDataFactory,
        DataObjectHelper $dataObjectHelper,
        Mapper $customerMapper,
        CustomerRepositoryInterface $customerRepository,
        CollectionFactory $vendorAttributeCollectionFactory
    ) {
        $this->newsLetterConfig = $newsLetterConfig;
        $this->extractCustomerData = $extractCustomerData;
        $this->createCustomerAccount = $createCustomerAccount;
        $this->helper = $helper;
        $this->_customerRepository = $customerRepository;
        $this->_customerDataFactory = $customerDataFactory;
        $this->_customerMapper = $customerMapper;
        $this->_dataObjectHelper = $dataObjectHelper;
        $this->vendorAttributeCollectionFactory = $vendorAttributeCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (empty($args['input']) || !is_array($args['input'])) {
            throw new GraphQlInputException(__('"input" value should be specified'));
        }

        if (!$this->newsLetterConfig->isActive(ScopeInterface::SCOPE_STORE)) {
            $args['input']['is_subscribed'] = false;
        }
        if (isset($args['input']['date_of_birth'])) {
            $args['input']['dob'] = $args['input']['date_of_birth'];
        }
        $customer = $this->createCustomerAccount->execute(
            $args['input'],
            $context->getExtensionAttributes()->getStore()
        );

        /** Function to save Mp Vendor Attributes data */
        $this->saveMpVendorAttributes($customer, $args);

        $data = $this->extractCustomerData->execute($customer);
        return ['customer' => $data];
    }

    /**
     * Function saveMpVendorAttributes
     *
     * @param object $customerObj
     * @param array $args
     * @return void
     */
    protected function saveMpVendorAttributes($customerObj, $args)
    {
        if (!empty($args['attributesData'])) {
            if (!is_array($args['attributesData'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Attributes data cannot be empty.')
                );
            }
            $customerId = $customerObj->getId();
            $sellerStatus = 0;

            $attributesData = $args['attributesData'];
            $customerData = [];
            foreach ($attributesData as $data) {
                $customerData[$data['code']] = $data['value'];
            }

            $vendorAttributeCollection = $this->vendorAttributeCollectionFactory->create()
                                            ->getVendorAttributeCollection();

            $error = [];
            $customerData = $this->setBooleanData($customerData, $sellerStatus);
            $customerData = $this->setFileAndImageData($customerData, $sellerStatus);

            foreach ($vendorAttributeCollection as $vendorAttribute) {
                foreach ($customerData as $attributeCode => $attributeValue) {
                    if ($attributeCode == $vendorAttribute->getAttributeCode()) {
                        if ($vendorAttribute->getIsRequired() && empty($attributeValue)) {
                            $error[] = $vendorAttribute->getAttributeCode();
                        }
                    }
                }
            }
            if (!empty($error)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Vendor Required Attributes can\'t be Empty.')
                );
            }
            $savedCustomerData = $this->_customerRepository->getById($customerId);
            $saveData = $this->_customerMapper->toFlatArray($savedCustomerData);

            $customer = $this->_customerDataFactory->create();
            
            $customerData = array_merge(
                $saveData,
                $customerData
            );
            $customerData['id'] = $customerId;
            if (!isset($customerData['is_vendor_group'])) {
                $customerData['is_vendor_group'] = 0;
            }

            $this->_dataObjectHelper->populateWithArray(
                $customer,
                $customerData,
                \Magento\Customer\Api\Data\CustomerInterface::class
            );

            $customer->setData('ignore_validation_flag', true);
            $this->_customerRepository->save($customer);
        }
    }

    /**
     * Set Values for Boolean Type Attribute
     *
     * @param array $customerData
     * @param int $sellerStatus
     * @return void
     */
    protected function setBooleanData($customerData, $sellerStatus)
    {
        $attributeUsedFor = [0,1];
        if ($sellerStatus) {
            $attributeUsedFor = [0,2];
        }
        $booleanAttributes = $this->vendorAttributeCollectionFactory->create()
                                ->getVendorAttributeCollection()
                                ->addFieldToFilter("frontend_input", ['eq' => 'boolean'])
                                ->addFieldToFilter("wk_attribute_status", ['eq' => 1])
                                ->addFieldToFilter("attribute_used_for", ["in" => $attributeUsedFor]);

        if ($booleanAttributes->getSize()) {
            foreach ($booleanAttributes as $attribute) {
                $attributeCode = $attribute->getAttributeCode();
                $givenValue = $customerData[$attributeCode] ?? false;
                $attributeValue = (boolean) $givenValue;
                $customerData[$attributeCode] = $attributeValue;
            }
        }
        return $customerData;
    }

    /**
     * Set Values for File/Image Type Attribute
     *
     * @param array $customerData
     * @param int $sellerStatus
     * @return void
     */
    protected function setFileAndImageData($customerData, $sellerStatus)
    {
        $attributeUsedFor = [0,1];
        if ($sellerStatus) {
            $attributeUsedFor = [0,2];
        }
        $fileTypes = ["image","file"];
        $fileTypeAttributes = $this->vendorAttributeCollectionFactory->create()
                                ->getVendorAttributeCollection()
                                ->addFieldToFilter("frontend_input", ['in' => $fileTypes])
                                ->addFieldToFilter("wk_attribute_status", ['eq' => 1])
                                ->addFieldToFilter("attribute_used_for", ["in" => $attributeUsedFor]);

        if ($fileTypeAttributes->getSize()) {
            foreach ($fileTypeAttributes as $attribute) {
                $attributeCode = $attribute->getAttributeCode();
                if (!empty($customerData[$attributeCode])) {
                    $newFileUrl = $customerData[$attributeCode];
                    $fileType = $attribute->getFrontendInput();

                    list($fileName, $ext) = $this->helper->getFileExtension($newFileUrl);
                    $allowedExtensions = explode(",", $this->helper->getConfigData('allowede_'.$fileType.'_extension'));
                    if (in_array($ext, $allowedExtensions)) {
                        $uploadPath = 'vendorfiles/' . $fileType . '/';
                        $result = $this->helper->createFileFromUrl($newFileUrl, $ext, $uploadPath, $fileName);
                        if ($result) {
                            $customerData[$attributeCode] = $result;
                        } else {
                            throw new \Magento\Framework\Exception\LocalizedException(
                                __('Invalid %1.', $fileType)
                            );
                        }
                    } else {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __(
                                'Invalid %1 type. Please upload valid file type from %2',
                                $fileType,
                                implode(",", $allowedExtensions)
                            )
                        );
                    }
                }
            }
        }
        return $customerData;
    }
}
