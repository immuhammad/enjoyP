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

use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\App\RequestInterface;
use Webkul\MpStripe\Logger\StripeLogger;
use Webkul\MpStripe\Model\Source\IntegrationType;
use Stripe\StripeClient;

class PaymentMethod extends AbstractMethod
{
    public const METHOD_CODE = 'mpstripe';

    /**
     * @var string
     */
    protected $_code = self::METHOD_CODE;

    /**
     * Availability option.
     *
     * @var bool
     */
    protected $_isInitializeNeeded = false;

    /**
     * @var bool
     */
    protected $_canAuthorize = true;

    /**
     * @var bool
     */
    protected $_canRefund = true;

    /**
     * @var bool
     */
    protected $_canUseInternal = false;

    /**
     * @var bool
     */
    protected $_canUseCheckout = true;

    /**
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;

    /**
     * @var \Webkul\MpStripe\Helper\Data
     */

    private $helper;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    private $mpHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * $newvar variable to check if seller shipping used.
     *
     * @var string
     */
    private $newvar;

    /**
     * @var $counterVal variable (count in final cart)
     */
    private $counterVal;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * $_commission admin commission.
     *
     * @var decimal
     */
    protected $_commission = 0;

    /**
     * @var boolean
     */
    private $customerStripeId = false;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var int
     */
    private $adminShipTaxAmt = 0;

    /**
     * @var \Webkul\MpStripe\Logger\StripeLogger
     */
    protected $logger;

    /**
     * @var \Webkul\MpStripe\Model\StripeCustomerFactory
     */
    private $stripeCustomer;

