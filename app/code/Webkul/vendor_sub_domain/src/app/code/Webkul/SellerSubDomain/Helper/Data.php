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
namespace Webkul\SellerSubDomain\Helper;

use Magento\Catalog\Model\ProductFactory;
use Webkul\Marketplace\Model\ProductFactory as MpProductFactory;

/**
 * Webkul SellerSubDomain Helper Data.
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var UrlInterface
     */
    protected $urlInterface;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Webkul\Marketplace\Model\SellerFactory
     */
    protected $_seller;

    /**
     * @var \Webkul\Marketplace\Model\ProductFactory
     */
    private $mpProduct;

    /**
     * @var \Webkul\SellerSubDomain\Model\DomainFactory $domainFactory
     */
    protected $_domainFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection $resource
     */
    protected $_resource;

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @param \Magento\Framework\App\Helper\Context   $context
     * @param \Webkul\Marketplace\Helper\Data         $mpHelper
     * @param \Webkul\Marketplace\Model\SellerFactory $seller
     * @param MpProductFactory                        $mpProduct
     * @param ProductFactory                          $productFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Webkul\Marketplace\Model\SellerFactory $seller,
        MpProductFactory $mpProduct,
        ProductFactory $productFactory,
        \Webkul\SellerSubDomain\Model\DomainFactory $domainFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_objectManager = $objectManager;
        $this->urlInterface = $context->getUrlBuilder();
        $this->_seller = $seller;
        $this->mpProduct = $mpProduct;
        $this->productFactory = $productFactory;
        $this->_domainFactory = $domainFactory;
        $this->_resource = $resource;
        $this->storeRepository = $storeRepository;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * is module enable
     *
     * @return boolean
     */
    public function isModuleEnable()
    {
        return $this->scopeConfig->getValue(
            'sellersubdomain/settings/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get Prefix
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->scopeConfig->getValue(
            'sellersubdomain/settings/prefix',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get getCurrentUrl
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->urlInterface->getCurrentUrl();
    }

    /**
     * is Admin Product Allowed
     *
     * @return boolean
     */
    public function isAllPagesAllowedOnSellerDomain()
    {
        return !$this->scopeConfig->getValue(
            'sellersubdomain/settings/url_display_setting',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * is Admin Product Allowed
     *
     * @return boolean
     */
    public function isAdminProductAllowed()
    {
        return $this->scopeConfig->getValue(
            'sellersubdomain/settings/admin_pro',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get main base url
     *
     * @return string
     */
    public function getAdminBaseUrl()
    {
        $secureEnabled = $this->scopeConfig->getValue(
            'web/secure/use_in_frontend',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($secureEnabled) {
            return $this->scopeConfig->getValue(
                'web/secure/base_url',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return $this->scopeConfig->getValue(
            'web/unsecure/base_url',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * is local domain setting enable for seller
     *
     * @return boolean
     */
    public function isLocalDomainSettingEnable()
    {
        return $this->scopeConfig->getValue(
            'sellersubdomain/localsetting/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get homepage cms identifier value
     *
     * @return string
     */
    public function getCmsHomePageIdentifier()
    {
        return $this->scopeConfig->getValue(
            'web/default/cms_home_page',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getMpUrlRewrite()
    {
        return $this->scopeConfig->getValue(
            'marketplace/profile_settings/url_rewrite'
            // ScopeInterface::SCOPE_STORE
        );
    }

    public function getMpAutoUrlRewrite()
    {
        return $this->scopeConfig->getValue(
            'marketplace/profile_settings/auto_url_rewrite'
            // ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get marketplace helper class
     *
     * @return Webkul\Marketplace\Helper\Data
     */
    public function getMpHelper()
    {
        return $this->_objectManager->get(\Webkul\Marketplace\Helper\Data::class);
    }

    /**
     * Get Store code
     *
     * @return string
     */
    public function getStoreCode()
    {
        return $this->_storeManager->getStore()->getCode();
    }

    /**
     * getBaseUrl
     *
     * @return string
     */
    public function getBaseUrl($url)
    {
        $finalUrl = $url;
        $storeCode = $this->getStoreCode();
        try {
            $currentUrl = $this->getCurrentUrl();
            if ($this->isModuleEnable() &&
            ($url == $this->getBaseUrlFromCurrentUrl() || strpos($url, $storeCode)===true)) {
                if ($this->isLocalDomainSettingEnable()) {
                    $currentStoreId = $this->getMpHelper()->getCurrentStoreId();
                    $baseUrlData = explode('/', $currentUrl);
                    $baseUrl = $baseUrlData[0].'//'.$baseUrlData[2].'/';
                    if (strpos($currentUrl, 'stores/store/redirect') !== false) {
                        $sellerId = $this->_domainFactory->create()
                            ->getCollection()
                            ->addFieldTofilter('vendor_url', $baseUrl)
                            ->setPageSize(1)
                            ->getFirstItem()
                            ->getSellerId();
                        $domainData = $this->_domainFactory->create()
                            ->getCollection()
                            ->addFieldTofilter('seller_id', $sellerId)
                            ->addFieldTofilter('vendor_store_id', $currentStoreId)
                            ->setPageSize(1)
                            ->getFirstItem();
                        $baseUrl = $domainData->getEntityId() ? $domainData->getVendorUrl() : $baseUrl;
                    } else {
                        $domainData = $this->_domainFactory->create()
                            ->getCollection()
                            ->addFieldTofilter('vendor_url', $baseUrl)
                            ->setPageSize(1)
                            ->getFirstItem();
                        $currentStoreId = $domainData->getVendorStoreId();
                    }
                    $this->getMpHelper()->setCurrentStore($currentStoreId);
                    if ($domainData->getEntityId() &&
                    $domainData->getVendorStoreId() == $this->getMpHelper()->getCurrentStoreId()) {
                        return $baseUrl;
                    } elseif ($domainData->getEntityId()) {
                        return $this->getSellerUrl(
                            $this->getMpHelper()->getCurrentStoreId(),
                            $domainData->getSellerId()
                        );
                    }
                }
                if (strpos($currentUrl, 'marketplace/seller/profile/shop/') !== false ||
                    strpos($currentUrl, 'marketplace/seller/collection/shop/') !== false ||
                    strpos($currentUrl, 'marketplace/seller/location/shop/') !== false ||
                    strpos($currentUrl, 'marketplace/seller/feedback/shop/') !== false) {
                    $finalUrl = $this->getFinalUrl($currentUrl);
                } else {
                    $urldata = explode('/', $currentUrl);
                    $urlString = $urldata[2];
                    $domain = explode('.', $urldata[2], 2);
                    if (strpos($domain[1], $this->getBaseUrlWithoutSsl()) !== false) {
                        $shop = $this->getShopUrlByDomain($domain[0]);
                        $seller = $this->getMpHelper()->getSellerDataByShopUrl($shop);
                        if (count($seller)) {
                            $finalUrl = $this->getFinalUrl(
                                $this->getProfileUrl().'marketplace/seller/profile/shop/'.$shop
                            );
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
        return $finalUrl;
    }

    public function getShopUrlByDomain($url)
    {
        if ($this->getPrefix()) {
            $shopArr = explode($this->getPrefix(), $url);
            $url = !empty($shopArr[1]) ? $shopArr[1] : $shop;
        }
        return $url;
    }

    public function getCurrentSellerUrlFromPrevUrl($url)
    {
        try {
            if ($this->isModuleEnable()) {
                $storeParsedQuery = [];

                $storeParsedUrl = parse_url($url);
                if (isset($storeParsedUrl['query'])) {

                    parse_str($storeParsedUrl['query'], $storeParsedQuery);
                }
                $prevStoreId = false;
                $currStoreId = false;
                $prevprefix = $this->getPrefix();
                $currprefix = $this->getPrefix();
                $sellerId = false;
                foreach ($storeParsedQuery as $key => $param) {
                    if (strpos($key, '_from_store') !== false) {
                        $prevStoreId = $this->storeRepository
                            ->getActiveStoreByCode($param)
                            ->getId();
                        $prevprefix = $this->getPrefixByStoreId($prevStoreId);
                    } elseif (strpos($key, '___store') !== false) {
                        $currStoreId = $this->storeRepository
                            ->getActiveStoreByCode($param)
                            ->getId();
                        $currprefix = $this->getPrefixByStoreId($currStoreId);
                    }
                }

                $currentUrlParse = parse_url($this->getCurrentUrl());
                $urlExplodedData = explode('.', $currentUrlParse['host']);
                $shop = $urlExplodedData[0];
                if ($this->isLocalDomainSettingEnable()) {
                    $baseUrlData = explode('/', $this->getCurrentUrl());
                    $baseUrl = $baseUrlData[0].'//'.$baseUrlData[2].'/';
                    $sellerId = $this->getSellerIdFromVendorUrl($baseUrl);
                }
                $currencyChangeUrl = $this->getCurrencyChangeUrl(
                    $sellerId,
                    $urlExplodedData,
                    $prevprefix,
                    $currStoreId
                );
                ($currencyChangeUrl != "") ? $url = $currencyChangeUrl : $url = $url;
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
        return $url;
    }

    public function getCurrencyChangeUrl($sellerId, $urlExplodedData, $prevprefix, $currStoreId)
    {
        $url = "";
        if (!$sellerId) {
            if (strpos($urlExplodedData[0], $prevprefix) !== false) {
                $shopArr = explode($prevprefix, $urlExplodedData[0]);
                $shop = !empty($shopArr[1]) ? $shopArr[1] : $shop;
            }
            $seller = $this->getMpHelper()->getSellerDataByShopUrl($shop);
            if (count($seller)) {
                $sellerId = $seller->setPageSize(1)
                    ->getFirstItem()
                    ->getSellerId();
            }
        }
        if ($sellerId) {

            $vendorParsedUrl = parse_url($this->getSellerUrl($currStoreId, $sellerId));
            if ($storeParsedUrl['host'] != $vendorParsedUrl['host']) {
                $storeParsedUrl['host'] = $vendorParsedUrl['host'];
            }
            $url = $storeParsedUrl['scheme']
            . '://'
            . $storeParsedUrl['host']
            . (isset($storeParsedUrl['port']) ? ':' . $storeParsedUrl['port'] : '')
            . $storeParsedUrl['path'];
        }
        return $url;
    }

    /**
     * getFinalUrl of seller
     *
     * @param string $url
     * @return string
     */
    public function getFinalUrl($currentUrl)
    {
        $urldata = explode('/', $currentUrl);
        $sslurldata = explode('//', $currentUrl);
        $urlString = $this->getBaseUrlWithoutSsl();
        if ($this->isLocalDomainSettingEnable()) {
            $seller = $this->getMpHelper()->getSellerDataByShopUrl($urldata[7]);
            if ($seller->getSize()) {
                foreach ($seller as $sellerdata) {
                    $id = $sellerdata->getSellerId();
                    $storeId = $this->getMpHelper()->getCurrentStoreId();
                    $domainData = $this->_domainFactory->create()
                        ->getCollection()
                        ->addFieldTofilter('seller_id', $sellerdata->getSellerId())
                        ->addFieldToFilter('vendor_store_id', $storeId)
                        ->setPageSize(1)
                        ->getFirstItem();

                    if ($domainData->getEntityId() && $domainData->getVendorUrl()) {
                        return $domainData->getVendorUrl();
                    }
                }
            }
        }
        return $sslurldata[0].'//'.$this->getPrefix().strtolower($urldata[7]).'.'.$urlString.'/';
    }

    public function getBaseUrlWithoutSSLFromCurrentUrl()
    {
        $urldata = explode('/', $this->getAdminBaseUrl());
        return $urldata[2];
    }

    /**
     * getBaseUrlFromCurrentUrl
     *
     * @return string
     */
    public function getBaseUrlFromCurrentUrl()
    {
        $sslurldata = explode('//', $this->getCurrentUrl());
        $urlString = $this->getBaseUrlWithoutSSLFromCurrentUrl();
        return $sslurldata[0].'//'.$urlString.'/';
    }

    /**
     * getShopNameByCurrentUrl
     *
     * @return string
     */
    public function getShopNameByCurrentUrl()
    {
        $shop = false;
        $currentUrl = $this->getCurrentUrl();
        if ($this->isLocalDomainSettingEnable()) {
            $baseUrlData = explode('/', $currentUrl);
            $baseUrl = $baseUrlData[0].'//'.$baseUrlData[2].'/';
            $shop = $this->_seller->create()
                ->getCollection()
                ->addFieldToFilter('seller_id', $this->getSellerIdFromVendorUrl($baseUrl))
                ->setPageSize(1)
                ->getFirstItem()
                ->getShopUrl();
        }
        if (!$shop) {
            $urldata = explode('/', $currentUrl);
            $urlString = $urldata[2];
            $domain = explode('.', $urldata[2], 2);
            if (strpos($domain[1], $this->getBaseUrlWithoutSsl()) !== false) {
                $shop = $domain[0];
                if ($this->getPrefix()) {
                    $shopArr = explode($this->getPrefix(), $domain[0]);
                    $shop = !empty($shopArr[1]) ? $shopArr[1] : $shop;
                }
            }
        }
        return $shop;
    }

    public function getSellerIdFromVendorUrl($vendorUrl)
    {
        return $this->_domainFactory->create()
            ->getCollection()
            ->addFieldTofilter('vendor_url', $vendorUrl)
            ->setPageSize(1)
            ->getFirstItem()
            ->getSellerId();
    }

    /**
     * checkShopExistsByCurrentUrl
     *
     * @return boolean||integer
     */
    public function checkShopExistsByCurrentUrl($storeId)
    {
        $result = false;
        $currentUrl = $this->getCurrentUrl();
        if ($this->isLocalDomainSettingEnable()) {
            $baseUrlData = explode('/', $currentUrl);
            $baseUrl = $baseUrlData[0].'//'.$baseUrlData[2].'/';
            $result = $this->getSellerIdFromVendorUrl($baseUrl);
        }
        if (!$result) {
            $urldata = explode('/', $currentUrl);
            $urlString = $urldata[2];
            $domain = explode('.', $urldata[2]);
            if ($urlString != $this->getBaseUrlWithoutSSLFromCurrentUrl()) {
                $shop = $domain[0];
                if ($this->getPrefixByStoreId($storeId)) {
                    $shopArr = explode($this->getPrefixByStoreId($storeId), $domain[0]);
                    $shop = !empty($shopArr[1]) ? $shopArr[1] : $shop;
                }
                $seller = $this->getMpHelper()->getSellerDataByShopUrl($shop);
                if (count($seller)) {
                    $result = $seller->setPageSize(1)->getFirstItem()->getSellerId();
                }
            }
        }
        return $result;
    }

    /**
     * get Seller Profile Url
     *
     * @return string
     */
    public function getProfileUrl()
    {
        $urldata = explode('/', $this->getCurrentUrl());
        $sslurldata = explode('//', $this->getCurrentUrl());
        return $sslurldata[0].'//'.$urldata[2].'/';
    }

    public function getBaseUrlWithoutSsl()
    {
        $urldata = explode('/', $this->getAdminBaseUrl());
        $urlString = $urldata[2];
        return $urlString;
    }

    /**
     * get Url
     *
     * @return string
     */
    public function getUrl($currentUrl, $targetUrl)
    {
        if ($this->isModuleEnable()) {
            $url = $this->getBaseUrlFromCurrentUrl().$targetUrl;
            if (strpos($targetUrl, 'marketplace/seller/profile/shop/') !== false) {
                return $this->getFinalUrl($url);
            } elseif (strpos($targetUrl, 'marketplace/seller/collection/shop/') !== false) {
                return $this->getFinalUrl($url).'collection';
            } elseif (strpos($targetUrl, 'marketplace/seller/location/shop/') !== false) {
                return $this->getFinalUrl($url).'location';
            } elseif (strpos($targetUrl, 'marketplace/seller/feedback/shop/') !== false) {
                return $this->getFinalUrl($url).'feedback';
            }
        }
        return $currentUrl;
    }

    /**
     * getProfileDetail
     *
     * @return \Webkul\Marketplace\Model\SellerFactory
     */
    public function getProfileDetail($value = '')
    {
        $shopUrl = $this->getMpHelper()->getProfileUrl();
        if (!$shopUrl) {
            $shopUrl = $this->getShopNameByCurrentUrl();
        }
        if ($shopUrl) {
            $data = $this->getMpHelper()->getSellerCollectionObjByShop($shopUrl);
            foreach ($data as $seller) {
                return $seller;
            }
        }
    }

    /**
     * getProductIds
     *
     * @return array
     */
    public function getProductIds()
    {
        $seller = $this->getProfileDetail();
        if ($seller) {
            $adminProList = [];
            $mpProColl = $this->mpProduct->create()
                ->getCollection()
                ->addFieldToFilter('seller_id', $seller->getSellerId())
                ->getColumnValues('mageproduct_id');
            if ($this->isAdminProductAllowed()) {
                $adminProList = $this->getAdminProducts();
            }
            $allowedProList = array_merge($mpProColl, $adminProList);
            return empty($allowedProList) ? [0] : $allowedProList;
        }
    }

    /**
     * getAdminProducts
     *
     * @return array
     */
    public function getAdminProducts()
    {
        $sellerProList = $this->mpProduct->create()
            ->getCollection()
            ->getColumnValues('mageproduct_id');
        $collection = $this->productFactory->create()->getCollection();
        if (!empty($sellerProList)) {
            $collection->addFieldToFilter('entity_id', ['nin' => $sellerProList]);
        }
        $adminProList = $collection->getColumnValues('entity_id');
        return empty($adminProList) ? [0] : $adminProList;
    }

    /**
     * get Seller Url
     *
     * @param $storeId
     * @param $sellerId
     *
     * @return string||boolean
     */
    public function getSellerUrl($storeId, $sellerId)
    {
        $url = false;
        if ($this->isModuleEnable() && $this->isLocalDomainSettingEnable()) {
            $url = $this->_domainFactory->create()
                        ->getCollection()
                        ->addFieldTofilter('seller_id', $sellerId)
                        ->addFieldToFilter('vendor_store_id', $storeId)
                        ->setPageSize(1)
                        ->getFirstItem()
                        ->getVendorUrl();
            if (!$url) {
                $url = $this->getSellerSubUrl($storeId, $sellerId);
            }
        } elseif ($this->isModuleEnable() && !$this->isLocalDomainSettingEnable()) {
            $url = $this->getSellerSubUrl($storeId, $sellerId);
        }
        return $url;
    }

    public function getCurrentDomainUrl($storeId, $sellerId)
    {
        if (empty($storeId)) {
            $storeId = '1';
        }
        return $this->_domainFactory->create()
                ->getCollection()
                ->addFieldTofilter('seller_id', $sellerId)
                ->addFieldToFilter('vendor_store_id', $storeId)
                ->setPageSize(1)
                ->getFirstItem()
                ->getVendorUrl();
    }

    /**
     * get Seller Url
     *
     * @param $storeId
     * @param $sellerId
     *
     * @return string
     */
    public function getSellerSubUrl($storeId, $sellerId)
    {
        $prefix = $this->getPrefixByStoreId($storeId);
        $shopUrl = $this->_seller->create()
                ->getCollection()
                ->addFieldToFilter('seller_id', $sellerId)
                ->setPageSize(1)
                ->getFirstItem()
                ->getShopUrl();
        return explode('//', $this->getCurrentUrl())[0].'//'.$prefix.$shopUrl.'.'.$this->getBaseUrlWithoutSsl().'/';
    }

    /**
     * get prefix by store Id
     * @param $storeId
     * @return string
     */
    public function getPrefixByStoreId($storeId)
    {
        return $this->scopeConfig->getValue(
            'sellersubdomain/settings/prefix',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * get default web url
     * @param $storeId
     * @return string
     */
    public function getDefaultWebUrl($storeId)
    {
        return $this->scopeConfig->getValue(
            'web/default/front',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * is url allowed on sellers domain
     *
     * @return boolean
     */
    public function isUrlAllowed()
    {
        $allowedPath = ["/collection", "/feedback", "/location", "/"];

        $parsedData = parse_url($this->getCurrentUrl());
        if (strpos($this->getCurrentUrl(), "checkout/cart/add")!==false ||
            strpos($this->getCurrentUrl(), "customer/section/load")!==false) {
            return true;
        }

        $baseUrlParsedData = parse_url($this->getAdminBaseUrl());

        if (in_array($parsedData["path"], $allowedPath)) {
            return true;
        } elseif ($baseUrlParsedData["host"] == $parsedData["host"]) {
            return true;
        }

        return false;
    }

    /**
     * get main domain url if url is not allowed
     *
     * @return string
     */
    public function getAllowedUrl()
    {
        $newUrl = "";
        if (!$this->isUrlAllowed()) {

            $parsedData = parse_url($this->getCurrentUrl());
            $newUrl = $parsedData['scheme']
                    . '://'
                    . $this->getBaseUrlWithoutSsl()
                    . (isset($parsedData['port']) ? ':' . $parsedData['port'] : '')
                    . $parsedData['path']
                    . (isset($parsedData['query']) ? ':' . $parsedData['query'] : '');
        }
        return $newUrl;
    }
}
