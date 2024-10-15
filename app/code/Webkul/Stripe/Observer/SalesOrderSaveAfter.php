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

namespace Webkul\Stripe\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Webkul\Stripe\Model\PaymentMethod;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Sales\Api\InvoiceRepositoryInterface;

class SalesOrderSaveAfter implements ObserverInterface
{
    /**
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Webkul\Stripe\Model\StripeOrderFactory $stripeOrder
     * @param \Webkul\Stripe\Helper\Data $helper
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder
     * @param \Magento\Framework\DB\Transaction $transaction
     * @param \Webkul\Stripe\Logger\Logger $logger
     */
    public function __construct(
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Webkul\Stripe\Model\StripeOrderFactory $stripeOrder,
        \Webkul\Stripe\Helper\Data $helper,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder,
        \Magento\Framework\DB\Transaction $transaction,
        \Webkul\Stripe\Logger\Logger $logger
    ) {
        $this->orderSender = $orderSender;
        $this->checkoutSession = $checkoutSession;
        $this->stripeOrder = $stripeOrder;
        $this->helper = $helper;
        $this->orderFactory = $orderFactory;
        $this->invoiceService = $invoiceService;
        $this->invoiceSender = $invoiceSender;
        $this->transactionBuilder = $transactionBuilder;
        $this->invoiceRepository  = $invoiceRepository;
        $this->transaction = $transaction;
        $this->logger = $logger;
    }
    /**
     * Sales Order Place After event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();
        if ($order) {
            $payment = $order->getPayment();
            if ($payment->getMethod() == PaymentMethod::METHOD_CODE) {
                
                $this->checkoutSession->setForceOrderMailSentOnSuccess(true);
                $stripeOrder = $this->stripeOrder->create()->getCollection()
                ->addFieldToFilter('quote_id', $order->getQuoteId());
                foreach ($stripeOrder as $stripe) {
                    if ($stripe['status'] == "checkout.session.completed") {
                        $order->setStripePaymentIntent($stripe['payment_intent']);
                        $order->save();
                        if ($order->canInvoice()) {
                            $transactionId = $this->createTransaction($order->getId(), $stripe['payment_intent']);
                
                            $invoice = $this->invoiceService->prepareInvoice($order);
                            $invoice->setTransactionId($transactionId);
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
                            $invoiceObj->setTransactionId($transactionId);
                            $invoiceObj->save();
                        }
                    }
                }
            }
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
        $response = \Stripe\PaymentIntent::retrieve($stripeResponse);

        $chargeId = $response['charges']['data'][0]['id']??$response['id'];

        try {
            $order = $this->orderFactory->create()->load($orderId);
            $smallcurrencyarray = ["bif", "clp", "djf", "gnf", "jpy", "kmf", "krw", "mga", "pyg", "rwf",
            "vnd", "vuv", "xaf", "xof", "xpf"];
            if (in_array(strtolower($order->getOrderCurrencyCode()), $smallcurrencyarray)) {
                $response['amount'] = $response['amount'];
                $response['amount_received'] = $response['amount_received'];
            } else {
                $response['amount'] = $response['amount'] / 100;
                $response['amount_received'] = $response['amount_received'] / 100;
            }
            $stripeFinalResponse['id'] = $chargeId;
            $stripeFinalResponse['customer_id'] = $response['customer'];
            $stripeFinalResponse['currency'] = $response['currency'];
            $stripeFinalResponse['amount'] = $response['amount'];
            $stripeFinalResponse['amount_received'] = $response['amount_received'];
            $stripeFinalResponse['capture_method'] = $response['capture_method'];
            $stripeFinalResponse['object'] = $response['object'];
            $stripeFinalResponse['status'] = $response['status'];
            $stripeFinalResponse['charge_id'] = $chargeId;
            $stripeFinalResponse['payment_intent_id'] = $response['id'];
            //get payment object from order object
            $payment = $order->getPayment();
            if ($payment != null) {
                $payment->setLastTransId($chargeId);
                $payment->setTransactionId($chargeId);
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
                ->setTransactionId($chargeId)
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
                $payment->save();
                $order->save();
        
                return $transaction->save()->getTransactionId();
            } else {
                \Stripe\Stripe::setApiKey($this->helper->getConfigValue("api_secret_key"));
                $response = \Stripe\PaymentIntent::retrieve($stripeResponse['id']);
                $this->createTransaction($orderId, $response);
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
