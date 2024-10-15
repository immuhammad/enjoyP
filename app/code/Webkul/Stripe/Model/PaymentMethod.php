<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package   Webkul_Stripe
 * @author Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\Stripe\Model;

use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Framework\Exception\LocalizedException;
use \Magento\Sales\Model\Order\Payment\Transaction;

class PaymentMethod extends AbstractMethod
{
    public const METHOD_CODE = 'stripe';

    /**
     * @var string
     */
    protected $_code = self::METHOD_CODE;

    /**
     * @var bool
     */
    protected $_isGateway = true;

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
    protected $_canCapture = true;

    /**
     * @var bool
     */
    protected $_canCapturePartial = true;

    /**
     * @var bool
     */
    protected $_canRefund = true;

    /**
     * @var bool
     */
    protected $_canVoid = true;

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
     * @var \Webkul\Stripe\Helper\Data
     */
    protected $_helper;

    /**
     * @var string
     */
    protected $_infoBlockType = \Webkul\Stripe\Block\Payment\Info::class;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_session;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Webkul\Stripe\Helper\Data $helper
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
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
        \Webkul\Stripe\Helper\Data $helper,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
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
        $this->_helper = $helper;
        $this->_session = $session;
        $this->transactionRepository = $transactionRepository;
        $this->jsonHelper = $jsonHelper;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        /*
         * set api key for payment  >> sandbox api key or live api key
         */
        if ($this->getDebugFlag()) {
            \Stripe\Stripe::setApiKey($this->_helper->getConfigValue('api_secret_key'));
        } else {
            \Stripe\Stripe::setApiKey($this->_helper->getConfigValue('api_secret_key'));
        }
        \Stripe\Stripe::setAppInfo(
            "Webkul Stripe Payment Gateway For Magento 2",
            "3.0.0",
            "https://store.webkul.com/magento2-stripe-payment-gateway.html",
            "pp_partner_FLJSvfbQDaJTyY"
        );
        \Stripe\Stripe::setApiVersion("2019-12-03");
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
        return $this;
    }

    /**
     * Refund refund the transaction.
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float                                $amount
     *
     * @return Webkul\Stripe\Model\PaymentMethod
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $smallcurrencyarray = ["bif", "clp", "djf", "gnf", "jpy", "kmf", "krw", "mga", "pyg", "rwf",
                "vnd", "vuv", "xaf", "xof", "xpf"];

        $transactionId = $payment->getLastTransId();
        $additionalInfo = $payment->getAdditionalInformation();
        $this->searchCriteriaBuilder->addFilter('txn_id', $transactionId);
        $transactionData = $this->transactionRepository
        ->getList($this->searchCriteriaBuilder->create())->getFirstItem();
        
        try {
            $paymentIntentId = $transactionData->getAdditionalInformation()['raw_details_info']['payment_intent_id'];
        } catch (\Exception $e) {
            $this->_debug([$e->getMessage()]);
            throw new LocalizedException(
                __(
                    'There was an error to get transaction information'
                )
            );
        }
        
        $order = $payment->getOrder();
        $billing = $order->getBillingAddress()->getData();
        $transactioninfo = [];
        $amount = $amount * $order->getBaseToOrderRate();
        if (in_array(strtolower($order->getOrderCurrencyCode()), $smallcurrencyarray)) {
            $amount = round($amount);
        } else {
            $amount = $amount * 100;
        }
        try {
            $charge = \Stripe\Refund::create([
                'amount' => $amount,
                'payment_intent' => $paymentIntentId,
            ]);
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

        } catch (\Stripe\Error $e) {
            $this->_debug([$e->getMessage()]);
            throw new LocalizedException(
                __(
                    'There was an error refunding the transaction , please contact admin'
                )
            );
        }

        $payment
        ->setTransactionId($charge['id'].'-'.rand(0, 99).'-'.Transaction::TYPE_REFUND)
        ->setParentTransactionId($transactionId)
        ->setIsTransactionClosed(1)
        ->setShouldCloseParentTransaction(1)
        ->setTransactionAdditionalInfo(
            \Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS,
            $encodeCharge
        );
        
        return $this;
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
     * Check whether payment method can be used
     *
     * @param \Magento\Quote\Api\Data\CartInterface|\Magento\Quote\Model\Quote|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return $this->getConfigData('api_secret_key') && $this->getConfigData('api_publish_key');
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

    /**
     * GetCardData get the unique fingureprint from the card object
     *
     * @param String $token
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
     * @param Array $stripeAddress
     * @param \Magento\Sales\Model\Order $order
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
     * @param Array $stripeAddress
     * @param \Magento\Sales\Model\Order $order
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
     * Get config payment action url
     *
     * Used to universalize payment actions when processing payment place
     *
     * @return string
     * @api
     */
    public function getConfigPaymentAction()
    {
        $sType = $this->getInfoInstance()->getAdditionalInformation('stype');
        if ($sType == 'bitcoin' && $this->getConfigData('payment_action') == 'authorize') {
            return self::ACTION_AUTHORIZE_CAPTURE;
        } else {
            return $this->getConfigData('payment_action');
        }
    }
}
