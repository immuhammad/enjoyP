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

use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\CustomerGraphQl\Model\Customer\ExtractCustomerData;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\GraphQl\Model\Query\ContextInterface;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\Customer\Mapper;
use Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAttribute\CollectionFactory;

/**
 * SaveMpVendorAttributes field resolver, used for GraphQL request processing.
 */
class SaveMpVendorAttributes implements ResolverInterface
{
    public const SEVERE_ERROR = 0;
    public const SUCCESS = 1;
    public const LOCAL_ERROR = 2;

    /**
     * @var GetCustomer
     */
    private $getCustomer;

    /**
     * @var ExtractCustomerData
     */
    private $extractCustomerData;

    /**
     * @var \Webkul\MpVendorAttributeManager\Helper\Data
     */
    private $helper;

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
     * Constructor
     *
     * @param GetCustomer $getCustomer
     * @param ExtractCustomerData $extractCustomerData
     * @param \Webkul\MpVendorAttributeManager\Helper\Data $helper
     * @param CustomerInterfaceFactory $customerDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param Mapper $customerMapper
     * @param CustomerRepositoryInterface $customerRepository
     * @param CollectionFactory $vendorAttributeCollectionFactory
     */
    public function __construct(
        GetCustomer $getCustomer,
        ExtractCustomerData $extractCustomerData,
        \Webkul\MpVendorAttributeManager\Helper\Data $helper,
        CustomerInterfaceFactory $customerDataFactory,
        DataObjectHelper $dataObjectHelper,
        Mapper $customerMapper,
        CustomerRepositoryInterface $customerRepository,
        CollectionFactory $vendorAttributeCollectionFactory
    ) {
        $this->getCustomer = $getCustomer;
        $this->extractCustomerData = $extractCustomerData;
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
        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        try {
            $returnArray = [];
            if (empty($args['attributesData']) || !is_array($args['attributesData'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Attributes data cannot be empty.')
                );
            }
            $purpose = $args['purpose'];
            $customerId = $context->getUserId();
            $sellerStatus = 0;
            if ($purpose == 'EDIT_SELLER_PROFILE') {
                $sellerStatus = $this->helper->getSellerStatusByCustomerId($customerId);
                if (!$sellerStatus) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('The current customer is not a seller.')
                    );
                }
            }
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

            $this->_dataObjectHelper->populateWithArray(
                $customer,
                $customerData,
                \Magento\Customer\Api\Data\CustomerInterface::class
            );

            $customer->setData('ignore_validation_flag', true);
            $this->_customerRepository->save($customer);
            $returnArray['message'] = __('Vendor Attributes has been saved.');
            $returnArray['status'] = self::SUCCESS;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['message'] = $e->getMessage();
            $returnArray['status'] = self::LOCAL_ERROR;
        } catch (\Exception $e) {
            $returnArray['message'] = __('Invalid Request');
            $returnArray['status'] = self::SEVERE_ERROR;
        }
        return $returnArray;
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
