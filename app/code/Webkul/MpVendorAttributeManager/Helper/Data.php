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
namespace Webkul\MpVendorAttributeManager\Helper;

use Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAttribute\CollectionFactory as VendorAttributeCollection;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Downloadable\Helper\File as FileHelper;

/**
 * MpVendorAttributeManager data helper.
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var string
     */
    protected $_code = 'vendor_attribute';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Core store config.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSessionFactory;

    /**
     * @var \Magento\Eav\Model\Entity
     */
    protected $_eavEntity;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory
     */
    protected $_attributeCollection;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAttribute\CollectionFactory
     */
    protected $_vendorAttributeCollection;

    /**
     * @var \Magento\Eav\Model\AttributeFactory
     */
    protected $_attributeFactory;

    /**
     * @var \Webkul\MpVendorAttributeManager\Model\VendorAssignGroupFactory
     */
    protected $_vendorAssignGroupFactory;

    /**
     * @var \Webkul\MpVendorAttributeManager\Model\VendorGroupFactory
     */
    protected $_vendorGroupFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezoneInterface;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    private $file;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $fileSystem;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private $ioFile;

    /**
     * @var FileHelper
     */
    private $fileHelper;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    private $curl;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\SessionFactory $customerSessionFactory
     * @param \Magento\Eav\Model\Entity $eavEntity
     * @param \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection
     * @param \Magento\Framework\App\RequestInterface $request
     * @param VendorAttributeCollection $vendorAttributeCollection
     * @param \Magento\Eav\Model\AttributeFactory $attributeFactory
     * @param \Webkul\MpVendorAttributeManager\Model\VendorAssignGroupFactory $vendorAssignGroupFactory
     * @param \Webkul\MpVendorAttributeManager\Model\VendorGroupFactory $vendorGroupFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param \Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory $sellerCollectionFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Filesystem\Driver\File $file
     * @param \Magento\Framework\Filesystem $fileSystem
     * @param \Magento\Framework\Filesystem\Io\File $ioFile
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param FileHelper $fileHelper
     * @param \Magento\Cms\Model\Template\FilterProvider|null $filterProvider
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Magento\Eav\Model\Entity $eavEntity,
        \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection,
        \Magento\Framework\App\RequestInterface $request,
        VendorAttributeCollection $vendorAttributeCollection,
        \Magento\Eav\Model\AttributeFactory $attributeFactory,
        \Webkul\MpVendorAttributeManager\Model\VendorAssignGroupFactory $vendorAssignGroupFactory,
        \Webkul\MpVendorAttributeManager\Model\VendorGroupFactory $vendorGroupFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory $sellerCollectionFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Framework\Filesystem\Io\File $ioFile,
        \Magento\Framework\HTTP\Client\Curl $curl,
        FileHelper $fileHelper,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider = null
    ) {
        $this->_scopeConfig = $context->getScopeConfig();
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_eavEntity = $eavEntity;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->request = $request;
        $this->_attributeCollection = $attributeCollection;
        $this->_vendorAttributeCollection = $vendorAttributeCollection;
        $this->_attributeFactory = $attributeFactory;
        $this->_vendorAssignGroupFactory = $vendorAssignGroupFactory;
        $this->_vendorGroupFactory = $vendorGroupFactory;
        $this->_urlBuilder = $urlBuilder;
        $this->_urlEncoder = $urlEncoder;
        $this->sellerCollectionFactory = $sellerCollectionFactory;
        $this->_customerFactory = $customerFactory;
        $this->mpHelper = $mpHelper;
        $this->timezoneInterface = $timezoneInterface;
        $this->jsonHelper = $jsonHelper;
        $this->file = $file;
        $this->fileSystem = $fileSystem;
        $this->ioFile = $ioFile;
        $this->curl = $curl;
        $this->fileHelper = $fileHelper;
        $this->filterProvider = $filterProvider ?: \Magento\Framework\App\ObjectManager::getInstance()
                                ->create(\Magento\Cms\Model\Template\FilterProvider::class);
    }

    /**
     * Retrieve information from carrier configuration.
     *
     * @param string $field
     *
     * @return void|false|string
     */
    public function getConfigData($field)
    {
        if (empty($this->_code)) {
            return false;
        }
        $path = 'marketplace/'.$this->_code.'/'.$field;

        return $this->_scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeManager->getStore()
        );
    }

    /**
     * Check is vendor registarion form.
     *
     * @return bool
     */
    public function isVendorRegistration()
    {
        $vendorRegister = $this->request->getParam('v');
        if ($vendorRegister) {
            return true;
        }

        return false;
    }

    /**
     * Load Customer Data
     *
     * @param string $customerId
     *
     * @return object
     */
    public function loadCustomer($customerId = '')
    {
        if ($customerId == '') {
            $customerId = $this->customerSessionFactory->create()->getCustomer()->getId();
        }
        $customer = $this->_customerFactory->create()->load($customerId);
        return $customer;
    }

    /**
     * Load Customer Data
     *
     * @param string $shopUrl
     *
     * @return object
     */
    public function loadCustomerByShopUrl($shopUrl = '')
    {
        if ($customerId == '') {
            $customerId = $this->customerSessionFactory->create()->getCustomer()->getId();
        }
        $customer = $this->_customerFactory->create()->load($customerId);
        return $customer;
    }

    /**
     * Get Attributes Collection
     *
     * @param boolean $isSeller
     * @param boolean $checkforProfile
     *
     * @return Collection $attributeCollection
     */
    public function getAttributeCollection($isSeller = false, $checkforProfile = false)
    {
        $attributeUsed = [0,1];
        if ($isSeller) {
            $attributeUsed = [0,2];
        }

        $customAttributes = $this->_vendorAttributeCollection->create()
                                ->getVendorAttributeCollection()
                                ->addFieldToFilter("attribute_used_for", ["in" => $attributeUsed])
                                ->addFieldToFilter("wk_attribute_status", "1");
        if ($checkforProfile) {
            $customAttributes->addFieldToFilter("show_in_front", "1");
        }

        if ($customAttributes->getSize()) {
            return $customAttributes;
        }

        return false;
    }

    /**
     * Get Attribute Collection by Attribute Group
     *
     * @param  boolean $groupId
     * @param  boolean $isSeller
     *
     * @return collection
     */
    public function getAttributeCollectionByGroup($groupId, $isSeller = false)
    {
        $groupAttributes = $this->getAttributesByGroupId($groupId);
        if ($groupAttributes->getSize()) {
            $attributeUsedFor = [0,1];
            if ($isSeller) {
                $attributeUsedFor = [0,2];
            }

            $attributeCollection = $groupAttributes->addFieldToFilter(
                "vat.attribute_used_for",
                ["in" => $attributeUsedFor]
            );

            return $attributeCollection;
        }
        return false;
    }

    /**
     * Function get Attributes By GroupId
     *
     * @param int $groupId
     * @return void
     */
    public function getAttributesByGroupId($groupId)
    {
        $groupAttributes = $this->_vendorAssignGroupFactory->create()->getCollection()
                                ->getGroupAttributes($groupId);
        return $groupAttributes;
    }

    /**
     * Function get attributes groups
     *
     * @return array
     */
    public function getAttributeGroup()
    {
        $groups = [];

        $vendorGroupCollection = $this->_vendorGroupFactory->create()
                            ->getCollection()
                            ->addFieldToFilter('status', ['eq' => 1]);

        if ($vendorGroupCollection->getSize()) {
            $groups = $vendorGroupCollection->getColumnValues('group_name');
        }

        return $groups;
    }

    /**
     * Get current store
     *
     * @return object
     */
    public function getStore()
    {
        return $this->_storeManager->getStore();
    }

    /**
     * Provide vendorConfig
     *
     * @return array
     */
    public function getVendorConfig()
    {
        $config['groups'] = $this->getAttributeGroups();
        $config['groups_attribute'] = $this->getAttributeCollectionForGroups($config['groups']);
        $config['is_attribute_assigned_to_any_customer'] = $this->getAnyAttributeAssignedToCustomer();
        $config['is_attribute_assigned_to_any_seller'] = $this->getAnyAttributeAssignedToSeller();
        return $config;
    }

    /**
     * Provide config to window.vendorConfig
     *
     * @return string
     */
    public function getEncodedVendorConfig()
    {
        $config = $this->getVendorConfig();
        $encodedConfig = $this->jsonHelper->jsonEncode($config);
        return $encodedConfig;
    }

    /**
     * Function get Any Attribute Assigned To Customer
     *
     * @return void
     */
    public function getAnyAttributeAssignedToCustomer()
    {
        $vendorGroupFactory = $this->_vendorGroupFactory->create();
        return $vendorGroupFactory->getAnyAttributeAssignedToCustomer();
    }

    /**
     * Function get Any Attribute Assigned To Seller
     *
     * @return void
     */
    public function getAnyAttributeAssignedToSeller()
    {
        $vendorGroupFactory = $this->_vendorGroupFactory->create();
        return $vendorGroupFactory->getAnyAttributeAssignedToSeller();
    }

    /**
     * Fetch attributes by attribute group
     *
     * @param array $groups
     * @return array
     */
    public function getAttributeCollectionForGroups($groups)
    {
        $groupAttributes = [];
        $fileTypes = ["image","file"];
        $extensions = [];
        foreach ($fileTypes as $fileType) {
            $extensions[$fileType] = $this->getConfigData('allowede_'.$fileType.'_extension');
        }
        foreach ($groups as $group) {
            $groupAttribute = $this->getAttributesByGroupId($group['group_id']);

            foreach ($groupAttribute as $attribute) {
                $attributeClass = explode(" ", $attribute->getFrontendClass() ?? '');
                $optiondata = [];

                if ($attribute->getFrontendInput() == 'select' || $attribute->getFrontendInput() == 'multiselect') {
                    $optiondata = $attribute->getSource()->getAllOptions();
                }
                $allowedExtensions = "";

                if (in_array($attribute->getFrontendInput(), $fileTypes)) {
                    $allowedExtensions = $extensions[$attribute->getFrontendInput()];
                }

                $groupAttributes['groups'][$group['group_id']][] = [
                    'attribute_id' => $attribute->getId(),
                    'attribute_code' => $attribute->getAttributeCode(),
                    'frontend_input' => $attribute->getFrontendInput(),
                    'is_required' => $attribute->getRequiredField(),
                    'label' => $attribute->getStoreLabel(),
                    'wysiwyg_enabled' => in_array('wysiwyg_enabled', $attributeClass)?1:0,
                    'option_data' => $optiondata,
                    'frontend_class' => $attribute->getFrontendClass() ?? '',
                    'extension' => $allowedExtensions,
                    'sort_order' => $attribute->getSortOrder(),
                    'used_for' => $attribute->getAttributeUsedFor()
                ];
            }
        }
        return $groupAttributes;
    }

    /**
     * Function getAttributeGroups
     *
     * @return void
     */
    public function getAttributeGroups()
    {
        $groups = [];
        $groupCollection = $this->_vendorGroupFactory->create()
                                ->getCollection()
                                ->addFieldToFilter('status', ['eq' => 1]);
        if ($groupCollection->getSize()) {
            foreach ($groupCollection as $value) {
                $groups[] = [
                    'group_id' => $value->getEntityId(),
                    'group_name' => $value->getGroupName()
                ];
            }
        }
        return $groups;
    }

    /**
     * Function get Allowed Image Extensions
     *
     * @return string
     */
    public function getAllowedImageExtensions()
    {
        $allowedImageExtensions = $this->scopeConfig->getValue(
            'marketplace/vendor_attribute/allowede_image_extension',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $allowedImageExtensions;
    }

    /**
     * Function get Allowed File Extensions
     *
     * @return string
     */
    public function getAllowedFileExtensions()
    {
        $allowedFileExtensions = $this->scopeConfig->getValue(
            'marketplace/vendor_attribute/allowede_file_extension',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $allowedFileExtensions;
    }

    /**
     * Function get Field Frontend Input
     *
     * @param string $attributeCode
     * @return void
     */
    public function getFieldFrontendInput($attributeCode)
    {
        $attributeCollection = $this->_attributeFactory->create()
                                    ->getCollection()
                                    ->addFieldToFilter('attribute_code', $attributeCode);

        if (count($attributeCollection) == 1) {
            foreach ($attributeCollection as $attribute) {
                return $attribute->getFrontendInput();
            }
        }
    }

    /**
     * Function get Term Condition Config
     *
     * @param string $field
     * @return mixed
     */
    public function getTermConditionConfig($field)
    {
        if (empty($this->_code)) {
            return false;
        }
        $path = 'marketplace/termcondition/'.$field;

        return $this->_scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeManager->getStore()
        );
    }

    /**
     * Function encodeFileName
     *
     * @param string $type
     * @param string $filePath
     * @return void
     */
    public function encodeFileName($type, $filePath)
    {
        $url = $this->_urlBuilder->getUrl(
            'vendorattribute/preview/fileview',
            [$type => $this->_urlEncoder->encode(ltrim($filePath, '/'))]
        );
        return $url;
    }

    /**
     * Function isB2BMarketplaceInstalled
     *
     * @return boolean
     */
    public function isB2BMarketplaceInstalled()
    {
        if ($this->_moduleManager->isEnabled('Webkul_B2BMarketplace')) {
            return true;
        }
        return false;
    }

    /**
     * Check is seller
     *
     * @param int $customerId
     * @return boolean
     */
    public function getSellerStatusByCustomerId($customerId)
    {
        $sellerStatus = 0;
        $model = $this->sellerCollectionFactory->create()
                ->addFieldToFilter('seller_id', $customerId)
                ->addFieldToFilter('store_id', 0);
        foreach ($model as $value) {
            $sellerStatus = $value->getIsSeller();
        }
        return $sellerStatus;
    }

    /**
     * Function getValueOptionsForAttribute
     *
     * @param object $attribute
     * @param mixed $rawValue
     * @return array
     */
    public function getValueOptionsForAttribute($attribute, $rawValue)
    {
        $options = [];
        $mediaUrl = $this->mpHelper->getMediaUrl();
        switch ($attribute->getFrontendInput()) {
            case "text":
                $value = $rawValue;
                break;
            case "textarea":
                $value = $this->getFilterData($rawValue);
                break;
            case "date":
                $value = $rawValue
                    ? $this->convertDateFormat($rawValue)
                    : '';
                break;
            case "boolean":
                $value = $rawValue;
                break;
            case "multiselect":
                $value = $this->jsonHelper->jsonEncode($rawValue);
                $value = is_array($rawValue) ? implode(",", $rawValue) : $rawValue ;
                $options = $attribute->getSource()->getAllOptions();
                break;
            case "select":
                $value = $rawValue;
                $options = $attribute->getSource()->getAllOptions();
                break;
            case "image":
                $value = $rawValue;
                if (isset($rawValue) && $rawValue != 1) {
                    $value = $mediaUrl."vendorfiles/image/".$rawValue;
                }
                break;
            case "file":
                $value = $rawValue;
                if (isset($rawValue) && $rawValue != 1) {
                    $value = $mediaUrl."vendorfiles/file/".$rawValue;
                }
                break;
            default:
                $value = '';
        }
        return [$value, $options];
    }

    /**
     * Function getAttributeValueForSellerProfile
     *
     * @param object $attribute
     * @param mixed $rawValue
     * @return mixed
     */
    public function getAttributeValueForSellerProfile($attribute, $rawValue)
    {
        $mediaUrl = $this->mpHelper->getMediaUrl();
        switch ($attribute->getFrontendInput()) {
            case "text":
                $value = $rawValue;
                break;
            case "textarea":
                $value = $this->getFilterData($rawValue);
                break;
            case "date":
                $value = $rawValue
                    ? $this->convertDateFormat($rawValue)
                    : '';
                break;
            case "boolean":
                $value = $rawValue;
                break;
            case "multiselect":
                $value = $rawValue;
                $multiSelectData = [];
                $options = $attribute->getSource()->getAllOptions();
                foreach ($options as $instance) {
                    if (in_array($instance['value'], $rawValue)) {
                        array_push($multiSelectData, $instance['label']);
                    }
                }
                $value = implode(',', $multiSelectData);
                break;
            case "select":
                $value = $rawValue;
                $options = $attribute->getSource()->getAllOptions();
                foreach ($options as $instance) {
                    if ($instance['value'] == $rawValue) {
                        $value =  $instance['label'];
                    }
                }
                break;
            case "image":
                $value = $rawValue;
                if (isset($rawValue) && $rawValue != 1) {
                    $value = $mediaUrl."vendorfiles/image/".$rawValue;
                }
                break;
            case "file":
                $value = $rawValue;
                if (isset($rawValue) && $rawValue != 1) {
                    $value = $mediaUrl."vendorfiles/file/".$rawValue;
                }
                break;
            default:
                $value = '';
        }
        return $value;
    }

    /**
     * Function get Filter Data
     *
     * @param string $content
     * @return string
     */
    public function getFilterData($content)
    {
        if ($content != "") {
            return $this->filterProvider->getPageFilter()->filter($content);
        }
        return $content;
    }

    /**
     * Convert Date Format
     *
     * @param Object $date
     *
     * @return string.
     */
    public function convertDateFormat($date)
    {
        return $this->timezoneInterface->date($date)->format("Y-m-d");
    }

    /**
     * Function createFileFromUrl
     *
     * @param string $url
     * @param string $ext
     * @param string $uploadPath Relative path inside pub media
     * @param string $newFileName
     * @return void
     */
    public function createFileFromUrl($url, $ext, $uploadPath, $newFileName = '')
    {
        if (!$newFileName) {
            $newFileName = 'File-'.time().'.'.$ext;
        }

        $browserStr = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 '
                                    .'(KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13';
        $this->curl->setOption(CURLOPT_USERAGENT, $browserStr);

        $this->curl->setOption(CURLOPT_RETURNTRANSFER, 1);
        $this->curl->setOption(CURLOPT_SSL_VERIFYHOST, 0);
        $this->curl->setOption(CURLOPT_SSL_VERIFYPEER, 0);
        $this->curl->get($url);
        $response = $this->curl->getBody();

        $newFilePath = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)
                                ->getAbsolutePath() . $uploadPath;
        $completeFilePath = $newFilePath . $newFileName;
        if ($this->file->isExists($completeFilePath) && $this->getFileSize($completeFilePath) > 0) {
            $this->file->deleteFile($completeFilePath);
        }

        $this->ioFile->checkAndCreateFolder($newFilePath, 0775);
        $this->ioFile->open(['path' => $newFilePath]);
        $this->ioFile->write($newFileName, $response, 0666);

        if ($this->file->isExists($completeFilePath) && $this->getFileSize($completeFilePath) > 0) {
            return $newFileName;
        }
        return false;
    }

    /**
     * Function getFileSize
     *
     * @param string $path
     * @return mixed
     */
    public function getFileSize($path)
    {
        return $this->fileHelper->getFileSize($path);
    }

    /**
     * Function getFileExtension
     *
     * @param string $fileUrl
     * @return mixed
     */
    public function getFileExtension($fileUrl)
    {
        $fileBaseName = $this->getFileBaseName($fileUrl);
        $explodedArray = explode("?", $fileBaseName);
        $fileName = $explodedArray[0];
        $splitname = explode('.', $fileName);
        $ext = end($splitname);
        return [$fileName, $ext];
    }

    /**
     * Function getFileBaseName
     *
     * @param string $path
     * @return mixed
     */
    public function getFileBaseName($path)
    {
        $parts = explode('/', $path);
        $baseName = end($parts);
        return $baseName;
    }
}
