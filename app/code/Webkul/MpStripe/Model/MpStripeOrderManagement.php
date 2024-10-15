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
namespace Webkul\MpStripe\Model;

use Webkul\MpStripe\Model\Source\PaymentAction;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Webkul\Marketplace\Model\OrdersFactory as MpOrdersModel;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Model\Order;

class MpStripeOrderManagement implements \Webkul\MpStripe\Api\MpStripeOrderManagementInterface
{
    /**
     * @var array
     */
    protected $sellerInvoiceData = [];

    /**
     * @param \Webkul\Marketplace\Helper\Data $marketplaceData
     * @param \Webkul\MpStripe\Helper\Data $helper
     * @param \Webkul\Marketplace\Helper\Orders $ordersHelper
     * @param \Webkul\MpStripe\Logger\StripeLogger $logger
     * @param JsonHelper $jsonHelper
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder
     * @param \Magento\Framework\DB\Transaction $transaction
     * @param \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender
     * @param \Webkul\MpStripe\Model\StripeSellerFactory $stripeSellerModel
     * @param \Webkul\MpStripe\Model\StripeSellerRepository $sellerKeys
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param MpOrdersModel $mpOrdersModel
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     */
    public function __construct(
        \Webkul\Marketplace\Helper\Data $marketplaceData,
        \Webkul\MpStripe\Helper\Data $helper,
        \Webkul\Marketplace\Helper\Orders $ordersHelper,
        \Webkul\MpStripe\Logger\StripeLogger $logger,
        JsonHelper $jsonHelper,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Webkul\MpStripe\Model\StripeSellerFactory $stripeSellerModel,
        \Webkul\MpStripe\Model\StripeSellerRepository $sellerKeys,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        MpOrdersModel $mpOrdersModel,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Sales\Model\OrderFactory $orderFactory
    ) {
        $this->marketplaceData = $marketplaceData;
        $this->helper = $helper;
        $this->ordersHelper = $ordersHelper;
        $this->logger = $logger;
        $this->jsonHelper = $jsonHelper;
        $this->invoiceService = $invoiceService;
        $this->transactionBuilder = $transactionBuilder;
        $this->transaction = $transaction;
        $this->invoiceSender = $invoiceSender;
        $this->stripeSellerModel = $stripeSellerModel;
        $this->sellerKeys = $sellerKeys;
        $this->priceHelper = $priceHelper;
        $this->storeManager = $storeManager;
        $this->mpOrdersModel = $mpOrdersModel;
        $this->priceCurrency = $priceCurrency;
        $this->orderFactory = $orderFactory;
    }

    /**
     * Add payment intent id to order
     *
     * @param string $orderId
     * @param string $paymentIntent
     * @return mixed
     */
    public function addPaymentIntentToOrder($orderId, $paymentIntent)
    {
        $order = $this->orderFactory->create()->load($orderId);
        $order->setStripePaymentIntent($paymentIntent);
        $order->save();
    }

    /**
     * ManageChargeSuccess function
     *
     * @param array $paymentDetails
     * @param Object $order
     * @param int $orderId
     * @param array $transfers
     * @param array $data
     * @return mixed
     */
    public function manageChargeSuccess($paymentDetails, $order, $orderId, $transfers, $data)
    {
        foreach ($paymentDetails as $paymentDetail) {
                            
            $cart = $paymentDetail['cart'];

            /*
            * check if seller charge or admin charge
            */
            if ($cart['seller'] || ($cart['products'] !== null)) {

                $invoice = $this->createInvoice($order, $cart, $data['data']['object']);
                $this->manageSellerFee($invoice, $order, $cart, $orderId, $transfers);
                
            } else {
                $invoiceId = $this->createShippingInvoice($order, $cart, $data['data']['object']);
            }
            $order->setBaseShippingAmount(
                $this->priceCurrency->round(
                    $order->getShippingAmount()
                )
            )->save();
            if (count($this->sellerInvoiceData) > 0) {
                $payment = $order->getPayment();
                $payment->setAdditionalInformation(
                    'stripeitem__invoice__data',
                    $this->jsonHelper->jsonEncode($this->sellerInvoiceData)
                );
                $payment->save();
            }
        }
    }

