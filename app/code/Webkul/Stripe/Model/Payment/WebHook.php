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
namespace Webkul\Stripe\Model\Payment;

use Webkul\Stripe\Api\WebhookInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\InvoiceRepositoryInterface;

class WebHook implements WebhookInterface
{
    /**
     * @var \Webkul\Stripe\Helper\Data
     */
    protected $helper;
    
    /**
     * @param \Webkul\Stripe\Helper\Data $helper
     * @param JsonHelper $jsonHelper
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender
     * @param \Magento\Sales\Model\OrderRepository $orderRepository
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param \Magento\Framework\Filesystem\Driver\File $driver
     * @param \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder
     * @param \Magento\Framework\DB\Transaction $transaction
     * @param \Webkul\Stripe\Logger\Logger $logger
     * @param \Webkul\Stripe\Model\StripeOrderFactory $stripeOrder
     */
    public function __construct(
        \Webkul\Stripe\Helper\Data $helper,
        JsonHelper $jsonHelper,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Framework\Filesystem\Driver\File $driver,
        \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder,
        \Magento\Framework\DB\Transaction $transaction,
        \Webkul\Stripe\Logger\Logger $logger,
        \Webkul\Stripe\Model\StripeOrderFactory $stripeOrder
    ) {
        $this->helper = $helper;
        $this->orderFactory = $orderFactory;
        $this->orderRepository = $orderRepository;
        $this->invoiceService = $invoiceService;
        $this->invoiceSender = $invoiceSender;
        $this->transactionBuilder = $transactionBuilder;
        $this->driver = $driver;
        $this->invoiceRepository  = $invoiceRepository;
        $this->jsonHelper = $jsonHelper;
        $this->transaction = $transaction;
        $this->logger = $logger;
        $this->stripeOrder = $stripeOrder;
    }

    /**
     * Handle payment success
     */
    public function executeWebhook()
    {
        $data = $this->driver->fileGetContents('php://input');
        $this->logger->info('Stripe message '.$data);

        $stripeResponse = $this->jsonHelper->jsonDecode($data);
        $webhookType = $stripeResponse['type'];
        switch ($webhookType) {
            case "checkout.session.completed":
                $quoteId = $stripeResponse['data']['object']['client_reference_id'];
                $paymentIntent = $stripeResponse['data']['object']['payment_intent'];
                $stripe = $this->stripeOrder->create()->getCollection()->addFieldToFilter('quote_id', $quoteId);
                if (!$stripe->getSize()) {
                    $stripeOrder = $this->stripeOrder->create();
                    $stripeOrder->setData('quote_id', $quoteId);
                    $stripeOrder->setData('status', $webhookType);
                    $stripeOrder->setData('payment_intent', $paymentIntent);
                    $stripeOrder->save();
                }
                $order = $this->orderFactory->create()->getCollection()
                ->addFieldToFilter('quote_id', $quoteId)->getFirstItem();
                $orderId = $order->getId();
                if ($order->getStripePaymentIntent() == null && $order->getTotalDue() > 0) {
                    $this->addPaymentIntentToOrder($orderId, $paymentIntent);
                    $order =  $this->orderRepository->get($orderId);
                    $this->logger->info('Stripe invoice '.$webhookType);
                    $this->createInvoice($order, $stripeResponse['data']['object']);
                }
                break;
            case "payment_intent.payment_failed":
                $paymentIntent = $stripeResponse['data']['object']['id'];
                $order = $this->orderFactory->create()
                ->getCollection()
                ->addFieldToFilter('stripe_payment_intent', $paymentIntent)->getFirstItem();
                $orderState = Order::STATE_PENDING_PAYMENT;
                $order->setState($orderState)->setStatus(Order::STATE_PENDING_PAYMENT);
                $order->save();
                break;
        }
        http_response_code(200);
    }

    /**
     * Add payment intent id to order
     *
     * @param int $orderId
     * @param array $paymentIntent
     */
    public function addPaymentIntentToOrder($orderId, $paymentIntent)
    {
        $order = $this->orderFactory->create()->load($orderId);
        $order->setStripePaymentIntent($paymentIntent);
        $order->save();
    }

