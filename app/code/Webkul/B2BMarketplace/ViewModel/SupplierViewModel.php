<?php
namespace Webkul\B2BMarketplace\ViewModel;

class SupplierViewModel implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    public function __construct(
        \Magento\Customer\Helper\Address $addressHelper,
        \Magento\Directory\Helper\Data $helperData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Directory\Block\Data $directoryBlock,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $helperFactory
    ) {
        $this->addressHelper = $addressHelper;
        $this->helperData = $helperData;
        $this->scopeConfig = $scopeConfig;
        $this->_directoryBlock = $directoryBlock;
        $this->_storeManager = $storeManager;
        $this->helperFactory = $helperFactory;
    }

    public function getConfigPasswordLength()
    {
        return $this->scopeConfig->getValue(
            'customer/password/minimum_password_length',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getConfigCharLength()
    {
        return $this->scopeConfig->getValue(
            'customer/password/required_character_classes_number',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getConfigRegionDisplay()
    {
        return $this->scopeConfig->getValue(
            'general/region/display_all',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_WEB
        );
    }

    public function getCountryHtmlSelect(
        $defValue = null,
        $name = 'country_id',
        $id = 'country',
        $title = 'Country'
    ) {
        return $this->_directoryBlock->getCountryHtmlSelect(
            $defValue,
            $name,
            $id,
            $title
        );
    }

    /**
     * @param String $className
     * @return object
     */
    public function helper($className)
    {
        $helper = $this->helperFactory->get($className);
        if (false === $helper instanceof \Magento\Framework\App\Helper\AbstractHelper) {
            throw new \LogicException($className .
             ' doesn\'t extends Magento\Framework\App\Helper\AbstractHelper');
        }
        return $helper;
    }
}