    /**
     * CreateInvoice function to create invoice seller wise.
     *
     * @param object $order
     * @param array $cart
     * @param array $stripeResponse
     */
    public function createInvoice($order, $cart = [], $stripeResponse = [])
    {
        $this->logger->critical('createInvoice');
        try {
            $orderId = $order->getId();
            $sellerId = $cart['seller'];

            /**
             * $transactionId create transaction for the current stripeResponse.
             *
             * @var int
             */
            $transactionId = $this->createTransaction($order, $stripeResponse);
            /**
             * $itemsarray get item data for invoice.
             *
             * @var array
             */
            $itemsarray = $this->helper->_getItemQtys($order, explode(',', $cart['products']));

            if (!empty($itemsarray)) {
                $invoice =
                $this->invoiceService
                ->prepareInvoice(
                    $order,
                    $itemsarray['data']
                );

                $invoice->setTransactionId($transactionId);
                $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                $invoice->setShippingAmount($cart['shippingprice']);
                $invoice->setBaseShippingInclTax($cart['shippingprice']);
                $invoice->setBaseShippingAmount($cart['shippingprice']);
                $invoice->setDiscountAmount($cart['discount']);
                $invoice->setTaxAmount($cart['taxamount']);
                $invoice->setBaseTaxAmount($cart['taxamount']);
                $invoice->setSubtotal($cart['invoiceprice']);
                $invoice->setBaseSubtotal($cart['invoiceprice']);
                $invoice->setGrandTotal(
                    $this->priceCurrency->round(
                        $cart['invoiceprice'] + $cart['shippingprice'] + $cart['taxamount'] - $cart['discount']
                    )
                );
                $invoice->setBaseGrandTotal(
                    $this->priceCurrency->round(
                        $cart['invoiceprice'] + $cart['shippingprice'] + $cart['taxamount'] - $cart['discount']
                    )
                );
                $invoice->register();
                $invoice->save();
                $invoice->getOrder()->setIsInProcess(true);
                
                $transactionSave =
                $this->transaction
                    ->addObject($invoice->getOrder());
                $transactionSave->save();
                $this->invoiceSender->send($invoice);
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
                return $invoice;
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());

            return false;
        }
    }

    /**
     * ManageSellerFee function
     *
     * @param Object $invoice
     * @param Object $order
     * @param array $cart
     * @param int $orderId
     * @param array $transfers
     * @return mixed
     */
    public function manageSellerFee($invoice, $order, $cart, $orderId, $transfers)
    {
        $managecomm = 0;
        if ($invoice->getId()) {
            $this->sellerInvoiceData[$cart['seller']]['order_id'] = $order->getId();
            $this->sellerInvoiceData[$cart['seller']]['invoice_id'] = $invoice->getId();
            /*
            * Pay seller fee after successfull invoice
            */
            if ($managecomm==0) {
                $this->ordersHelper->getCommssionCalculation($order);
                $managecomm++;
            }
            if ($cart['seller'] && $cart['stripe_user_id'] != '') {
                $this->ordersHelper
                ->paysellerpayment($order, $cart['seller'], $invoice->getTransactionId());
                $trackingcol1 = $this->mpOrdersModel->create()
                ->getCollection()
                ->addFieldToFilter(
                    'order_id',
                    $orderId
                )
                ->addFieldToFilter(
                    'seller_id',
                    $cart['seller']
                );
                $stripeSeller = $this->stripeSellerModel->create()
                    ->getCollection()
                    ->addFieldToFilter('seller_id', ['eq' => $cart['seller']])
                    ->getFirstItem();
                
                foreach ($trackingcol1 as $row) {
                    foreach ($transfers['data'] as $sellerTransfer) {
                        if ($sellerTransfer['destination'] == $stripeSeller->getStripeUserId()) {
                            $row->setStripePaymentIntentTransferId($sellerTransfer['id']);
                            $row->save();
                            break;
                        }
                    }
                    $row->setOrderStatus('processing');
                    $row->save();
                }
            }
        }
    }
    
    /**
     * Get store base currency code
     *
     * @return string
     */
    public function getBaseCurrencyCode()
    {
        return $this->storeManager->getStore()->getBaseCurrencyCode();
    }

