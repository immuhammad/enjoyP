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
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;

class Success extends Action implements CsrfAwareActionInterface
{
    /**
     * @var array
     */
    protected $_publicActions = ['success'];
    
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;
    
    /**
     * @var Magento\Customer\Model\Session
     */
    protected $_customerSession;
    
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Quote\Model\QuoteManagement $quoteManagement
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     * @param \Webkul\Stripe\Logger\Logger $logger
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Session $customerSession,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Webkul\Stripe\Logger\Logger $logger,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_customerSession = $customerSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->session = $session;
        $this->checkoutSession = $checkoutSession;
        $this->quoteManagement = $quoteManagement;
        $this->quoteRepository = $quoteRepository;
        $this->_messageManager = $messageManager;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Get csrf validation exception
     *
     * @param RequestInterface $request
     * @return null
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
     * Handle payment success
     */
    public function execute()
    {
        try {
            $paramData = $this->getRequest()->getParams();
            $this->createOrder($paramData);
            return $this->resultRedirectFactory->create()->setPath('checkout/onepage/success', ['_current' => true]);
        } catch (\Exception $e) {
            $this->logger->info('Stripe exception '.$e->getMessage());
            $this->_messageManager->addError($e->getMessage());
            return $this->resultRedirectFactory->create()->setPath('checkout/cart', ['_current' => true]);
        }
    }
    
    /**
     * Create an order
     *
     * @param array $paramData
     * @return void
     */
    public function createOrder($paramData)
    {
        $quote = $this->checkoutSession->getQuote();
        if (!$this->_customerSession->isLoggedIn()) {
            $email = $quote->getBillingAddress()->getEmail();
            if ($quote->getBillingAddress()->getEmail() == null) {
                $email = $this->session->getStripeGuestUserEmail();
            }
            $quote->setCustomerId(null)
            ->setCustomerEmail($email)
            ->setCustomerIsGuest(true)
            ->setCustomerGroupId(\Magento\Customer\Model\Group::NOT_LOGGED_IN_ID);
        }
        $payment = $quote->getPayment();
        $additionalInfo = [];
        $payment->setMethod(\Webkul\Stripe\Model\PaymentMethod::METHOD_CODE);
        
        $quote->collectTotals();
        $order = $this->quoteManagement->placeOrder($quote->getId());

        $this->checkoutSession->setQuoteId(null);
        $quote->setIsActive(false);
        $this->quoteRepository->save($quote);
    }
}
