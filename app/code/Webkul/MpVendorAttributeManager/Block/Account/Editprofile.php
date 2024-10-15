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
namespace Webkul\MpVendorAttributeManager\Block\Account;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\SessionFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Eav\Model\Entity;
use Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Webkul\Marketplace\Helper\Data as MarketplaceHelper;
use Webkul\MpVendorAttributeManager\Helper\Data;
use Webkul\MpVendorAttributeManager\Model\VendorAttributeFactory;
use Magento\Cms\Helper\Wysiwyg\Images;

class Editprofile extends Template
{
    /**
     * @var Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezoneInterface;

    /**
     * @var \Magento\Eav\Model\Entity
     */
    protected $eavEntity;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory
     */
    protected $attributeCollection;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $_mpHelper;

    /**
     * @var \Webkul\MpVendorAttributeManager\Helper\Data
     */
    protected $helper;

    /**
     * @var \Webkul\MpVendorAttributeManager\Model\VendorAttributeFactory
     */
    protected $vendorAttributeFactory;

    /**
     * @var $wysiwygImages
     */
    protected $wysiwygImages;

    /**
     * Constructor
     *
     * @param Context $context
     * @param SessionFactory $customerSessionFactory
     * @param CustomerFactory $customerFactory
     * @param TimezoneInterface $timezoneInterface
     * @param Entity $eavEntity
     * @param CollectionFactory $attributeCollection
     * @param MarketplaceHelper $mpHelper
     * @param Data $helper
     * @param VendorAttributeFactory $vendorAttributeFactory
     * @param JsonHelper $jsonHelper
     * @param Images $wysiwygImages
     * @param array $data
     * @param \Magento\Cms\Model\Template\FilterProvider|null $filterProvider
     */
    public function __construct(
        Context $context,
        SessionFactory $customerSessionFactory,
        CustomerFactory $customerFactory,
        TimezoneInterface $timezoneInterface,
        Entity $eavEntity,
        CollectionFactory $attributeCollection,
        MarketplaceHelper $mpHelper,
        Data $helper,
        VendorAttributeFactory $vendorAttributeFactory,
        JsonHelper $jsonHelper,
        Images $wysiwygImages,
        array $data = [],
        \Magento\Cms\Model\Template\FilterProvider $filterProvider = null
    ) {
        $this->customerSessionFactory = $customerSessionFactory;
        $this->_customerFactory = $customerFactory;
        $this->timezoneInterface = $timezoneInterface;
        $this->eavEntity = $eavEntity;
        $this->attributeCollection = $attributeCollection;
        $this->_mpHelper = $mpHelper;
        $this->helper = $helper;
        $this->vendorAttributeFactory = $vendorAttributeFactory;
        $this->jsonHelper = $jsonHelper;
        $this->wysiwygImages = $wysiwygImages;
        $this->filterProvider = $filterProvider ?: \Magento\Framework\App\ObjectManager::getInstance()
                                ->create(\Magento\Cms\Model\Template\FilterProvider::class);
        parent::__construct($context, $data);
    }

    /**
     * Get Attribute Collection
     *
     * @param  boolean $isSeller
     *
     * @return collection
     */
    public function getAttributeCollection($isSeller = false)
    {
        return $this->helper->getAttributeCollection($isSeller);
    }

    /**
     * Get Vendor's Attribute Collection
     *
     * @param  boolean $isSeller
     *
     * @return collection
     */
    public function getVendorAttributeCollection()
    {
        $attributeUsed = [0,2];

        $vendorAttributes = $this->vendorAttributeFactory->create()
                                ->getCollection()
                                ->addFieldToFilter("attribute_used_for", ["in" => $attributeUsed])
                                ->addFieldToFilter("show_in_front", 1);

        if ($vendorAttributes->getSize()) {
            $attributeIds = $vendorAttributes->getColumnValues('attribute_id');

            $typeId = $this->eavEntity->setType('customer')->getTypeId();
            $vendorAttributesCollection = $this->attributeCollection->create()
                                       ->setEntityTypeFilter($typeId)
                                       ->addFilterToMap("attribute_id", "main_table.attribute_id")
                                       ->addFieldToFilter("attribute_id", ["in" => $attributeIds])
                                       ->setOrder('sort_order', 'ASC');

            return $vendorAttributesCollection;
        }

        return false;
    }

    /**
     * Check if Attribute is Required or not
     *
     * @param int $attributeId
     *
     * @return Boolean true|false
     */
    public function checkIfRequired($attributeId)
    {
        $customAttribute = $this->vendorAttributeFactory->create()->load($attributeId, "attribute_id");
        if ($customAttribute) {
            return $customAttribute->getRequiredField();
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
        $groupAttributes = $this->helper->getAttributesByGroupId($groupId);
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
     * Get System configuration value.
     *
     * @param  string $field
     *
     * @return string
     */
    public function getConfigData($field)
    {
        return $this->helper->getConfigData($field);
    }

    /**
     * Load Customer Data
     *
     * @param  string $customerId
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
     * Get Current store
     *
     * @return object
     */
    public function getStore()
    {
        return $this->helper->getStore();
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
     * Get Form Save Action Url for Seller Vendor Attribute Form
     *
     * @return String url
     */
    public function getActionUrl()
    {
        return $this->getUrl(
            'vendorattribute/seller/saveattribute',
            [
                "_secure" => $this->getRequest()->isSecure()
            ]
        );
    }

    /**
     * Get Form Save Action Url for Customer Additional Attribute Form
     *
     * @return String url
     */
    public function getCustomerActionUrl()
    {
        return $this->getUrl(
            'vendorattribute/account/saveattribute',
            [
                "_secure" => $this->getRequest()->isSecure()
            ]
        );
    }

    /**
     * Function get Helper
     *
     * @return object
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * Function get MpHelper
     *
     * @return object
     */
    public function getMpHelper()
    {
        return $this->_mpHelper;
    }

    /**
     * Get Seller Profile Details
     *
     * @return \Webkul\Marketplace\Model\Seller | bool
     */
    public function getProfileDetail()
    {
        $helper = $this->_mpHelper;
        return $helper->getProfileDetail(MarketplaceHelper::URL_TYPE_PROFILE);
    }

    /**
     * Function get JsonHelper
     *
     * @return object
     */
    public function getJsonHelper()
    {
        return $this->jsonHelper ;
    }

    /**
     * Function get Wysiwyg Url
     *
     * @param void
     * @return string
     */
    public function getWysiwygUrl()
    {
        $currentTreePath = $this->wysiwygImages->idEncode(
            \Magento\Cms\Model\Wysiwyg\Config::IMAGE_DIRECTORY
        );
        $url =  $this->getUrl(
            'marketplace/wysiwyg_images/index',
            [
                'current_tree_path' => $currentTreePath
            ]
        );
        return $url;
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
}
