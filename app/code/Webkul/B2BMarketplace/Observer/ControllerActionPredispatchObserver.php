<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_B2BMarketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\B2BMarketplace\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session as CheckoutSession;

/**
 * Webkul B2BMarketplace CheckoutCartProductAddAfterObserver Observer.
 */
class ControllerActionPredispatchObserver implements ObserverInterface
{
    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;

    /**
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        \Webkul\B2BMarketplace\Helper\Data $helper,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Framework\UrlInterface $url
    ) {
        $this->_helper = $helper;
        $this->responseFactory = $responseFactory;
        $this->url = $url;
    }

    /**
     * Checkout cart product add event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_helper->isMpLoginPage()) {
            $redirectUrl = $this->url->getUrl('b2bmarketplace/supplier/login');
            $this->responseFactory->create()->setRedirect($redirectUrl)->sendResponse();
            $observer->getControllerAction()->getResponse()->setRedirect($redirectUrl);
            return $this;
        }
    }
}
