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
namespace Webkul\MpStripe\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Exception\LocalizedException;
use Webkul\MpStripe\Logger\StripeLogger;
use Webkul\MpStripe\Model\Source\PaymentAction;

class CreateIntent extends Action
{

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_jsonResultFactory;

    /**
     * $newvar variable to check if seller shipping used.
     *
     * @var string
     */
    private $newvar;

    /**
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Webkul\MpStripe\Helper\Data $helper
     * @param \Magento\Customer\Model\AddressFactory $addressFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param StripeLogger $stripeLogger
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Type\Onepage $onePage
     * @param \Magento\Shipping\Model\ConfigFactory $configFactory
     * @param \Webkul\Marketplace\Model\ProductFactory $mpProductFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Session\SessionManager $coreSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\MpStripe\Helper\Data $helper,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        PriceCurrencyInterface $priceCurrency,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        StripeLogger $stripeLogger,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Type\Onepage $onePage,
        \Magento\Shipping\Model\ConfigFactory $configFactory,
        \Webkul\Marketplace\Model\ProductFactory $mpProductFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Session\SessionManager $coreSession,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_jsonResultFactory = $jsonResultFactory;
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
        $this->mpHelper = $mpHelper;
        $this->priceCurrency = $priceCurrency;
        $this->addressFactory = $addressFactory;
        $this->customerSession = $customerSession;
        $this->configFactory = $configFactory;
        $this->orderFactory = $orderFactory;
        $this->logger = $stripeLogger;
        $this->coreSession = $coreSession;
        $this->onePage = $onePage;
        $this->mpProductFactory = $mpProductFactory;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Create stripe data for checkout page
     */
    public function execute()
    {
        $resultJson = $this->_jsonResultFactory->create();
        $resultJson->setHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store', true);
        $resultJson->setHeader('Pragma', 'no-cache', true);

        $this->helper->setUpDefaultDetails();
        $requestData = $this->getRequest()->getParams();
        $paymentAction = $this->helper->getConfigValue('stripe_payment_action');
        if (!empty($requestData['paymentIntentMethod'])) {
            if ($paymentAction == PaymentAction::STRIPE_ACTION_AUTHORIZE_CAPTURE &&
                !(int)$this->helper->isDirectCharge()
            ) {
                $finalCartData = $this->coreSession->getPaymentDetails();
                $ifSellerInCart = $this->helper->getIfSellerInCart($finalCartData);
                $paymentIntent = \Stripe\PaymentIntent::retrieve(
                    $requestData['payment_intent_id']
                );

                $response = $this->manageStripeChargeTransfer($ifSellerInCart, $finalCartData, $paymentIntent);
                $resultJson->setData($response);
                return $resultJson->setData($response);

            } else {
                $response = [];
                return $resultJson->setData($response);
            }
        } else {
            $request = $this->getRequest()->getParams();
            $canSaveCard = $request['canSaveCard'];
            $paymentMethod = $request['paymentMethod'];
            
            $order = $this->coreSession->getData('orderids');
            if ($order != "") {
                $transferOrderData = $order;
                $orderIds = explode(",", $order);
                $paymentDetails = [];
                foreach ($orderIds as $orderId) {
                    $quote = $this->orderFactory->create()->load($orderId);
                    $stripeCustomerId = '';
                    if ($canSaveCard) {
                        $stripeCustomerId = $this->helper->createStripeCustomer(
                            $quote,
                            $canSaveCard
                        );
                    }
                    $finalCart = $this->helper->getFinalCart($quote);
                    $paymentDetails[] = $this->helper->getCheckoutFinalData($finalCart, $quote);
                }
                $orderItemArray = [];
                $indexKey = 1;
                foreach ($paymentDetails as $details) {
                    foreach ($details as $pkey => $pval) {
                        if ($pkey != 0) {
                            $firstKey = $pkey;
                        } else {
                            $indexKey = $pkey;
                        }
                    }
                    $finalArray[$firstKey]=$details[$firstKey];
                    if ($indexKey != 1) {
                        if (array_key_exists($indexKey, $orderItemArray)) {
                            $orderItemArray[$indexKey]['payment_array']['amount'] =
                            $orderItemArray[$indexKey]['payment_array']['amount'] +
                            $details[$indexKey]['payment_array']['amount'];
                            $orderItemArray[$indexKey]['cart']['price'] =
                            $orderItemArray[$indexKey]['cart']['price'] + $details[$indexKey]['cart']['price'];
                            $orderItemArray[$indexKey]['cart']['shippingprice'] =
                            $orderItemArray[$indexKey]['cart']['shippingprice'] +
                            $details[$indexKey]['cart']['shippingprice'];
                            $orderItemArray[$indexKey]['payment_array']['description'] =
                            $details[$indexKey]['payment_array']['description'];
                            $orderItemArray[$indexKey]['payment_array']['order_id'] =
                            $details[$indexKey]['payment_array']['order_id'];
                        } else {
                            $orderItemArray[$indexKey]=$details[$indexKey];
                        }
                    }
                }
                if (!empty($orderItemArray)) {
                    $finalArray[0] = $orderItemArray[0];
                }
                $paymentDetails = $finalArray;
            } else {
                $orderId = $this->onePage->getCheckout()->getLastOrderId();
                $transferOrderData = $orderId;
                $quote = $this->orderFactory->create()->load($orderId);
                $stripeCustomerId = '';
                if ($canSaveCard) {
                    $stripeCustomerId = $this->helper->createStripeCustomer(
                        $quote,
                        $canSaveCard
                    );
                }
                $finalCart = $this->helper->getFinalCart($quote);
                $paymentDetails = $this->helper->getCheckoutFinalData($finalCart, $quote);
            }
            $this->coreSession->setPaymentDetails($paymentDetails);
            try {
                $transfer = $this->createStripeTransfer(
                    $paymentDetails,
                    $paymentAction,
                    $stripeCustomerId,
                    $paymentMethod,
                    $transferOrderData
                );
                $quote->setStripePaymentAction($paymentAction);
                $quote->save();
                return $resultJson->setData($transfer);
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
                throw new LocalizedException(
                    __(
                        'There was an error capturing the transaction, order has been cancelled',
                        $e->getMessage()
                    )
                );
            }
        }
    }

