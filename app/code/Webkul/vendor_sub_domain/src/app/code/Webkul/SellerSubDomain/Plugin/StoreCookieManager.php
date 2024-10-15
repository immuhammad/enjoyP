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
namespace Webkul\SellerSubDomain\Plugin;

use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Store\Api\Data\StoreInterface;

class StoreCookieManager
{
    const COOKIE_NAME = 'store';

    /**
     * @var UrlInterface
     */
    protected $urlInterface;

    /**
     * @var \Webkul\SellerSubDomain\Helper\Data
     */
    protected $_helper;

    /**
     * @param \Magento\Framework\UrlInterface     $urlInterface
     * @param \Webkul\SellerSubDomain\Helper\Data $helepr
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlInterface,
        \Webkul\SellerSubDomain\Helper\Data $helper,
        CookieMetadataFactory $cookieMetadataFactory,
        CookieManagerInterface $cookieManager
    ) {
        $this->_helper = $helper;
        $this->urlInterface = $urlInterface;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->cookieManager = $cookieManager;
    }

    /**
     * @param \Magento\Store\Model\StoreCookieManager $subject
     * @param callable $proceed
     * @return StoreInterface $store
     */
    public function aroundSetStoreCookie(
        \Magento\Store\Model\StoreCookieManager $subject,
        callable $proceed,
        StoreInterface $store
    ) {
        $cookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setHttpOnly(true)
            ->setDurationOneYear()
            ->setDomain($this->_helper->getBaseUrlWithoutSsl())
            ->setPath($store->getStorePath());

        $this->cookieManager->setPublicCookie(self::COOKIE_NAME, $store->getCode(), $cookieMetadata);
    }

    /**
     * @param \Magento\Store\Model\StoreCookieManager $subject
     * @param callable $proceed
     * @return StoreInterface $store
     */
    public function aroundDeleteStoreCookie(
        \Magento\Store\Model\StoreCookieManager $subject,
        callable $proceed,
        StoreInterface $store
    ) {
        $cookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setDomain($this->_helper->getBaseUrlWithoutSsl())
            ->setPath($store->getStorePath());

        $this->cookieManager->deleteCookie(self::COOKIE_NAME, $cookieMetadata);
    }
}
