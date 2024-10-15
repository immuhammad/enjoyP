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

class Store
{
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
        \Webkul\SellerSubDomain\Helper\Data $helper
    ) {
        $this->_helper = $helper;
        $this->urlInterface = $urlInterface;
    }

    /**
     * @param \Magento\Store\Model\Store $subject
     * @param $result
     * @return string
     */
    public function afterGetBaseUrl(
        \Magento\Store\Model\Store $subject,
        $result
    ) {
        if (strpos($this->urlInterface->getCurrentUrl(), 'marketplace/catalog/view/id') !== false) {
            return $result;
        }
        return $this->_helper->getBaseUrl($result);
    }

    /**
     * @param \Magento\Store\Model\Store $subject
     * @param $result
     * @return string
     */
    public function afterGetCurrentUrl(
        \Magento\Store\Model\Store $subject,
        $url
    ) {
        return $this->_helper->getCurrentSellerUrlFromPrevUrl($url);
    }
}