    /**
     * CreateTransaction function to create payment transations.
     *
     * @param object $order
     * @param array  $stripeResponse
     *
     * @return int
     */
    public function createTransaction($order = null, $stripeResponse = [])
    {
        $orderId = $order->getId();
        $order = $this->orderFactory->create()->loadByIncrementId($order->getIncrementId());
        $orderItems = $order->getAllItems();
        $productId = "";
        foreach ($orderItems as $item) {
            $productId = $item->getProductId();
            break;
        }
        $stripeCustomerId = $this->marketplaceData->getSellerIdByProductId($productId);

        $collectionData = "";
        if ($stripeCustomerId != 0 &&
            $this->helper->isDirectCharge()
        ) {
            $collectionData = $this->sellerKeys->getBySellerId($stripeCustomerId);
            $response = \Stripe\PaymentIntent::retrieve(
                $stripeResponse['payment_intent'],
                ['stripe_account' => $collectionData["stripe_user_id"]]
            );
        } else {
            $response = \Stripe\PaymentIntent::retrieve(
                $stripeResponse['payment_intent']
            );
        }

        try {
            $stripeFinalResponse['id'] = $response['id'];
            if ($response['customer'] != null) {
                $stripeFinalResponse['customer_id'] = $response['customer'];
            }
            $stripeFinalResponse['currency'] = $response['currency'];
            $stripeFinalResponse['amount'] = $this->convertAmount($response['amount']);
            $stripeFinalResponse['amount_received'] = $this->convertAmount($response['amount_received']);
            $stripeFinalResponse['capture_method'] = $response['capture_method'];
            $stripeFinalResponse['object'] = $response['object'];
            $stripeFinalResponse['status'] = $response['status'];
            $stripeFinalResponse['charge_id'] = $response['charges']['data'][0]['id'];
            $order = $this->orderFactory->create()->load($orderId);
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
                if ($stripeCustomerId != 0 &&
                    $this->helper->isDirectCharge()) {
                    $response = \Stripe\PaymentIntent::retrieve(
                        $stripeResponse['payment_intent'],
                        ['stripe_account' => $collectionData["stripe_user_id"]]
                    );
                } else {
                    $response = \Stripe\PaymentIntent::retrieve(
                        $stripeResponse['payment_intent']
                    );
                }
                $this->createTransaction($orderId, $response);
            }
        } catch (\Exception $e) {
            if ($stripeCustomerId != 0 &&
                $this->helper->isDirectCharge()) {
                $response = \Stripe\PaymentIntent::retrieve(
                    $stripeResponse['payment_intent'],
                    ['stripe_account' => $collectionData["stripe_user_id"]]
                );
            } else {
                $response = \Stripe\PaymentIntent::retrieve(
                    $stripeResponse['payment_intent']
                );
            }

            $this->logger->critical('Create Transction error '.$e->getMessage());
            // $this->logger->critical('Create Transction response '.$response);
            $this->createTransaction($orderId, $response);
        }
    }

    /**
     * ConvertAmount function
     *
     * @param int $amount
     * @return mixed
     */
    public function convertAmount($amount)
    {
        $baseCurrencyCode = strtolower($this->getBaseCurrencyCode());
        $smallcurrencyarray = ["bif", "clp", "djf", "gnf", "jpy", "kmf", "krw", "mga", "pyg", "rwf",
                                "vnd", "vuv", "xaf", "xof", "xpf"];
        if (!in_array($baseCurrencyCode, $smallcurrencyarray)) {
            $amount = $amount/100;
        }
        $formattedCurrencyValue = $this->priceHelper->currency($amount, true, false);
        return $formattedCurrencyValue;
    }

    /**
     * Create remaining order shipping amount invoice.
     *
     * @param Object $order
     * @param array $cart
     * @param array $stripeResponse
     * @return bool|int
     */
    protected function createShippingInvoice($order, $cart = [], $stripeResponse = [])
    {
        $shippingDiscount = $order->getBaseShippingDiscountAmount();
        $shippingAmount = $cart['shippingprice'];
        $taxAmount = $cart['taxamount'];
        $subTotal = $shippingAmount+$taxAmount-$shippingDiscount;
        if ($shippingAmount || $taxAmount) {
            try {
                /**
                 * $transactionId create transaction for the current stripeResponse.
                 *
                 * @var int
                 */
                $transactionId = $this->createTransaction($order, $stripeResponse);
                
                $invoice =
                $this->invoiceService
                ->prepareInvoice($order, []);

                $invoice->setTransactionId($transactionId);
                $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                $invoice->setShippingAmount($shippingAmount);
                $invoice->setBaseShippingInclTax($shippingAmount);
                $invoice->setBaseShippingAmount($shippingAmount);
                $invoice->setTaxAmount($taxAmount);
                $invoice->setBaseTaxAmount($taxAmount);
                $invoice->setSubtotal($subTotal);
                $invoice->setBaseSubtotal($subTotal);
                $invoice->setDiscountAmount($shippingDiscount);
                $invoice->setGrandTotal($shippingAmount + $taxAmount);
                $invoice->setBaseGrandTotal(($shippingAmount + $taxAmount));
                $invoice->register();
                $invoice->save();
                $invoice->getOrder()->setIsInProcess(true);
                
                $transactionSave =
                $this->transaction->addObject(
                    $invoice->getOrder()
                );
                $transactionSave->save();

                $invoiceId = $invoice->getId();

                $this->invoiceSender->send($invoice);
                $order->addStatusHistoryComment(
                    __('Notified customer about invoice #%1.', $invoice->getId())
                )
                    ->setIsCustomerNotified(true)
                    ->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)
                    ->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING)
                    ->save();

                return $invoiceId;
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());

                return false;
            }
        } else {
            return false;
        }
    }
}