    /**
     * Manage stripe charge transfer data
     *
     * @param boolean $ifSellerInCart
     * @param array $finalCartData
     * @param string $paymentIntent
     * @return array
     */
    public function manageStripeChargeTransfer($ifSellerInCart, $finalCartData, $paymentIntent)
    {
        try {
            $response = [];
            if ($ifSellerInCart) {
                foreach ($finalCartData as $sellerId => $paymentDetail) {
                    if (!empty($paymentDetail['cart']['stripe_user_id'])) {
                        $response[$sellerId] = $this->createStripeTransferCharge(
                            $paymentDetail,
                            $paymentIntent['charges']['data'][0]
                        );
                    }
                }
            }
            return $response;

        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            throw new LocalizedException(
                __(
                    'There was an error capturing the transaction, order has been cancelled',
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * CreateStripeTranfer create stripe transfer.
     *
     * @param array $paymentDetailsArray seller wise details
     * @param string $paymentAction
     * @param bool  $isAdmin
     *
     * @return array or boolean
     */

    /**
     * CreateStripeTranfer create stripe transfer.
     *
     * @param array $paymentDetailsArray
     * @param string $paymentAction
     * @param string $stripeCustomerId
     * @param string $paymentMethod
     * @param string $transferOrderData
     * @return array|bool
     */
    public function createStripeTransfer(
        $paymentDetailsArray,
        $paymentAction,
        $stripeCustomerId,
        $paymentMethod,
        $transferOrderData
    ) {
        try {
            $finalArray = [];
            if ($paymentAction == PaymentAction::STRIPE_ACTION_AUTHORIZE) {
                $finalArray["capture_method"] = 'manual';
            }
            if ($stripeCustomerId) {
                $finalArray["customer"] = $stripeCustomerId;
            }
            if ($paymentMethod) {
                $finalArray["payment_method"] = $paymentMethod;
            }
            $finalArray["payment_method_types"] = ["card"];
            $finalArray['amount'] = 0;
            $stripeUserAccount = 0;
            $sellerId = 0;
            foreach ($paymentDetailsArray as $paymentDetails) {
                $finalArray['amount'] += $paymentDetails['payment_array']['amount'];
                $finalArray['currency'] = $paymentDetails['payment_array']['currency'];
                $finalArray['transfer_group'] = $transferOrderData;
                if ($paymentDetails['cart']['seller'] != 0 &&
                    $this->helper->isDirectCharge()
                ) {
                    $sellerId = $paymentDetails['cart']['seller'];
                    $finalArray['application_fee_amount'] = $paymentDetails['payment_array']['application_fee'];
                    $stripeUserAccount = $paymentDetails['cart']['stripe_user_id'];
                }
            }
            return $this->createStripeIntent($finalArray, $stripeUserAccount, $sellerId);
        } catch (\Stripe\Error $e) {
            $this->logger->critical($e->getMessage());
            return false;
        }
    }

    /**
     * CreateStripeIntent function
     *
     * @param array $finalArray
     * @param string|int $stripeUserAccount
     * @param string $sellerId
     * @return void
     */
    public function createStripeIntent($finalArray, $stripeUserAccount, $sellerId)
    {
        try {
            $this->helper->setUpDefaultDetails();
            if ($sellerId != 0 && $this->helper->isDirectCharge()) {
                $intent = \Stripe\PaymentIntent::create(
                    $finalArray,
                    [
                        'stripe_account' => $stripeUserAccount
                    ]
                );
            } else {
                $intent = \Stripe\PaymentIntent::create($finalArray);
            }
            return $intent;
        } catch (\Stripe\Error $e) {
            $this->logger->critical($e->getMessage());
            return false;
        }
    }

    /**
     * CreateStripeTransferCharge create stripe charge.
     *
     * @param array $paymentDetails
     * @param array $charge
     * @return array or boolean
     */
    public function createStripeTransferCharge($paymentDetails, $charge)
    {
        try {
            if (!empty($paymentDetails['cart']['stripe_user_id'])) {
                $finalAmount = (
                    $paymentDetails['payment_array']['amount'] - $paymentDetails['payment_array']['application_fee']
                );
                $transfer = \Stripe\Transfer::create([
                    "amount" => $finalAmount,
                    "currency" => $paymentDetails['payment_array']['currency'],
                    "destination" => $paymentDetails['cart']['stripe_user_id'],
                    "source_transaction" => $charge['id']
                  ]);
                return $transfer;
            }
        } catch (\Stripe\Error $e) {
            $this->logger->critical($e->getMessage());
            return false;
        }
    }
}
