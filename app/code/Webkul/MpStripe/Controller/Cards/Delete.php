<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpStripe
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpStripe\Controller\Cards;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;

class Delete extends Action
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    private $formKeyValidator;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    private $mpHelper;

    /**
     * @var \Webkul\MpStripe\Model\StripeCustomer
     */
    private $stripeCustomerModel;

    /**
     * @var \Magento\Customer\Model\Url
     */
    private $customerUrl;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Webkul\MpStripe\Helper\Data $helper
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param FormKeyValidator $formKeyValidator
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Session $customerSession,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Webkul\MpStripe\Helper\Data $helper,
        \Magento\Customer\Model\Url $customerUrl,
        FormKeyValidator $formKeyValidator
    ) {
        $this->customerSession = $customerSession;
        $this->formKeyValidator = $formKeyValidator;
        $this->resultPageFactory = $resultPageFactory;
        $this->mpHelper = $mpHelper;
        $this->helper = $helper;
        $this->customerUrl = $customerUrl;
        parent::__construct($context);
    }

    /**
     * Retrieve customer session object.
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->customerSession;
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl =
        $this->customerUrl
        ->getLoginUrl();

        if (!$this->customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * Delete stripe cards.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            try {
                if (!$this->formKeyValidator->validate($this->getRequest())) {
                    return $this->resultRedirectFactory
                        ->create()
                        ->setPath('*/*/index', ['_secure' => $this->getRequest()->isSecure()]);
                }

                $requestData = $this->getRequest()->getParams();
                if (isset($requestData['card_id'])) {
                    $cardIds = $requestData['card_id'];
                    $response = $this->deleteCards($cardIds, $this->mpHelper->getCustomerId());
                    if ($response) {
                        $this->messageManager->addSuccess(__('Cards successfully deleted'));
                    } else {
                        $this->messageManager->addError(__('Not able to delete the cards'));
                    }
                }
            } catch (\Exception $e) {
                $this->messageManager->addError(__($e->getMessage()));
            }
        }

        return $this->resultRedirectFactory
            ->create()
            ->setPath('*/*/index', ['_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * DeleteCards function to delete cards of the customer.
     *
     * @param array $cards
     * @param integer $customerId
     * @return bool
     */
    public function deleteCards($cards = [], $customerId = 0)
    {
        if ($customerId && !empty($cards)) {
            $this->helper->setUpDefaultDetails();
            foreach ($cards as $card) {
                $paymentMethod = \Stripe\PaymentMethod::retrieve(
                    $card
                );
                $paymentMethod->detach();
            }
        } else {
            return false;
        }

        return true;
    }

    /**
     * Method to delete card
     *
     * @param object $card
     */
    public function deleteCard($card)
    {
        $card->delete();
    }
}
