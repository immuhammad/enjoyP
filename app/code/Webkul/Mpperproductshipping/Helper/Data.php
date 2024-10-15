<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Mpperproductshipping
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\Mpperproductshipping\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var null|array
     */
    protected $_options;

    /**
     * @var currency
     */
    protected $_currency;

     /**
      * @var marketplaceHelper
      */
    protected $marketplaceHelper;

    /**
     * @param Magento\Framework\App\Helper\Context $context
     * @param Magento\Directory\Model\Currency $currency
     * @param Magento\Customer\Model\Session $customerSession
     * @param Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Webkul\Marketplace\Helper\Data $marketplaceHelper
     * @param Magento\Directory\Model\Currency $currency
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Directory\Model\Currency $currency,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper
    ) {
        parent::__construct($context);
        $this->_currency = $currency;
        $this->marketplaceHelper = $marketplaceHelper;
    }

    /**
     * get shipping is enabled or not for system config
     */

    public function getIsActive()
    {
        return $this->scopeConfig->getValue(
            'carriers/webkulmpperproduct/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * get table rate shipping title from system config
     */

    public function getshippingTitle()
    {
        return $this->scopeConfig->getValue(
            'carriers/webkulmpperproduct/title',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get table rate shipping name from system config
     */

    public function getshippingName()
    {
        return $this->scopeConfig->getValue(
            'carriers/webkulmpperproduct/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Currency Symbol
     */

    public function getCurrencySymbol()
    {
        return $this->_currency->getCurrencySymbol();
    }

    /**
     * Get Shipping Based
     */

    public function getShippingBasedOn()
    {
        return $this->scopeConfig->getValue(
            'carriers/webkulmpperproduct/shippingbasedon',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Default Shipping Price Of Module
     */

    public function getDefaultShippingPrice()
    {
        return $this->scopeConfig->getValue(
            'carriers/webkulmpperproduct/defaultprice',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Customer Id Of Partner
     */

    public function getPartnerId()
    {
        $partnerId = $this->marketplaceHelper->getCustomerId();
        return $partnerId;
    }
}
