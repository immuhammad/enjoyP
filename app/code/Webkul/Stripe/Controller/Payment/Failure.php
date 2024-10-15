<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Stripe
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Stripe\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session;
use Magento\Sales\Model\Order;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;

class Failure extends Action implements CsrfAwareActionInterface
{
    /**
     * @var array
     */
    protected $_publicActions = ['failure'];
    
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    
    /**
     * @var Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Webkul\Stripe\Helper\Data
     */
    protected $helper;
    
    /**
     * __construct
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Checkout\Model\Type\Onepage $onePage
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param Session $customerSession
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Webkul\Stripe\Helper\Data $helper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Checkout\Model\Type\Onepage $onePage,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        Session $customerSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Webkul\Stripe\Helper\Data $helper,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->helper = $helper;
        $this->orderFactory = $orderFactory;
        $this->onePage = $onePage;
        $this->customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
        $this->_messageManager = $messageManager;
    }

    /**
     * Get csrf validation exception
     *
     * @param RequestInterface $request
     * @return bool
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * Csrf validation
     *
     * @param RequestInterface $request
     * @return bool
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * Handle payment failure
     */
    public function execute()
    {
        try {
            $orderId = $this->onePage->getCheckout()->getLastOrderId();
            $order = $this->orderFactory->create()->load($orderId);
            $orderState = Order::STATE_PENDING_PAYMENT;
            $order->setState($orderState)->setStatus(Order::STATE_PENDING_PAYMENT);
            $order->save();
            return $this->resultRedirectFactory->create()->setPath('checkout/onepage/failure', ['_current' => true]);
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
            return $this->resultRedirectFactory->create()->setPath('checkout/onepage/failure', ['_current' => true]);
        }
    }
}