    /**
     * @var string
     */
    private $stripeKey;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Webkul\MpStripe\Helper\Data $helper
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Webkul\MpStripe\Model\StripeCustomerFactory $stripeCustomer
     * @param StripeLogger $stripeLogger
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param RequestInterface $request
     * @param \Webkul\Marketplace\Model\SaleslistFactory $saleslistFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Webkul\Marketplace\Model\OrdersFactory $mpordersFactory
     * @param \Magento\Sales\Model\Order\Payment\Transaction $orderTrans
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Sales\Model\Order\InvoiceFactory $invoice
     * @param \Webkul\MpStripe\Model\StripeSellerRepository $sellerKeys
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Webkul\MpStripe\Helper\Data $helper,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Webkul\MpStripe\Model\StripeCustomerFactory $stripeCustomer,
        StripeLogger $stripeLogger,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        PriceCurrencyInterface $priceCurrency,
        RequestInterface $request,
        \Webkul\Marketplace\Model\SaleslistFactory $saleslistFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Webkul\Marketplace\Model\OrdersFactory $mpordersFactory,
        \Magento\Sales\Model\Order\Payment\Transaction $orderTrans,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Sales\Model\Order\InvoiceFactory $invoice,
        \Webkul\MpStripe\Model\StripeSellerRepository $sellerKeys,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
        $this->jsonHelper = $jsonHelper;
        $this->helper = $helper;
        $this->mpHelper = $mpHelper;
        $this->customerSession = $customerSession;
        $this->priceCurrency = $priceCurrency;
        $this->request = $request;
        $this->stripeCustomer = $stripeCustomer;
        $this->logger = $stripeLogger;
        $this->messageManager = $messageManager;
        $this->saleslistFactory = $saleslistFactory;
        $this->productFactory = $productFactory;
        $this->mpordersFactory = $mpordersFactory;
        $this->orderTrans = $orderTrans;
        $this->orderFactory = $orderFactory;
        $this->invoice = $invoice;
        $this->sellerKeys = $sellerKeys;
        $status = $this->initializeStripe($this->helper->getConfigValue('api_key'));
    }

    /**
     * Set api key for payment  >> sandbox api key or live api key
     *
     * @param boolean $stripeKey
     * @return void|bool
     */
    public function initializeStripe($stripeKey = false)
    {
        if ($stripeKey) {
            $this->secretKey = $stripeKey;
            $this->helper->setUpDefaultDetails();
        } else {
            return false;
        }
    }

    /**
     * Authorizes specified amount.
     *
     * @param InfoInterface $payment
     * @param float         $amount
     *
     * @return $this
     *
     * @throws LocalizedException
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $token = $this->getInfoInstance()->getAdditionalInformation('token');

        $paymentSource = $this->getInfoInstance()->getAdditionalInformation('paymentSource');
        $cardNumber = $this->getInfoInstance()->getAdditionalInformation('cardNumber');
        $this->customerStripeId = $this->getInfoInstance()->getAdditionalInformation('stripeCustomerId');
        $saveCardForCustomer = $this->getInfoInstance()->getAdditionalInformation('saveCardForCustomer');
        $paymentDetailsArray = [];
        $email = $this->getInfoInstance()->getAdditionalInformation('email');
        $address = $this->getInfoInstance()->getAdditionalInformation('address');
        $order = $payment->getOrder();
    }

    /**
     * Refund refund online transactions.
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float                                $amount
     *
     * @return this current class object
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        try {
            $adminAmountForRefund = $amount;
            $order = $payment->getOrder();
            $orderItems = $order->getAllItems();
            $productId = "";
            foreach ($orderItems as $item) {
                $productId = $item->getProductId();
                break;
            }
            $stripeCustomerId = $this->mpHelper->getSellerIdByProductId($productId);
            if ($this->helper->isDirectCharge()) {
                $collectionData = $this->sellerKeys->getBySellerId($stripeCustomerId);
            }

            $orderData = $payment->getOrder()->getData();
            $orderId = $orderData['entity_id'];
            $refundData = $this->request->getParams();
            $transactionId = $payment->getParentTransactionId();
            $buyerId = $order->getCustomerId();
            $buyerEmail = $order->getCustomerEmail();
            $isAdminProduct = true;
            $calculatedTax = 0;
            $appliedCouponAmount = 0;

            // refund calculation check
            $adjustmentPositive = $refundData['creditmemo']['adjustment_positive'];
            $adjustmentNegative = $refundData['creditmemo']['adjustment_negative'];
            
            if ($adjustmentNegative >= $adjustmentPositive) {
                $adjustmentNegative = $adjustmentNegative - $adjustmentPositive;
            } else {
                $adjustmentNegative = $adjustmentNegative - $adjustmentPositive;
                $this->checkSellerAmountAvailable($amount, $orderId, $adjustmentNegative);
            }
            
            $creditmemoItemsIds = [];
            $creditmemoItemsQty = [];
            $creditmemoItemsPrice = [];
            if (!isset($refundData['creditmemo']['items'])) {
                $refundData['creditmemo']['items'] = [];
            }
            foreach ($refundData['creditmemo']['items'] as $key => $value) {
                $productId = '';
                $sellerProducts = $this->saleslistFactory->create()->getCollection()
                ->addFieldToFilter('order_item_id', $key)
                ->addFieldToFilter('order_id', $orderId);
                foreach ($sellerProducts as $sellerProduct) {
                    $productId = $sellerProduct['mageproduct_id'];
                    /**
                     * check if admin product or not
                     */
                    if ($sellerProduct->getSellerId()) {
                        $isAdminProduct = false;
                    }
                    $appliedCouponAmount = $sellerProduct["applied_coupon_amount"] + $appliedCouponAmount;
                }
                if ($productId) {
                    $itemFactory = $this->productFactory->create();
                    $item = $this->loadProductId($itemFactory, $productId);
                    $creditmemoItemsIds[$key] = $productId;
                    $creditmemoItemsQty[$key] = $value['qty'];
                    $creditmemoItemsPrice[$key] = $this->getItemPrice(
                        $orderId,
                        $item->getId()
                    ) * $value['qty'];
                }
            }

            arsort($creditmemoItemsPrice);

            $sellerArr = [];

            if (!empty($this->customerSession->getMpCreditmemoCommissionRate())) {
                $creditmemoCommissionRateArr = $this->customerSession->getMpCreditmemoCommissionRate();
                $this->customerSession->setMpCreditmemoCommissionRate([]);
            } else {
                $creditmemoCommissionRateArr = [];
            }

            foreach ($creditmemoItemsPrice as $key => $item) {
                $sellerProducts = $this->saleslistFactory->create()->getCollection()
                                        ->addFieldToFilter('order_item_id', $key)
                                        ->addFieldToFilter('order_id', $orderId);
                foreach ($sellerProducts as $sellerProduct) {
                    if ($sellerProduct["order_item_id"]!=$key) {
                        continue;
                    }

                    $refundedQty = $creditmemoItemsQty[$key];
                    $refundedPrice = $creditmemoItemsPrice[$key];
                    $productId = $creditmemoItemsIds[$key];

                    if ($adjustmentNegative * 1) {
                        if ($adjustmentNegative >= $sellerProduct['total_amount']) {
                            $adjustmentNegative = $adjustmentNegative - $sellerProduct['total_amount'];
                            $updatedPrice = $sellerProduct['total_amount'];
                            $refundedPrice = 0;
                        } else {
                            $refundedPrice = $refundedPrice - $adjustmentNegative;
                            $updatedPrice = $sellerProduct['total_amount'] - $refundedPrice;
                            $adjustmentNegative = 0;
                        }
                    } else {
                        $updatedPrice = $sellerProduct['total_amount'] - $refundedPrice;
                    }
                    if (!($sellerProduct['total_amount'] * 1)) {
                        $sellerProduct['total_amount'] = 1;
                    }
                    if ($sellerProduct['total_commission'] * 1) {
                        $commissionPercentage = (
                        $sellerProduct['total_commission'] * 100
                            ) / $sellerProduct['total_amount'];
                    } else {
                        $commissionPercentage = 0;
                    }
                    $updatedCommission = ($refundedPrice * $commissionPercentage) / 100;
                    $updatedSellerAmount = $refundedPrice - $updatedCommission;

                    $taxAmount = $this->calculateRefundTaxAmount($refundedQty, $sellerProduct);

                    if (!$this->mpHelper->getConfigTaxManage()
                    ) {
                        $taxAmount = 0;
                    }
                    $updatedSellerAmount = $updatedSellerAmount + $taxAmount;
                    if (!isset($sellerArr[$sellerProduct['seller_id']]['seller_refund'])) {
                        $sellerArr[$sellerProduct['seller_id']]['seller_refund'] = 0;
                    }

                    if (!isset($sellerArr[$sellerProduct['seller_id']]['updated_commission'])) {
                        $sellerArr[$sellerProduct['seller_id']]['updated_commission'] = 0;
                    }

                    $sellerArr[$sellerProduct['seller_id']]['seller_refund'] =
                    $sellerArr[$sellerProduct['seller_id']]['seller_refund'] + $updatedSellerAmount;
                    $sellerArr[$sellerProduct['seller_id']]['updated_commission'] =
                    $sellerArr[$sellerProduct['seller_id']]['updated_commission'] + $updatedCommission;
                }
            }
            
            $adminShipping = 0;
            $i = 0;
            $shippingCharges = 0;
            $totalSellerRefund = 0;
            $totalSellerRefundWithoutCommission = 0;
            $transferIdArr = [];
            foreach ($sellerArr as $sellerId => $value) {
                $shippingCharges = 0;
                $codCharges = 0;
                $trackingcoll = $this->mpordersFactory->create()->getCollection()
                ->addFieldToFilter('order_id', $orderId)
                ->addFieldToFilter('seller_id', $sellerId);
                foreach ($trackingcoll as $tracking) {
                    $tranferId = $tracking->getStripePaymentIntentTransferId();
                    $transferIdArr[$sellerId] = $tranferId;
                    $codCharges = $tracking->getCodCharges();
                    $shippingCharges = $tracking->getShippingCharges();
                    $refundCreditMemoShippingAmount = 0;
                    if (isset($refundData['creditmemo']['shipping_amount'])) {
                        $refundCreditMemoShippingAmount = $refundData['creditmemo']['shipping_amount'];
                    }
                    if ($shippingCharges * 1) {
                        if ($shippingCharges > $refundCreditMemoShippingAmount) {
                            $shippingCharges = $refundCreditMemoShippingAmount;
                            $refundData['creditmemo']['shipping_amount'] = 0;
                        } else {
                            $refundData['creditmemo']['shipping_amount'] =
                            $refundCreditMemoShippingAmount - $shippingCharges;
                        }
                    }
                }

                if (($value['seller_refund'] + $shippingCharges) * 1 > 0) {
                        $totalSellerRefundWithoutCommission = $value['seller_refund'] + $shippingCharges;
                        $totalSellerRefund = $value['seller_refund'] + $shippingCharges + $value['updated_commission'];
                        $totalSellerRefundWithoutCommission = round($totalSellerRefundWithoutCommission, 2);
                        $totalSellerRefund = round($totalSellerRefund, 2);
                }
            }

            $amount = $totalSellerRefund;
            $amount = $amount - $appliedCouponAmount;
            $finalTotalSellerRefundWithoutCommission = $totalSellerRefundWithoutCommission - $appliedCouponAmount;
            //amount in cents
            $amount = $this->helper->calculateAmount($amount, strtolower($order->getOrderCurrencyCode()));
            $finalTotalSellerRefundWithoutCommission = $this->helper->calculateAmount(
                $finalTotalSellerRefundWithoutCommission,
                strtolower($order->getOrderCurrencyCode())
            );
            $transactionId = $payment->getRefundTransactionId();
            $transaction = $this->orderTrans->load($payment->getRefundTransactionId());
            $onlineTxnId = $transaction->getTxnId();
            $charge = [];
            
            try {
                $refundCharge = [];
                $adminAmountForRefund = $this->helper->calculateAmount(
                    $adminAmountForRefund,
                    strtolower($order->getOrderCurrencyCode())
                );
                if ($isAdminProduct) {
                    $refundCharge = [
                        'charge' => $onlineTxnId,
                        'amount' => $adminAmountForRefund,
                        'refund_application_fee' => true,
                    ];
                } else {
                    if ($adminAmountForRefund < $amount) {
                        $amount = $adminAmountForRefund;
                    }
                    $refundCharge = [
                        'charge' => $onlineTxnId,
                        'amount' => $amount,
                        'refund_application_fee' => true,
                    ];
                }

                if (isset($refundCharge['amount']) && $refundCharge['amount']>0) {

                    if ($stripeCustomerId != 0 &&
                        $this->helper->isDirectCharge()
                    ) {
                        $chargeData = \Stripe\Charge::retrieve(
                            $onlineTxnId,
                            ['stripe_account' => $collectionData["stripe_user_id"]]
                        );
                    } else {
                        $chargeData = \Stripe\Charge::retrieve($onlineTxnId);
                    }

                    if (!$chargeData['application_fee'] || !$chargeData['destination']) {
                        unset($refundCharge['refund_application_fee']);
                    }

                    if ($stripeCustomerId != 0 &&
                        $this->helper->isDirectCharge()
                    ) {
                        $charge = \Stripe\Refund::create([
                            $refundCharge,
                        ], ['stripe_account' => $collectionData["stripe_user_id"]]);
                    } else {
                        $charge = \Stripe\Refund::create([
                            $refundCharge,
                        ]);
                    }

                    if (!$isAdminProduct &&
                        !$this->helper->isDirectCharge()
                    ) {
                        foreach ($transferIdArr as $sellerId => $transferId) {
                            $stripe = new \Stripe\StripeClient(
                                $this->secretKey
                            );
                            $finalTotalSellerRefundWithoutCommission = $this->checkForSellerReverseTransfer(
                                $finalTotalSellerRefundWithoutCommission,
                                $amount
                            );
                            $stripe->transfers->createReversal(
                                $transferId,
                                ['amount' => $finalTotalSellerRefundWithoutCommission]
                            );
                        }
                    }

                    $charge = (array) $charge;
                    foreach ($charge as $key => $value) {
                        if (strpos($key, 'values') !== false) {
                            $charge = $value;
                            $charge['amount'] = $charge['amount'] / 100;
                            $charge['metadata'] = $this->jsonHelper->jsonEncode((array) $charge['metadata']);
                        }
                    }

                    $encodeCharge = [];
                    foreach ($charge as $key => $value) {
                        $encodeCharge[$key] = $this->jsonHelper->jsonEncode($charge[$key]);
                    }

                    $payment
                    ->setTransactionId($charge['id'].'-'.\Magento\Sales\Model\Order\Payment\Transaction::TYPE_REFUND)
                    ->setParentTransactionId($transactionId)
                    ->setIsTransactionClosed(1)
                    ->setShouldCloseParentTransaction(1)
                    ->setTransactionAdditionalInfo(
                        \Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS,
                        $encodeCharge
                    );
                    if ($refundData['invoice_id']) {
                        $invoice = $this->invoice->create()->load($refundData['invoice_id']);
                        $invoice->setIsUsedForRefund(true);
                        $invoice->save();
                    }

                } else {
                    throw new LocalizedException(
                        __(
                            'There is nothing to refund.'
                        )
                    );
                }
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
                throw new LocalizedException(
                    __($e->getMessage())
                );
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
            throw new LocalizedException(
                __(
                    'payment refund error, please contact admin'
                )
            );
        }
        return $this;
    }

    /**
     * CheckForSellerReverseTransfer function
     *
     * @param float $finalTotalSellerRefundWithoutCommission
     * @param float $amount
     * @return void
     */
    private function checkForSellerReverseTransfer($finalTotalSellerRefundWithoutCommission, $amount)
    {
        if ($finalTotalSellerRefundWithoutCommission > $amount) {
            $finalTotalSellerRefundWithoutCommission = $amount;
        }
        return $finalTotalSellerRefundWithoutCommission;
    }

    /**
     * CalculateRefundTaxAmount function
     *
     * @param int $refundedQty
     * @param array $sellerProduct
     * @return int $taxAmount
     */
    public function calculateRefundTaxAmount($refundedQty, $sellerProduct)
    {
        if ($refundedQty) {
            if ((int)$sellerProduct['magequantity'] != '' ||
                (int)$sellerProduct['magequantity'] != 0
                ) {
                    $taxAmount = (
                    $sellerProduct['total_tax'] / $sellerProduct['magequantity']
                                ) * $refundedQty;
            } else {
                $taxAmount = 0;
            }
        } else {
            $taxAmount = 0;
        }
        return $taxAmount;
    }

    /**
     * LoadProductId function
     *
     * @param object $item
     * @param int $productId
     * @return $item
     */
    public function loadProductId($item, $productId)
    {
        $this->logger->critical("productId - ".$productId);
        return $item->load($productId);
    }

    /**
     * Check if seller amount available as mentioned in refund
     *
     * @param int $amount
     * @param int $orderId
     * @param array $adjustmentNegative
     * throws LocalizedException exception
     */
    private function checkSellerAmountAvailable($amount, $orderId, $adjustmentNegative)
    {
        $calculateCommission = $this->saleslistFactory->create()
                                    ->getCollection()
                                    ->addFieldToFilter('order_id', $orderId);
                       
        foreach ($calculateCommission as $commissionRate) {
            $actualSellerAmount = $commissionRate["actual_seller_amount"];
        }

        $refundAmount = $actualSellerAmount-$adjustmentNegative;

        if ($actualSellerAmount < $amount) {
            throw new LocalizedException(
                __(
                    'Refund amount by seller($'.$refundAmount
                    .') is more than seller commission($'.$actualSellerAmount.')'
                )
            );
        }
    }

    /**
     * Calculation of the refund item ordered price
     *
     * @param int $orderId
     * @param int $productId
     * @return int
     */
    protected function getItemPrice($orderId, $productId)
    {
        $order = $this->orderFactory->create()->load($orderId);
        foreach ($order->getAllItems() as $item) {
            if ($item->getProductId() == $productId) {
                return $item->getPrice();
            }
        }
        return 0;
    }

    /**
     * GetCardData get the unique fingureprint from the card object
     *
     * @param [String] $token
     * @return String
     */
    public function getCardData($token = null)
    {
        try {
            $tokenData = \Stripe\Token::retrieve($token);
            return $tokenData;
        } catch (\Exception $e) {
            throw new LocalizedException(
                __(
                    'There was an error capturing the transaction: %1',
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * GetShippingAddress get the shipping detail
     *
     * @param [Array] $stripeAddress
     * @param [\Magento\Sales\Model\Order] $order
     * @return Array
     */
    public function getShippingAddress($stripeAddress, $order)
    {
        $shippingAddress = [];
        if ($order->getIsVirtual() == 0) {
            if (empty($stripeAddress)) {
                $street = $order->getShippingAddress()->getStreet();
                if (count($street) == 2) {
                    $address['line1']  = $street[0];
                    $address['line2']  = $street[1];
                } elseif (count($street) == 1) {
                    $address['line1']  = $street[0];
                    $address['line2']  = "";
                } elseif (count($street) == 3) {
                    $address['line1']  = $street[0].' '.$street[1];
                    $address['line2']  = $street[2];
                }
                $address['city'] = $order->getShippingAddress()->getCity();
                $address['country'] = $order->getShippingAddress()->getCountryId();
                $address['postal_code'] = $order->getShippingAddress()->getPostcode();
                $address['state'] = $order->getShippingAddress()->getRegion();
                $shippingAddress['address']= $address;
                $shippingAddress['name']= $order->getShippingAddress()
                                          ->getFirstname()." ".$order->getShippingAddress()->getLastName();
                $shippingAddress['phone'] = $order->getShippingAddress()->getTelephone();
            } else {
                    $address['line1']  = $stripeAddress['shipping_address_line1'];
                    $address['line2']  = "";
                    $address['city'] = $stripeAddress['shipping_address_city'];
                    $address['country'] = $stripeAddress['shipping_address_country'];
                    $address['postal_code'] = $stripeAddress['shipping_address_zip'];
                    $address['state'] = "";
                    $shippingAddress['address']= $address;
                    $shippingAddress['name'] = $stripeAddress['billing_name'];
                    $shippingAddress['phone']= "";
            }
        }
        return $shippingAddress;
    }

    /**
     * GetBillingAddress get the billing detail
     *
     * @param [Array] $stripeAddress
     * @param [\Magento\Sales\Model\Order] $order
     * @return Array
     */
    public function getBillingAddress($stripeAddress, $order)
    {
        $billingAddress = [];
        if (empty($stripeAddress)) {
            $street = $order->getBillingAddress()->getStreet();
            if (count($street) == 2) {
                $address['line1']  = $street[0];
                $address['line2']  = $street[1];
            } elseif (count($street) == 1) {
                $address['line1']  = $street[0];
                $address['line2']  = "";
            }
            $address['city'] = $order->getBillingAddress()->getCity();
            $address['country'] = $order->getBillingAddress()->getCountryId();
            $address['postal_code'] = $order->getBillingAddress()->getPostcode();
            $address['state'] = $order->getBillingAddress()->getRegion();
            $billingAddress['address']= $address;
            $billingAddress['name']= $order->getBillingAddress()
                                     ->getFirstname()." ".$order->getBillingAddress()->getLastName();
            $billingAddress['phone'] = $order->getBillingAddress()->getTelephone();
        } else {
                $address['line1']  = $stripeAddress['billing_address_line1'];
                $address['line2']  = "";
                $address['city'] = $stripeAddress['billing_address_city'];
                $address['country'] = $stripeAddress['billing_address_country'];
                $address['postal_code'] = $stripeAddress['billing_address_zip'];
                $address['state'] = "";
                $billingAddress['address']= $address;
                $billingAddress['name'] = $stripeAddress['billing_name'];
                $billingAddress['phone']= "";
        }
        return $billingAddress;
    }

    /**
     * CheckDuplicacyOfFingurePrint check the uniqueness of fingureprint for particular customer in database
     *
     * @param [String] $fingerPrint
     * @param [String] $customerId
     * @return Boolean
     */
    public function checkDuplicacyOfFingurePrint($fingerPrint = null, $customerId = null)
    {
        try {
            $model = $this->stripeCustomer->create();
            $collection = $model->getCollection()
                            ->addFieldToFilter('fingerprint', ['eq' => $fingerPrint])
                            ->addFieldToFilter('customer_id', ['eq' => $customerId]);
            if ($collection->getSize()) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            throw new LocalizedException(
                __(
                    'There was an error capturing the transaction: %1',
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * Do not validate payment form using server methods.
     *
     * @return bool
     */
    public function validate()
    {
        return true;
    }

    /**
     * Assign corresponding data.
     *
     * @param \Magento\Framework\DataObject|mixed $data
     *
     * @return $this
     *
     * @throws LocalizedException
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        parent::assignData($data);
        return $this;
    }

    /**
     * Define if debugging is enabled.
     *
     * @return bool
     *
     * @api
     */
    public function getDebugFlag()
    {
        if ($this->getConfigData('debug') == 'sandbox') {
            return true;
        } else {
            return false;
        }
    }
}
