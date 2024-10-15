<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvancedBookingSystem
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedBookingSystem\Controller\Cancellation;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Helper\Guest as GuestHelper;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Process cancellation request
 */
class Cancel extends Action implements HttpPostActionInterface
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var PageFactory
     */
    protected $_customerSession;

    /**
     * @var \Webkul\MpAdvancedBookingSystem\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Webkul\MpAdvancedBookingSystem\Model\CancellationFactory
     */
    protected $cancellationFactory;

    /**
     * @var GuestHelper
     */
    protected $guestHelper;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $_url;

    /**
     * Constructor
     *
     * @param Context                                                   $context
     * @param \Magento\Customer\Model\Url                               $url
     * @param \Magento\Customer\Model\Session                           $customerSession
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Data               $helper
     * @param \Webkul\MpAdvancedBookingSystem\Model\CancellationFactory $cancellationFactory
     * @param GuestHelper                                               $guestHelper
     * @param OrderRepositoryInterface                                  $orderRepository
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Url $url,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\MpAdvancedBookingSystem\Helper\Data $helper,
        \Webkul\MpAdvancedBookingSystem\Model\CancellationFactory $cancellationFactory,
        GuestHelper $guestHelper,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->_url = $url;
        $this->_customerSession = $customerSession;
        $this->_helper = $helper;
        $this->cancellationFactory = $cancellationFactory;
        $this->guestHelper = $guestHelper;
       // $this->emailHelper = $emailHelper;
        $this->orderRepository = $orderRepository;
        parent::__construct($context);
    }

    /**
     * Execute
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        if (!$this->_customerSession->isLoggedIn()) {
            $result = $this->guestHelper->loadValidOrder($this->getRequest());
            if ($result instanceof ResultInterface) {
                return $result;
            }
        }

        if (!empty($this->getRequest()->getParam('item_id'))) {
            try {
                $itemId = $this->getRequest()->getParam('item_id');
                $orderId = $this->getRequest()->getParam('order_id');
                $item = $this->_helper->getOrderItem($itemId);
                $status = $this->_helper->getCancellationStatus($orderId, $item->getId());
                $invoiceItem = $this->_helper->getInvoiceItemByOrderItemId($itemId);
            } catch (\Exception $e) {
                $status = false;
            }

            if ($status && $item->getIsCancellationAvailable() && $item->canRefund()) {
                $cancelCharge = $this->_helper->getConfigData(
                    'mpadvancedbookingsystem/cancellation/cancellation_charge'
                );
                $cancelAmount = 0;
                if ($cancelCharge) {
                    $cancelAmount = ($this->getTotalAmount($invoiceItem) * $cancelCharge) * .01;
                }

                $cancellation = $this->cancellationFactory->create();
                $cancellation->setOrderId($orderId);
                $cancellation->setOrderItemId($item->getId());
                $cancellation->setCreditMemoId('');
                $cancellation->setCancellationCharge($cancelAmount);
                $cancellation->setStatus(0);
                $cancellation->save();

                $this->messageManager->addSuccessMessage(__("Cancellation Request Send"));
                try {
                    $this->sendCancellationEmail($orderId, $invoiceItem);
                } catch (\Exception $e) {
                    $this->messageManager->addNoticeMessage($e->getMessage());
                }
            } else {
                $this->messageManager->addErrorMessage(__("Cancellation is not allowed"));
            }
        } else {
            $this->messageManager->addErrorMessage(__("Order Item not Found"));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->_customerSession->isLoggedIn()) {
            return $resultRedirect->setPath('sales/order/history/');
        } else {
            return $resultRedirect->setPath('sales/guest/view');
        }
    }

    /**
     * Return the total amount minus discount
     *
     * @param OrderItem|InvoiceItem|CreditmemoItem $item
     * @return mixed
     */
    protected function getTotalAmount($item)
    {
        $totalAmount = $item->getRowTotal()
            + $item->getTaxAmount()
            + $item->getDiscountTaxCompensationAmount()
            + $item->getWeeeTaxAppliedRowAmount()
            - $item->getDiscountAmount();

        return $totalAmount;
    }

    /**
     * Cancellation email to admin
     *
     * @param int $orderId
     * @param object $item
     * @return null
     */
    protected function sendCancellationEmail($orderId, $item)
    {
        $data = [];
        $order = $this->orderRepository->get($orderId);

        $data['order_increment_id'] = $order->getIncrementId();
        $data['customer_name'] = (string)__('Admin');
        $data['product_name'] = $item->getName();
        $data['product_sku'] = $item->getSku();
        $data['subject'] = (string)__('Cancellation request generated');

        //$this->emailHelper->sendCancellationMailToAdmin($data);
    }
}
