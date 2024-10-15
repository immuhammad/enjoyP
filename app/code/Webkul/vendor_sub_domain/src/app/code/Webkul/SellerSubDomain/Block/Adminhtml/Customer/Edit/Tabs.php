<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SellerSubDomain
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\SellerSubDomain\Block\Adminhtml\Customer\Edit;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Ui\Component\Layout\Tabs\TabInterface;
use Magento\Backend\Block\Widget\Form;
use Magento\Customer\Model\CustomerFactory;
use Magento\Backend\Block\Widget\Form\Generic;

/**
 * Customer account form block.
 */
class Tabs extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    protected $_dob = null;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customer;
    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory
     */
    protected $_attributeCollection;

    /**
     * @var \Magento\Eav\Model\Entity
     */
    protected $_eavEntity;
    /**
     * @var \Webkul\MpVendorAttributeManager\Helper\Data
     */
    protected $_currentHelper;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    protected $helperFactory;

    const VENDOR_DOMAIN_TEMPLATE = 'customer/vendor-domain.phtml';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry             $registry
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection,
        \Magento\Eav\Model\Entity $eavEntity,
        CustomerFactory $customer,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Webkul\SellerSubDomain\Helper\Data $currentHelper,
        \Magento\Framework\ObjectManagerInterface $helperFactory,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_systemStore = $systemStore;
        $this->_attributeCollection = $attributeCollection;
        $this->_customer = $customer;
        $this->_eavEntity = $eavEntity;
        $this->_objectManager = $objectManager;
        $this->storeManager = $context->getStoreManager();
        $this->_currentHelper = $currentHelper;
        $this->helperFactory = $helperFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return string|null
     */
    public function getCustomerId()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Vendor Domain');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Vendor Domain');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        $coll = $this->_objectManager->create(
            \Webkul\Marketplace\Block\Adminhtml\Customer\Edit::class
        )->getMarketplaceUserCollection();
        $isSeller = false;
        foreach ($coll as $row) {
            $isSeller = $row->getIsSeller();
        }
        if ($this->getCustomerId() && $isSeller && $this->_currentHelper->isLocalDomainSettingEnable()) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        $coll = $this->_objectManager->create(
            \Webkul\Marketplace\Block\Adminhtml\Customer\Edit::class
        )->getMarketplaceUserCollection();
        $isSeller = false;
        foreach ($coll as $row) {
            $isSeller = $row->getIsSeller();
        }
        if ($this->getCustomerId() && $isSeller && $this->_currentHelper->isLocalDomainSettingEnable()) {
            return false;
        }

        return true;
    }

    /**
     * Tab class getter.
     *
     * @return string
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * Return URL link to Tab content.
     *
     * @return string
     */
    public function getTabUrl()
    {
        return '';
    }

    /**
     * Tab should be loaded trough Ajax call.
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    public function getCurrentCustomer()
    {
        $customerId = $customerId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        $customerData = $this->_customer->create()->load($customerId);

        return $customerData;
    }

    /**
     * Set template to itself.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::VENDOR_DOMAIN_TEMPLATE);
        }
        return $this;
    }
    
    /**
     * @param String $className
     * @return object
     */
    public function helper($className)
    {
        $helper = $this->helperFactory->get($className);
        if (false === $helper instanceof \Magento\Framework\App\Helper\AbstractHelper) {
            throw new \LogicException($className . ' doesn\'t extends Magento\Framework\App\Helper\AbstractHelper');
        }
        return $helper;
    }
}