    /**
     * Create invoice of order
     *
     * @param int $order
     * @param array $stripeResponse
     */
    public function createInvoice($order, $stripeResponse)
    {
        if ($order->canInvoice() && $order->getTotalDue() > 0) {
            $transactionId = $this->createTransaction($order->getId(), $stripeResponse);

            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->setTransactionId($stripeResponse['id']);
            $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
            $invoice->register();
            $invoice->save();
            $invoice->getOrder()->setIsInProcess(true);

            $transactionSave = $this->transaction->addObject(
                $invoice
            )->addObject(
                $invoice->getOrder()
            );
            $transactionSave->save();
            
            //send notification code
            $order->addStatusHistoryComment(
                __(
                    'Notified customer about invoice #%1.',
                    $invoice->getId()
                )
            )
            ->setIsCustomerNotified(true)
            ->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)
            ->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING)
            ->save();
            $this->invoiceSender->send($invoice);
            $invoiceObj = $this->invoiceRepository->get($invoice->getId());
            $invoiceObj->setTransactionId($stripeResponse['id']);
            $invoiceObj->save();
        }
    }

    /**
     * Create transaction for stripe placed order
     *
     * @param int $orderId
     * @param array $stripeResponse
     */
    public function createTransaction($orderId, $stripeResponse)
    {
        \Stripe\Stripe::setApiKey($this->helper->getConfigValue("api_secret_key"));
        $response = \Stripe\PaymentIntent::retrieve($stripeResponse['payment_intent']);
        try {
            $order = $this->orderFactory->create()->load($orderId);
            if ($order->getTotalDue() > 0) {
                $smallcurrencyarray = ["bif", "clp", "djf", "gnf", "jpy", "kmf", "krw", "mga", "pyg", "rwf",
                "vnd", "vuv", "xaf", "xof", "xpf"];
                if (in_array(strtolower($order->getOrderCurrencyCode()), $smallcurrencyarray)) {
                    $response['amount'] = $response['amount'];
                    $response['amount_received'] = $response['amount_received'];
                } else {
                    $response['amount'] = $response['amount'] / 100;
                    $response['amount_received'] = $response['amount_received'] / 100;
                }
                $stripeFinalResponse['id'] = $response['id'];
                $stripeFinalResponse['customer_id'] = $response['customer'];
                $stripeFinalResponse['currency'] = $response['currency'];
                $stripeFinalResponse['amount'] = $response['amount'];
                $stripeFinalResponse['amount_received'] = $response['amount_received'];
                $stripeFinalResponse['capture_method'] = $response['capture_method'];
                $stripeFinalResponse['object'] = $response['object'];
                $stripeFinalResponse['status'] = $response['status'];
                $stripeFinalResponse['charge_id'] = $response['charges']['data'][0]['id'];
                //get payment object from order object
                $payment = $order->getPayment();
                if ($payment != null) {
                    $payment->setLastTransId($stripeResponse['id']);
                    $payment->setTransactionId($stripeResponse['id']);
                    $payment->setAdditionalInformation(
                        [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $stripeFinalResponse]
                    );
                    $formatedPrice = $order->getBaseCurrency()->formatTxt(
                        $order->getGrandTotal()
                    );
            
                    $message = __('The authorized amount is %1.', $formatedPrice);
                    //get the object of builder class
                    $trans = $this->transactionBuilder;
                    $transaction = $trans->setPayment($payment)
                    ->setOrder($order)
                    ->setTransactionId($stripeResponse['id'])
                    ->setAdditionalInformation(
                        [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $stripeFinalResponse]
                    )
                    ->setFailSafe(true)
                    //build method creates the transaction and returns the object
                    ->build(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE);
            
                    $payment->addTransactionCommentsToOrder(
                        $transaction,
                        $message
                    );
                    $payment->setParentTransactionId(null);
                    $payment->save();
                    $order->save();
            
                    return $transaction->save()->getTransactionId();
                } else {
                    \Stripe\Stripe::setApiKey($this->helper->getConfigValue("api_secret_key"));
                    $response = \Stripe\PaymentIntent::retrieve($stripeResponse['id']);
                    $this->createTransaction($orderId, $response);
                }
            }
        } catch (\Exception $e) {
            \Stripe\Stripe::setApiKey($this->helper->getConfigValue("api_secret_key"));
            $response = \Stripe\PaymentIntent::retrieve($stripeResponse['id']);
            $this->createTransaction($orderId, $response);
            $this->logger->info('Create Transction'.$e->getMessage());
            $this->logger->info('Create Transction response'.$response);
        }
    }
}
