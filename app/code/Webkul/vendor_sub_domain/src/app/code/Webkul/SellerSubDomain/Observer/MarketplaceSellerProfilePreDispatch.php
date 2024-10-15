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
namespace Webkul\SellerSubDomain\Observer;

use Magento\Framework\Event\ObserverInterface;

class MarketplaceSellerProfilePreDispatch implements ObserverInterface
{

    /**
     * @var \Webkul\SellerSubDomain\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @param \Webkul\SellerSubDomain\Helper\Data      $helper
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param \Magento\Framework\App\RequestInterface  $request
     */
    public function __construct(
        \Webkul\SellerSubDomain\Helper\Data $helper,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->_helper = $helper;
        $this->_response = $response;
        $this->_request = $request;
    }

    /**
     * seller profile event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_helper->isModuleEnable()) {
            $profileUrl = $this->_helper->getProfileUrl();
            $landingPageUrl = $profileUrl."marketplace";
            $basempurl = str_replace("//marketplace", "/marketplace", $this->_helper->getAdminBaseUrl()."/marketplace");
            $isLandingPage = (strtolower($this->_request->getFullActionName()) == 'marketplace_index_index');
            if ($isLandingPage && $landingPageUrl != $this->_helper->getCurrentUrl() &&
            $this->_helper->getCurrentUrl()!=$basempurl) {
                $this->_response->setRedirect($basempurl);
            } elseif (strtolower($this->_request->getFullActionName()) == 'marketplace_seller_profile') {
                $this->_response->setRedirect($profileUrl);
            } elseif (strtolower($this->_request->getFullActionName()) == 'marketplace_seller_collection') {
                $this->_response->setRedirect($profileUrl.'collection');
            } elseif (strtolower($this->_request->getFullActionName()) == 'marketplace_seller_location') {
                $this->_response->setRedirect($profileUrl.'location');
            } elseif (strtolower($this->_request->getFullActionName()) == 'marketplace_seller_feedback') {
                $this->_response->setRedirect($profileUrl.'feedback');
            }
        }
        return $this;
    }
}
