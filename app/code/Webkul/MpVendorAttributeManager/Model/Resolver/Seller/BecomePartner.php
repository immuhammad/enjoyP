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

namespace Webkul\MpVendorAttributeManager\Model\Resolver\Seller;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\Customer\Mapper;
use Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAttribute\CollectionFactory;

/**
 * Book field resolver, used for GraphQL request processing
 */
class BecomePartner implements ResolverInterface
{
    public const SEVERE_ERROR = 0;
    public const SUCCESS = 1;
    public const LOCAL_ERROR = 2;

    /**
     * @var \Webkul\Marketplace\Model\SellerFactory
     */
    protected $sellerFactory;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

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
     * Constructor
     *
     * @param \Webkul\Marketplace\Model\SellerFactory $sellerFactory
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Webkul\MpVendorAttributeManager\Helper\Data $helper
     * @param CustomerInterfaceFactory $customerDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param Mapper $customerMapper
     * @param CustomerRepositoryInterface $customerRepository
     * @param CollectionFactory $vendorAttributeCollectionFactory
     */
    public function __construct(
        \Webkul\Marketplace\Model\SellerFactory $sellerFactory,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\MpVendorAttributeManager\Helper\Data $helper,
        CustomerInterfaceFactory $customerDataFactory,
        DataObjectHelper $dataObjectHelper,
        Mapper $customerMapper,
        CustomerRepositoryInterface $customerRepository,
        CollectionFactory $vendorAttributeCollectionFactory
    ) {
        $this->sellerFactory = $sellerFactory;
        $this->mpHelper = $mpHelper;
        $this->date = $date;
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
        if (!isset($args['shopUrl']) || !isset($args['isSeller'])) {
            throw new GraphQlInputException(
                __("'shopUrl' & 'isSeller' input arguments are required.")
            );
        }

        try {
            $returnArray = [];
            $customerId = $context->getUserId();
            $shopUrl = $args['shopUrl'];
            $isSeller = $args['isSeller'];
            if ($this->helper->getSellerStatusByCustomerId($customerId)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('already seller')
                );
            }

            $shop_urlcount = $this->sellerFactory->create()->getCollection()
                ->addFieldToFilter(
                    'shop_url',
                    $shopUrl
                );
            if (!count($shop_urlcount)) {
                $sellerId = $customerId;
                $status = $this->mpHelper->getIsPartnerApproval() ? 0 : 1;
                $model = $this->sellerFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('shop_url', $shopUrl);
                if (!count($model)) {
                    if (isset($isSeller) && $isSeller) {
                        $autoId = 0;
                        $collection = $this->sellerFactory->create()
                            ->getCollection()
                            ->addFieldToFilter('seller_id', $sellerId);
                        foreach ($collection as $value) {
                            $autoId = $value->getId();
                        }
                        $value = $this->sellerFactory->create()->load($autoId);
                        $value->setData('is_seller', $status);
                        $value->setData('shop_url', $shopUrl);
                        $value->setData('seller_id', $sellerId);
                        $value->setCreatedAt($this->date->gmtDate());
                        $value->setUpdatedAt($this->date->gmtDate());
                        $value->save();

                        /** Function to save Mp Vendor Attributes data */
                        $this->saveMpVendorAttributes($customerId, $args);

                        $returnArray['message'] = __('Profile information was successfully saved');
                        $returnArray['status'] = self::SUCCESS;
                        return $returnArray;
                    } else {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('Please confirm that you want to become seller.')
                        );
                    }
                } else {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Shop URL already exist please set another.')
                    );
                }
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Shop URL already exist please set another.')
                );
            }
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
     * Function saveMpVendorAttributes
     *
     * @param int $customerId
     * @param array $args
     * @return void
     */
    protected function saveMpVendorAttributes($customerId, $args)
    {
        if (!empty($args['attributesData'])) {
            if (!is_array($args['attributesData'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Attributes data cannot be empty.')
                );
            }

            $sellerStatus = 1;

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
