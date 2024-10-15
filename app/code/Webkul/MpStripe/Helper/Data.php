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
namespace Webkul\MpStripe\Helper;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Customer\Model\Session;
use Webkul\MpStripe\Logger\StripeLogger;
use Webkul\MpStripe\Model\Source\IntegrationType;
use Webkul\MpStripe\Model\Source\ChargeType;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * MpStripe data helper.
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper implements ArgumentInterface
{
    public const METHOD_CODE = \Webkul\MpStripe\Model\PaymentMethod::METHOD_CODE;
    public const STRIPE_MODULE_NAME = 'Webkul Marketplace Stripe Payment Gateway For Magento 2';
    public const STRIPE_MODULE_VERSION = '5.0.2';
    public const STRIPE_MODULE_URL = 'https://store.webkul.com/magento2-marketplace-stripe-vendor-payment.html';
    public const STRIPE_PARTNER_ID = 'pp_partner_FLJSvfbQDaJTyY';
    public const STRIPE_API_VERSOIN = '2019-12-03';

    /**
     * @var Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Webkul\Marketplace\Model\ResourceModel\Saleperpartner\CollectionFactory
     */
    private $saleperpartnerCollectionFactory;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    private $marketplaceHelperData;

    /**
     * @var \Magento\Framework\Locale\Resolver
     */
    private $resolver;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    private $encryptor;

    /**
     * @var \Webkul\MpStripe\Logger\StripeLogger
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serialize;

    /**
     * @var \Webkul\MpStripe\Model\StripeCustomer
     */
    private $stripeCustomerModel;

    /**
     * @var \Webkul\MpStripe\Model\StripeSeller
     */
    private $stripeSellerModel;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    private $customerModel;

    /**
     * @var \Magento\Quote\Model\Quote\Item\Option
     */
    private $optionFactory;

    /**
     * @var \Magento\Sales\Model\Order\InvoiceFactory
     */
    private $invoiceModel;
    
    /**
     * $newvar variable to check if seller shipping used.
     *
     * @var string
     */
    private $newvar;

    /**
     * @param Session $customerSession
     * @param \Magento\Framework\App\Helper\Context $context
     * @param DateTime $date
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Webkul\Marketplace\Model\ResourceModel\Saleperpartner\CollectionFactory $saleperpartnerCollectionFactory
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelperData
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\Locale\Resolver $resolver
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param StripeLogger $stripeLogger
     * @param \Webkul\MpStripe\Model\StripeCustomerFactory $stripeCustomerModel
     * @param \Webkul\MpStripe\Model\StripeSellerFactory $stripeSellerModel
     * @param \Magento\Customer\Model\CustomerFactory $customerModel
     * @param \Magento\Quote\Model\Quote\Item\OptionFactory $optionFactory
     * @param \Magento\Sales\Model\Order\InvoiceFactory $invoiceModel
     * @param \Magento\Customer\Model\AddressFactory $addressFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Webkul\Marketplace\Model\ProductFactory $mpProductFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Session\SessionManager $coreSession
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Customer\Model\AddressFactory $customerAddress
     * @param \Magento\Shipping\Model\Config $shippingConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Webkul\Marketplace\Model\OrdersFactory $mpOrderFactory
     * @param \Magento\Framework\Serialize\Serializer\Json $serialize
     * @param \Webkul\MpStripe\Model\StripeSellerRepository $sellerKeys
     */
    public function __construct(
        Session $customerSession,
        \Magento\Framework\App\Helper\Context $context,
        DateTime $date,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\Marketplace\Model\ResourceModel\Saleperpartner\CollectionFactory $saleperpartnerCollectionFactory,
        \Webkul\Marketplace\Helper\Data $marketplaceHelperData,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\Locale\Resolver $resolver,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        StripeLogger $stripeLogger,
        \Webkul\MpStripe\Model\StripeCustomerFactory $stripeCustomerModel,
        \Webkul\MpStripe\Model\StripeSellerFactory $stripeSellerModel,
        \Magento\Customer\Model\CustomerFactory $customerModel,
        \Magento\Quote\Model\Quote\Item\OptionFactory $optionFactory,
        \Magento\Sales\Model\Order\InvoiceFactory $invoiceModel,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        PriceCurrencyInterface $priceCurrency,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Webkul\Marketplace\Model\ProductFactory $mpProductFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Session\SessionManager $coreSession,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Customer\Model\AddressFactory $customerAddress,
        \Magento\Shipping\Model\Config $shippingConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Webkul\Marketplace\Model\OrdersFactory $mpOrderFactory,
        \Magento\Framework\Serialize\Serializer\Json $serialize,
        \Webkul\MpStripe\Model\StripeSellerRepository $sellerKeys
    ) {
        $this->date = $date;
        $this->customerSession = $customerSession;
        $this->objectManager = $objectManager;
        $this->storeManager = $storeManager;
        $this->saleperpartnerCollectionFactory = $saleperpartnerCollectionFactory;
        $this->marketplaceHelperData = $marketplaceHelperData;
        $this->productMetadata = $productMetadata;
        $this->resolver =  $resolver;
        $this->encryptor = $encryptor;
        $this->logger = $stripeLogger;
        $this->serialize = $serialize;
        $this->stripeCustomerModel = $stripeCustomerModel;
        $this->stripeSellerModel = $stripeSellerModel;
        $this->customerModel = $customerModel;
        $this->optionFactory = $optionFactory;
        $this->invoiceModel = $invoiceModel;
        $this->request = $context->getRequest();
        $this->mpHelper = $mpHelper;
        $this->priceCurrency = $priceCurrency;
        $this->addressFactory = $addressFactory;
        $this->orderFactory = $orderFactory;
        $this->coreSession = $coreSession;
        $this->mpProductFactory = $mpProductFactory;
        $this->jsonHelper = $jsonHelper;
        $this->customerAddress = $customerAddress;
        $this->shippingConfig = $shippingConfig;
        $this->checkoutSession = $checkoutSession;
        $this->mpOrderFactory = $mpOrderFactory;
        $this->sellerKeys = $sellerKeys;
        parent::__construct($context);
    }

    /**
     * GetRequestData function return the request data
     *
     * @return array
     */
    public function getRequestData()
    {
        return $this->_request->getParams();
    }

    /**
     * CheckInvoiceHaveShipping function getting the invoice data to check shipping amount
     *
     * @param int $id
     * @return array
     */
    public function checkInvoiceHaveShipping($id = null)
    {
        $invoice =
        $this->invoiceModel->create()
        ->load($id);
        return $invoice->getData();
    }

    /**
     * Function to get Config Data.
     *
     * @param boolean $field
     * @return string
     */
    public function getConfigValue($field = false)
    {
        if ($field) {
            if ($field == 'api_key' || $field == 'api_publish_key' || $field == 'client_secret') {
                return $this->encryptor->decrypt($this->scopeConfig
                  ->getValue(
                      'payment/'.self::METHOD_CODE.'/'.$field,
                      \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                  ));
            } else {
                return $this->scopeConfig
                    ->getValue(
                        'payment/'.self::METHOD_CODE.'/'.$field,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    );
            }
        } else {
            return;
        }
    }

    /**
     * GetIsActive check if payment method active.
     *
     * @return bool
     */
    public function getIsActive()
    {
        return $this->getConfigValue('active');
    }

    /**
     * IsDirectCharge function
     *
     * @return boolean
     */
    public function isDirectCharge()
    {
        $directChargeFlag = false;
        if ($this->getConfigValue('charge_type') === ChargeType::DIRECT_CHARGE) {
            $directChargeFlag = true;
        }
        return $directChargeFlag;
    }

    /**
     * CreateStripeCustomer create customer on stripe.
     *
     * @param \Magento\Sales\Model\Order $order
     * @param boolean $saveCard
     * @return string stripe customer id|boolean
     */
    public function createStripeCustomer($order, $saveCard = false)
    {
        $orderItems = $order->getAllItems();
        $productId = "";
        foreach ($orderItems as $item) {
            $productId = $item->getProductId();
            break;
        }
        $sellerId = $this->mpHelper->getSellerIdByProductId($productId);
        $shippingAddress = $this->getShippingAddress($order);
        $billingAddress = $this->getBillingAddress($order);
        if ($sellerId != 0 && $this->isDirectCharge()) {
            return false;
        }
        if ($saveCard) {
            $model = $this->stripeCustomerModel->create();
            $collection = $model->getCollection()
                ->addFieldToFilter(
                    'customer_id',
                    [
                        'eq' => $this->mpHelper->getCustomerId()
                    ]
                )
                ->addFieldToFilter(
                    'payment_environment',
                    [
                        'eq' => $this->getConfigValue('debug')
                    ]
                )
                ->getFirstItem();
            if ($collection->getId() == null) {
                try {
                    /**
                     * $customer stripe customer object stores stripe customer info.
                     */
                    $this->setUpDefaultDetails();
                    $customer = \Stripe\Customer::create(
                        [
                          'description' => $order->getCustomerEmail(),
                          'email' => $order->getCustomerEmail(),
                          'shipping' => $shippingAddress
                        ]
                    );
                    $this->logger->critical('customer'.$this->jsonHelper->jsonEncode($customer));
                    if ($customer->id) {
                        if ($saveCard) {
                            /*
                             * save stripe card info in data base
                             */
                            $this->saveStripeCustomer($customer->id);
                        }
    
                        return $customer->id;
                    }
    
                    return false;
                } catch (\Stripe\Error $e) {
                    $this->logger->critical($e);
                    throw new LocalizedException(
                        __(
                            'There was an error capturing the transaction: %1',
                            $e->getMessage()
                        )
                    );
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                    throw new LocalizedException(
                        __(
                            'There was an error capturing the transaction: %1',
                            $e->getMessage()
                        )
                    );
                }
            } else {
                return $collection->getStripeCustomerId();
            }
            
        } else {
            return false;
        }
    }

    /**
     * SaveStripeCustomer save stripe customer id for future payment.
     *
     * @param string $stripeCustomerId
     * @return void
     */
    public function saveStripeCustomer($stripeCustomerId)
    {
        if ($this->customerSession->isLoggedIn()) {
            $data = [
                'customer_id' => $this->customerSession->getCustomer()->getId(),
                'is_active' => 1,
                'stripe_customer_id' => $stripeCustomerId,
                'website_id' => $this->storeManager->getStore()->getWebsiteId(),
                'store_id' => $this->storeManager->getStore()->getId(),
                'payment_environment' => $this->getConfigValue('debug')
            ];
            try {
                $model = $this->stripeCustomerModel->create();
                $model->setData($data);
                $model->save();
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }
    }

    /**
     * GetShippingAddress get the shipping detail
     *
     * @param [\Magento\Sales\Model\Order] $order
     * @return Array
     */
    public function getShippingAddress($order)
    {
        $shippingAddress = [];
        if ($order->getIsVirtual() == 0) {
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
        }
        return $shippingAddress;
    }

    /**
     * GetBillingAddress get the billing detail
     *
     * @param [\Magento\Sales\Model\Order] $order
     * @return Array
     */
    public function getBillingAddress($order)
    {
        $billingAddress = [];
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
        
        return $billingAddress;
    }

    /**
     * SaveStripeSeller connect stripe seller to admin stripe account.
     *
     * @param bolean $data
     * @return bool
     */
    public function saveStripeSeller($data = false)
    {
        if ($data) {
            $seller =
            $this->customerModel->create()
                ->load($data['user_id']);
            $email = $seller->getEmail();
            $savedSeller = $this->getStripeSeller($data['user_id']);
            $wholedata = [
                    'seller_id' => $seller->getId(),
                    'is_active' => 1,
                    'stripe_key' => $data['key'],
                    'email' => $email,
                    'website_id' => $this->storeManager->getStore()->getWebsiteId(),
                    'store_id' => $this->storeManager->getStore()->getId(),
                    'is_verified' => $data['isverified'],
                    'user_type' => $data['user_type'],
                    'access_token' => $data['access_token'],
                    'refresh_token' => $data['refresh_token'],
                    'stripe_user_id' => $data['stripe_user_id'],
                    'integration_type' => '1',
                    'payment_environment' => $this->getConfigValue('debug')
                ];
            if ($savedSeller) {
                try {
                    $model =
                    $this->stripeSellerModel->create()
                        ->load($savedSeller->getId());
                    $model->setData($wholedata);
                    $model->save();
                } catch (\Exception $e) {
                    return false;
                }
            } else {
                try {
                    $model =
                    $this->stripeSellerModel->create();
                    $model->setData($wholedata);
                    $model->save();
                } catch (\Exception $e) {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    /**
     * SavecustomStripeSeller connect account.
     *
     * @param boolean $data
     * @return bool
     */
    public function saveCustomStripeSeller($data = false)
    {
        if ($data) {
            $seller =
            $this->customerModel->create()
                ->load($data['user_id']);
            $email = $seller->getEmail();
            $savedSeller = $this->getStripeSeller($data['user_id']);
            $wholedata = [
                'seller_id' => $seller->getId(),
                'is_active' => 1,
                'email' => $email,
                'website_id' => $this->storeManager->getStore()->getWebsiteId(),
                'store_id' => $this->storeManager->getStore()->getId(),
                'user_type' => $data['type'],
                'stripe_user_id' => $data['id'],
                'integration_type' => '2',
                'payment_environment' => $this->getConfigValue('debug')
            ];
            if (isset($data['stripe_person_id'])) {
                $wholedata['stripe_person_id'] = $data['stripe_person_id'];
            }
            if ($savedSeller) {
                try {
                    $model =
                    $this->stripeSellerModel->create()
                        ->load($savedSeller->getId());
                    $model->setData($wholedata);
                    $model->save();
                    return true;
                } catch (\Exception $e) {
                    return false;
                }
            } else {
                try {
                    $model =
                    $this->stripeSellerModel->create();
                    $model->setData($wholedata);
                    $model->save();
                    return true;
                } catch (\Exception $e) {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    /**
     * GetSavedCards function to get saved cards of the customer.
     *
     * @return array
     */
    public function getSavedCards()
    {
        if ($this->customerSession->isLoggedIn()) {
            $customerId = $this->customerSession->getCustomer()->getId();
            $collection = $this->stripeCustomerModel->create()
                ->getCollection()
                ->addFieldToFilter('customer_id', ['eq' => $customerId])
                ->addFieldToFilter(
                    'payment_environment',
                    ['eq' => $this->getConfigValue('debug')]
                )->getFirstItem();
            if ($collection->getId()) {
                $this->setUpDefaultDetails();
                $paymentMethods = \Stripe\PaymentMethod::all([
                    'customer' => $collection->getStripeCustomerId(),
                    'type' => 'card',
                ]);
                return $paymentMethods;
            } else {
                return [];
            }
        } else {
            return [];
        }
    }

    /**
     * GetStripeSeller get stripe seller connect details.
     *
     * @param bool $customerId
     *
     * @return Webkul\MpStripe\Model\StripeSeller or boolean
     */
    public function getStripeSeller($customerId = false)
    {
        if ($customerId) {
            $collection =
            $this->stripeSellerModel->create()
                ->getCollection()
                ->addFieldToFilter('seller_id', ['eq' => $customerId])
                ->getFirstItem();
            if ($collection && $collection->getId()) {
                return $collection;
            } else {
                return false;
            }
        }
    }

    /**
     * GetSellerDetail get seller commission details
     *
     * @param  string $sellerId
     * @return array
     */
    public function getSellerDetail($sellerId = '')
    {
        if ($sellerId) {
            $sellerdetails = $this->saleperpartnerCollectionFactory
                ->create()
                ->addFieldToFilter('seller_id', $sellerId);
            if ($sellerdetails->getSize()) {
                foreach ($sellerdetails as $temp) {
                    if ($temp->getCommissionRate() > 0) {
                        return [
                            'id' => $temp->getSellerId(),
                            'commission' => $temp->getCommissionRate(),
                        ];
                    } else {
                        return [
                            'id' => $temp->getSellerId(),
                            'commission' => $this->marketplaceHelperData->getConfigCommissionRate(),
                        ];
                    }
                }
            } else {
                return [
                    'id' => $sellerId,
                    'commission' => $this->marketplaceHelperData->getConfigCommissionRate(),
                ];
            }
        } else {
            return ['id' => 0,'commission' => 0];
        }
    }

    /**
     * GetAssignSellerId function to get assign seller id from order item.
     *
     * @param object $item
     *
     * @return int
     */
    public function getAssignSellerId($item)
    {
        // Get Info Buy Request from quote item,
        $itemOption =
        $this->optionFactory->create()
            ->getCollection()
            ->addFieldToFilter('item_id', $item->getId())
            ->addFieldToFilter('code', 'info_buyRequest');

        foreach ($itemOption as $option) {
            $info = $option->getValue();
        }

        if (preg_match("/^2\.[0-1]\.\d/", $this->productMetadata->getVersion())) {
            $info = $this->serialize->unserialize($info);
        }
        if (preg_match("/^2\.2\.\d/", $this->productMetadata->getVersion())) {
            $info = $this->jsonHelper->jsonDecode($info, true);
        }

        //Get mpassignproduct_id from $info
        $assignId = 0;
        $sellerId = 0;
        if (isset($info['mpassignproduct_id'])) {
            $assignId = $info['mpassignproduct_id'];
            $mpassignModel = $this->objectManager
                ->create(\Webkul\MpAssignProduct\Model\Items::class)
                ->load($assignId);
            $sellerId = $mpassignModel->getSellerId();
        }
        return $sellerId;
    }

    /**
     * GetLocaleFromConfiguration get the current set locale code from configuration.
     *
     * @return String
     */
    public function getLocaleFromConfiguration()
    {
        return $this->resolver->getLocale();
    }

    /**
     * GetLocaleForStripe return the locale value exixt in stripe api other wise return "auto"
     *
     * @return String
     */
    public function getLocaleForStripe()
    {
        $configLocale = $this->getLocaleFromConfiguration();
        if ($configLocale) {
            $temp = explode('_', $configLocale);
            if (isset($temp['0'])) {
                $configLocale = $temp['0'];
            }
        }
        $stripeLocale = $this->matchCodeSupportedByStripeApi($configLocale);
        return $stripeLocale;
    }

    /**
     * MatchCodeSupportedByStripeApi matches the configuration locale to the locale exixt in strip api
     *
     * @param [String] $configLocale
     * @return String
     */
    public function matchCodeSupportedByStripeApi($configLocale)
    {
        $returnLocale = '';
        switch ($configLocale) {
            case "zh":
                $returnLocale = $configLocale;
                break;
            case "da":
                $returnLocale = $configLocale;
                break;
            case "nl":
                $returnLocale = $configLocale;
                break;
            case "en":
                $returnLocale = $configLocale;
                break;
            case "fi":
                $returnLocale = $configLocale;
                break;
            case "fr":
                $returnLocale = $configLocale;
                break;
            case "de":
                $returnLocale = $configLocale;
                break;
            case "it":
                $returnLocale = $configLocale;
                break;
            case "ja":
                $returnLocale = $configLocale;
                break;
            case "no":
                $returnLocale = $configLocale;
                break;
            case "es":
                $returnLocale = $configLocale;
                break;
            case "sv":
                $returnLocale = $configLocale;
                break;
            default:
                $returnLocale = "auto";
                break;
        }
        return $returnLocale;
    }

    /**
     * CustomerExist function
     *
     * @param string $custID
     * @return boolean
     */
    public function customerExist($custID = null)
    {
        try {
            $this->setUpDefaultDetails();
            $customer = \Stripe\Customer::retrieve($custID);
            if (isset($customer['id'])) {
                return 1;
            } else {
                return 0;
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * IsSellerConnected function
     *
     * @return boolean
     */
    public function isSellerConnected()
    {
        $flag = 0;
        $customerId = $this->customerSession->getCustomer()->getId();
        if ($this->getStripeSeller($customerId)) {
            $flag = 1;
        }
        return $flag;
    }

    /**
     * Converts amount into cents for specific currencies
     *
     * @param int $totalAmount
     * @param string $currency
     * @return int
     */
    public function calculateAmount($totalAmount, $currency)
    {
        $smallcurrencyarray = ["bif", "clp", "djf", "gnf", "jpy", "kmf", "krw", "mga", "pyg", "rwf",
                                "vnd", "vuv", "xaf", "xof", "xpf"];
        if (in_array($currency, $smallcurrencyarray)) {
            return $totalAmount * 100;
        } else {
            return $totalAmount * 100;
        }
    }

    /**
     * GetIntegration function
     *
     * @return boolean
     */
    public function getIntegration()
    {
        $globalIntegration = $this->getConfigValue('integration');
        if ($globalIntegration == IntegrationType::STRIPE_CONNECT) {
            return true;
        } elseif ($globalIntegration == IntegrationType::STRIPE_CONNECT_CUSTOM) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * CheckStripeAccount function
     *
     * @param int $stripeSellerId
     * @return boolean
     */
    public function checkStripeAccount($stripeSellerId)
    {
        $stripeSeller = $this->stripeSellerModel->create()
        ->getCollection()
        ->addFieldToFilter('stripe_user_id', ['eq' => $stripeSellerId])
        ->getFirstItem();
        $sellerIntergration = $stripeSeller->getIntegrationType();
        if ($sellerIntergration == IntegrationType::STRIPE_CONNECT_CUSTOM) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * GetStripeIntegrationInfo function
     *
     * @param int $sellerId
     * @return boolean
     */
    public function getStripeIntegrationInfo($sellerId)
    {
        $stripeSeller = $this->stripeSellerModel->create()
        ->getCollection()
        ->addFieldToFilter('seller_id', ['eq' => $sellerId])
        ->getFirstItem();
        $sellerIntergration = $stripeSeller->getIntegrationType();
        if ($sellerIntergration == IntegrationType::STRIPE_CONNECT_CUSTOM) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get Order item array data to create invoice.
     *
     * @param \Magento\Sales\Model\Order $order
     * @param array $items
     *
     * @return array
     */
    public function _getItemQtys($order, $items)
    {
        $data = [];
        $subtotal = 0;
        $baseSubtotal = 0;
        foreach ($order->getAllItems() as $item) {
            if (in_array($item->getProductId(), $items)) {
                $data[$item->getItemId()] = (int)
                    $item->getQtyOrdered() - $item->getQtyInvoiced();

                $_item = $item;

                // for bundle product
                $bundleitems = $this->mergeBundleItems($_item);

                if ($_item->getParentItem()) {
                    continue;
                }

                if ($_item->getProductType() == 'bundle') {
                    foreach ($bundleitems as $_bundleitem) {
                        if ($_bundleitem->getParentItem()) {
                            $data[$_bundleitem->getItemId()] = (int)
                                $_bundleitem->getQtyOrdered() - $item->getQtyInvoiced();
                        }
                    }
                }
                $subtotal += $_item->getRowTotal();
                $baseSubtotal += $_item->getBaseRowTotal();
            } else {
                if (!$item->getParentItemId()) {
                    $data[$item->getItemId()] = 0;
                }
            }
        }

        return [
            'data' => $data,
            'subtotal' => $subtotal,
            'baseSubtotal' => $baseSubtotal,
        ];
    }

    /**
     * MergeBundleItems function
     *
     * @param Object $_item
     * @return array
     */
    public function mergeBundleItems($_item)
    {
        return array_merge(
            [$_item],
            $_item->getChildrenItems()
        );
    }

    /**
     * GetIfSellerInCart function to check if seller present in the cart.
     *
     * @param array $paymentDetails
     *
     * @return bool true | false
     */
    public function getIfSellerInCart($paymentDetails = [])
    {
        if (!empty($paymentDetails)) {
            foreach ($paymentDetails as $pd) {
                $cart = $pd['cart'];
                if (isset($cart['seller']) && $cart['seller']) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * GetSellerCart function
     *
     * @param boolean $order
     * @return array
     */
    public function getSellerCart($order = false)
    {
        $finalCart = [];
        if ($order) {
            $cartItems = $order->getAllVisibleItems();
            $cart = [];
            $i = 0;
            $orderShippingTaxAmount = 0;
            $orderShippingAmount = 0;
            $customerAddressId = 0;
            $sellerId = 0;
            $commissionDetail = [];
            
            $shippingData = $this->getShippingData($order);
            // $newvar = $shippingData['newvar'];
            $shippingTaxAmount = $shippingData['shippingTaxAmount'];
            $shippingAmount = $shippingData['shippingAmount'];
            $shipinf = $shippingData['shipinf'];
            $customName = $shippingData['customName'];
            $customerAddress = $shippingData['customerAddress'];

            /**
            * $marketplaceHelper get marketplace helper.
            *
            * @var Webkul\Marketplace\Helper\Data
            */
            $marketplaceHelper = $this->mpHelper;
            /*
             * dispatch event for advance commission module
             */
            $this->_eventManager->dispatch(
                'mp_advance_commission_rule',
                ['order' => $order]
            );

            /**
            * $advanceCommissionRule advance commssion rule from session if set.
            */
            $advanceCommissionRule = $this->customerSession->getData(
                'advancecommissionrule'
            );

            /*
             * iterate through cart items to create seller wise payment details
             */
            foreach ($cartItems as $item) {
                $invoiceprice = 0;
                $itemId = $item->getProductId();
                $sellerId = 0;
                /**
                 * $assignSellerId variable to store assign seller id.
                 *
                 * @var int
                 */
                $assignSellerId = $this->getAssignSellerId($item);

                $seller = $this->mpProductFactory->create()->getCollection();

                /*
                 * check if assign seller id exists then add filter for seller id else product id
                 */
                if ($assignSellerId) {
                    $sellerId = $assignSellerId;
                } else {
                    $seller->addFieldToFilter('mageproduct_id', $itemId);
                    if ($seller->getSize() > 0) {
                        foreach ($seller as $obj) {
                            $sellerId = $obj->getSellerId();
                        }
                    }
                }

                $tempcoms = 0;
                /*
                 * get advance commission rule
                 */
                if (!$marketplaceHelper->getUseCommissionRule()) {
                    /*
                     * dispatch event for the calculation of advance commission
                     */
                    $this->_eventManager->dispatch(
                        'mp_advance_commission',
                        ['id' => $itemId]
                    );
                    /**
                     * [$advancecommission get Advance commission from the session.
                     *
                     * @var decimal
                     */
                    $advancecommission = $this->customerSession->getData(
                        'commission'
                    );
                    /*
                     * check if advance commission is set then calculate commission
                     */
                    if ($advancecommission != '') {
                        $percent = $advancecommission;
                        $commType = $marketplaceHelper->getCommissionType();
                        if ($commType == 'fixed') {
                            $tempcoms = $percent;
                        } else {
                            $tempcoms = (($item->getRowTotal() - $item->getDiscountAmount()) * $advancecommission)
                            / 100;
                        }
                        if ($tempcoms > $item->getRowTotal()) {
                            $tempcoms = (($item->getRowTotal() - $item->getDiscountAmount())
                             * $marketplaceHelper->getConfigCommissionRate()) / 100;
                        }

                        $commissionDetail['id'] = $sellerId;
                    }
                } else {
                    /*
                     * check if advance commission is not set then set calculate advance commission
                     */
                    if (!empty($advanceCommissionRule)) {
                        if ($advanceCommissionRule[$item->getId()]['type'] == 'fixed') {
                            $tempcoms = $advanceCommissionRule[$item->getId()]['amount'];
                        } else {
                            $tempcoms =
                                (($item->getRowTotal() - $item->getDiscountAmount())
                                 * $advanceCommissionRule[$item->getId()]['amount']) / 100;
                        }

                        $commissionDetail['id'] = $sellerId;
                    }
                }

                /*
                 * if there is no advance commission then calculate normal commission
                 */
                if (!$tempcoms) {
                    $commissionDetail = $this->getSellerDetail($sellerId);

                    if ($commissionDetail['id'] !== 0
                        && $commissionDetail['commission'] !== 0
                    ) {
                        $tempcoms = $this->priceCurrency->round(
                            (($item->getRowTotal() - $item->getDiscountAmount())
                             * $commissionDetail['commission']) / 100,
                            2
                        );
                    }
                }

                /**
                 * $price price after commission.
                 *
                 * @var decimal
                 */
                $price = $this->priceCurrency->round($item->getRowTotal()- $item->getDiscountAmount() - $tempcoms);
                /**
                 * $invoiceprice row total of product.
                 *
                 * @var decimal
                 */
                $invoiceprice = $item->getRowTotal();
                /**
                 * include tax amount if tax management done by seller
                 */
                if ($sellerId && $marketplaceHelper->getConfigTaxManage()) {
                    $invoiceprice = $this->priceCurrency->round($invoiceprice);
                } elseif ($sellerId==0) {
                    /**
                     * else tax managed by admin
                     */
                    $invoiceprice = $this->priceCurrency->round($invoiceprice + $orderShippingTaxAmount);
                }

                $shippingprice = 0;
                if ($this->newvar == 'webkul') {
                    $custid = 0;

                    $custid = $sellerId;

                    foreach ($shipinf as $k => $key) {
                        if ($key['seller'] == $custid) {
                            $price = $price + $key['amount'];
                            $shippingprice = $key['amount'];
                            $shipinf[$k]['amount'] = 0;
                        }
                    }
                }
                
                /*
                 * create seller wise array for payment
                 */
                if ($orderShippingTaxAmount !== 0
                    && ($commissionDetail['id'] == 0)
                ) {
                    $this->adminShipTaxAmt = 1;
                    $cart[$i]['data'] = [
                        'seller_id' => $commissionDetail['id'],
                        'commission' => $tempcoms,
                        'product_id' => $item->getProductId(),
                        'price' => $this->calculateAmount(
                            $price,
                            strtolower($order->getOrderCurrencyCode())
                        ),
                        'invoice_price' => $invoiceprice,
                        'shipping_price' => $shippingprice,
                        'tax_amount' => $this->priceCurrency->round($item->getTaxAmount() + $orderShippingTaxAmount),
                        'discount_amount' => $item->getDiscountAmount()
                    ];
                } else {
                    $cart[$i]['data'] = [
                        'seller_id' => $commissionDetail['id'],
                        'commission' => $tempcoms,
                        'product_id' => $item->getProductId(),
                        'price' => $this->calculateAmount(
                            $price,
                            strtolower($order->getOrderCurrencyCode())
                        ),
                        'invoice_price' => $invoiceprice,
                        'shipping_price' => $shippingprice,
                        'tax_amount' => $item->getTaxAmount(),
                        'discount_amount' => $item->getDiscountAmount()
                    ];
                }
                ++$i;
            }
            return $cart;
        } else {
            throw new LocalizedException(
                __(
                    'There was an error capturing the transaction: Please contact admin'
                )
            );
        }
    }

    /**
     * GetShippingData function
     *
     * @param Object $order
     * @return array
     */
    public function getShippingData($order)
    {
        $newvar = '';
        $allmethods = [];
        $shipinf = [];

        $customerAddressData = $this->getCustomerAddressData($order);

        $shipmeth = $customerAddressData['shipmeth'];
        $customerAddressId = $customerAddressData['customerAddressId'];
        $shippingTaxAmount = $customerAddressData['shippingTaxAmount'];
        $shippingAmount = $customerAddressData['shippingAmount'];
        
        //Guest User
        if (!$customerAddressId || $customerAddressId == null) {
            $customName = $order->getBillingAddress()->getFirstname()
            . ' '
            . $order->getBillingAddress()->getLastname();

            $customerAddress = $order->getBillingAddress();
        } else {
            $customerAddress = $this->customerAddress->create()->load($customerAddressId);
            $customName = $customerAddress['firstname'].' '.$customerAddress['lastname'];
        }
        $methods = $this->shippingConfig->getActiveCarriers();

        foreach ($methods as $_code => $_method) {
            array_push($allmethods, $_code);
        }

        if ($shipmeth == 'mp_multi_shipping_mp_multi_shipping') {
            $this->newvar = 'webkul';
            $shippinginfo = $this->checkoutSession->getData(
                'selected_shipping'
            );
            foreach ($shippinginfo as $key => $val) {
                $taxAmount = $this->calculateTaxByPercent($val['amount'], $order);
                $shipinf[] = [
                    'seller' => $key,
                    'amount' => $val['amount'],
                    'tax_amount'=>$taxAmount
                ];
            }
        } else {
            $shipmethod = explode('_', $shipmeth, 2);
            $shippinginfo = $this->checkoutSession->getShippingInfo();
            if (empty($shippinginfo) || $shippinginfo=="" || $shippinginfo==null) {
                $shippinginfo = $this->coreSession->getShippingInfo();
            }
            if (in_array($shipmethod[0], $allmethods)
                && !empty($shippinginfo[$shipmethod[0]])
            ) {
                foreach ($shippinginfo[$shipmethod[0]] as $key) {
                    $this->newvar = 'webkul';
                    foreach ($key['submethod'] as $k => $v) {
                        if ($k == $shipmethod[1]) {
                            $taxAmount = $this->calculateTaxByPercent($v['cost'], $order);
                            $shipinf[] = [
                                'seller' => $key['seller_id'],
                                'amount' => $v['cost'],
                                'tax_amount'=>$taxAmount
                            ];
                        }
                    }
                }
            } elseif (in_array($shipmethod[0], $allmethods)) {
                $sellerOrders = $this->mpOrderFactory->create()->getCollection()
                    ->addFieldToFilter('order_id', $order->getId());
                if (count($sellerOrders)) {
                    foreach ($sellerOrders as $sellerOrder) {
                        if ($sellerOrder->getShippingCharges() > 0) {
                            $this->newvar = 'webkul';
                            $taxAmount = $this->calculateTaxByPercent($sellerOrder->getShippingCharges(), $order);
                            $shipinf[] = [
                                'seller' => $sellerOrder->getSellerId(),
                                'amount' => $sellerOrder->getShippingCharges(),
                                'tax_amount'=>$taxAmount
                            ];
                        }
                    }
                }
            }
        }
        return [
            'shippingTaxAmount' => $shippingTaxAmount,
            'shippingAmount' => $shippingAmount,
            'shipinf' => $shipinf,
            'customName' => $customName,
            'customerAddress' => $customerAddress
        ];
    }

    /**
     * GetCustomerAddressData function
     *
     * @param Object $order
     * @return array
     */
    public function getCustomerAddressData($order)
    {
        $shippingTaxAmount = 0;
        $shippingAmount = 0;
        $customerAddressId = 0;
        if (!empty($order->getShippingAddress())) {
            $shipmeth = $order->getShippingMethod();

            $shippingData = $this->calculateShippingTax(
                $order->getShippingAddress()
            );
            $shippingTaxAmount = $shippingData['shippingTaxAmount'];
            $shippingAmount = $shippingData['shippingAmount'];

            $customerAddressId = $order->getShippingAddress()
            ->getCustomerAddressId();
        } else {
            $shipmeth = '';
            $customerAddressId = $order->getBillingAddress()
            ->getCustomerAddressId();
        }
        if ($customerAddressId == null) {
            $customerAddressId = $order->getBillingAddress()
            ->getCustomerAddressId();
        }
        return [
            'shipmeth' => $shipmeth,
            'customerAddressId' => $customerAddressId,
            'shippingTaxAmount' => $shippingTaxAmount,
            'shippingAmount' => $shippingAmount
        ];
    }

    /**
     * CalculateShippingTax function
     *
     * @param Object $shippingAddress
     * @return array
     */
    public function calculateShippingTax($shippingAddress)
    {
        $shippingTaxAmount = $shippingAddress->getData(
            'shipping_tax_amount'
        );

        $shippingAmount = $shippingAddress->getData(
            'shipping_amount'
        );

        $shippingAmountInclTax = $shippingAddress->getData(
            'shipping_incl_tax'
        );

        if ($shippingAmount < $shippingAmountInclTax && $shippingTaxAmount!==0) {
            $shippingTaxAmount = 0;
            $shippingAmount = $shippingAmountInclTax;
        }

        return [
            'shippingTaxAmount' => $shippingTaxAmount,
            'shippingAmount' => $shippingAmount
        ];
    }

    /**
     * CalculateTaxByPercent function
     *
     * @param int $amount
     * @param Object $order
     * @return int
     */
    public function calculateTaxByPercent($amount, $order)
    {
        $percent = $this->getTaxPercent($order);
        if ($percent !== 0 && $amount !== 0) {
            $taxAmount = ( $amount * $percent ) / 100 ;
            return round($taxAmount, 2);
        } else {
            return 0;
        }
    }

    /**
     * GetTaxPercent function
     *
     * @param Object $order
     * @return int
     */
    public function getTaxPercent($order)
    {
        $percent = 0;
        if ($this->getShippingTaxClass()) {
            $appliedTaxes = $order->getShippingAddress()->getAppliedTaxes();
            if (!empty($appliedTaxes)) {
                foreach ($appliedTaxes as $taxRate => $tax) {
                    if ($tax['item_type']=='shipping') {
                        return $tax['rates'][0]['percent'];
                    } else {
                        $percent = $tax['rates'][0]['percent'];
                    }
                }
            }
        }
        return $percent;
    }

    /**
     * GetShippingTaxClass function
     *
     * @return string
     */
    public function getShippingTaxClass()
    {
        return $this->scopeConfig->getValue(
            'tax/classes/shipping_tax_class',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * CreateSellerWiseShipping function
     *
     * @param array $shippinginfo
     * @param array $shipinf
     * @return array
     */
    public function createSellerWiseShipping($shippinginfo, $shipinf)
    {
        foreach ($shippinginfo as $key) {
            $this->newvar = 'webkul';
            foreach ($key['submethod'] as $k => $v) {
                if ($k == $shipmethod[1]) {
                    $shipinf[] = [
                            'seller' => $key['seller_id'],
                            'amount' => $v['cost'],
                        ];
                }
            }
        }
        return $shipinf;
    }

    /**
     * GetFinalCart function
     *
     * @param boolean $order
     * @return array
     */
    public function getFinalCart($order = false)
    {
        if ($order) {
            $shippingDiscount = $order->getBaseShippingDiscountAmount();
            $cart = $this->getSellerCart($order);
            usort($cart, function ($a, $b) {
                return $b['data']['seller_id'] - $a['data']['seller_id'];
            });
            $commission = 0;
            /**
             * $orderShippingTaxAmount get shipping tax amount if any.
             *
             * @var decimal
             */
            $orderShippingTaxAmount = 0;
            if ($order->getShippingAddress() != null) {
                $orderShippingTaxAmount = $order->getShippingAddress()->getData('shipping_tax_amount');
            }
            $finalcart = [];
            $this->counterVal = 0;
            $adminTaxAmount = 0;

            foreach ($cart as $item) {
                $stripeSecretKey = '';
                $accessToken = '';
                $publishKey = '';
                $stripeUserId = '';
                $temp = $item['data'];
                $commission += $temp['commission'];

                if ($temp['seller_id'] != 0) {
                    $mpStripe = $this->getStripeSeller($temp['seller_id']);
                    $mpstripeAccessToken = 0;
                    $mpstripePublishKey = 0;
                    $userId = 0;

                    if ($mpStripe) {
                        $mpstripeAccessToken = $mpStripe->getAccessToken();
                        $mpstripePublishKey = $mpStripe->getStripeKey();
                        $userId = $mpStripe->getStripeUserId();
                    }

                    if ($mpstripeAccessToken && $mpstripePublishKey) {
                        $accessToken = $mpstripeAccessToken;
                        $publishKey = $mpstripePublishKey;
                        $stripeUserId = $userId;
                    } elseif ($mpStripe &&
                        !empty($mpStripe->getIntegrationType()) &&
                        ($mpStripe->getIntegrationType() == IntegrationType::STRIPE_CONNECT_CUSTOM)
                    ) {
                        $stripeUserId = $userId;
                    } else {
                        $stripeSecretKey = $this->getConfigValue('api_key');
                    }
                } else {
                    $stripeSecretKey = $this->getConfigValue('api_key');
                }
                $adminTaxAmount += $temp['tax_amount'];

                $finalcart = $this->calculateFinalCart(
                    $finalcart,
                    $temp,
                    $stripeSecretKey,
                    $accessToken,
                    $publishKey,
                    $stripeUserId
                );
            }
            $status = 0;
            $index = 0;
            $counter = 0;
            foreach ($finalcart as $cart) {
                if (!isset($cart['seller']) || $cart['seller'] == 0) {
                    $status = 1;
                    $index = $counter;
                }
                ++$counter;
            }
            $adminShippingTax = 0;
            $quoteshipPrice = 0;
            if ($this->newvar != 'webkul') {
                $quoteshipPrice = $order->getShippingAmount();
            }
            /**
             * if admin product
             */
            $finalcart = $this->calculateAdminProduct(
                $finalcart,
                $counter,
                $adminTaxAmount,
                $commission,
                $quoteshipPrice,
                $shippingDiscount,
                $orderShippingTaxAmount,
                $order,
                $status,
                $index
            );
        }
        return $finalcart;
    }

    /**
     * Calculates final cart
     *
     * @param array $finalcart
     * @param array $temp
     * @param string $stripeSecretKey
     * @param string $accessToken
     * @param string $publishKey
     * @param string $stripeUserId
     * @return array $finalcart
     */
    private function calculateFinalCart(
        $finalcart,
        $temp,
        $stripeSecretKey,
        $accessToken,
        $publishKey,
        $stripeUserId
    ) {
    
        if ($this->counterVal == 0) {
            $i=$this->counterVal;
            $finalcart[$i]['shippingprice'] = $temp['shipping_price'];
            $finalcart[$i]['invoiceprice'] = $temp['invoice_price'];
            $finalcart[$i]['commission'] = $temp['commission'];
            $finalcart[$i]['price'] = $temp['price'];
            $finalcart[$i]['products'] = $temp['product_id'];
            if ($temp['seller_id']==0 && !$this->mpHelper->getConfigTaxManage()) {
                $finalcart[$i]['taxamount'] = $temp['tax_amount'];
            } elseif ($this->mpHelper->getConfigTaxManage()) {
                $finalcart[$i]['taxamount'] = $temp['tax_amount'];
            } else {
                $finalcart[$i]['taxamount'] = 0;
            }
            $finalcart[$i]['discount'] = $temp['discount_amount'];
            $finalcart[$i]['seller'] = $temp['seller_id'];
            $finalcart[$i]['stripe_secret_key'] = $stripeSecretKey;
            $finalcart[$i]['access_token'] = $accessToken;
            $finalcart[$i]['publish_key'] = $publishKey;
            $finalcart[$i]['stripe_user_id'] = $stripeUserId;
            ++$this->counterVal;
        } else {
            $i=$this->counterVal;
            if ($temp['seller_id'] == $finalcart[$i - 1]['seller']) {
                $finalcart[$i - 1]['price'] =
                $this->priceCurrency->round(
                    $finalcart[$i - 1]['price'] + $temp['price']
                );

                $finalcart[$i - 1]['invoiceprice'] =
                $finalcart[$i - 1]['invoiceprice'] +
                $temp['invoice_price'];
                $finalcart[$i - 1]['commission'] = $finalcart[$i - 1]['commission'] + $temp['commission'];
                $finalcart[$i - 1]['products'] =
                $finalcart[$i - 1]['products'].','.$temp['product_id'];
                $finalcart[$i - 1]['discount'] = $finalcart[$i - 1]['discount'] + $temp['discount_amount'];
                if ($temp['seller_id']==0 && !$this->mpHelper->getConfigTaxManage()) {
                    $finalcart[$i - 1]['taxamount'] =
                    $finalcart[$i - 1]['taxamount'] +
                    $temp['tax_amount'];
                } elseif ($this->mpHelper->getConfigTaxManage()) {
                    $finalcart[$i - 1]['taxamount'] =
                    $finalcart[$i - 1]['taxamount'] +
                    $temp['tax_amount'];
                } else {
                    $finalcart[$i - 1]['taxamount'] = 0;
                }
            } else {
                $finalcart[$i]['shippingprice'] = $temp['shipping_price'];
                $finalcart[$i]['invoiceprice'] = $temp['invoice_price'];
                $finalcart[$i]['commission'] = $temp['commission'];
                $finalcart[$i]['price'] = $temp['price'];
                $finalcart[$i]['products'] = $temp['product_id'];
                $finalcart[$i]['discount'] = $temp['discount_amount'];
                if ($temp['seller_id']==0 && !$this->mpHelper->getConfigTaxManage()) {
                    $finalcart[$i]['taxamount'] = $temp['tax_amount'];
                } elseif ($this->mpHelper->getConfigTaxManage()) {
                    $finalcart[$i]['taxamount'] = $temp['tax_amount'];
                } else {
                    $finalcart[$i]['taxamount'] = 0;
                }
                $finalcart[$i]['seller'] = $temp['seller_id'];
                $finalcart[$i]['stripe_secret_key'] = $stripeSecretKey;
                $finalcart[$i]['access_token'] = $accessToken;
                $finalcart[$i]['publish_key'] = $publishKey;
                $finalcart[$i]['stripe_user_id'] = $stripeUserId;
                ++$this->counterVal;
            }
        }
        return $finalcart;
    }

    /**
     * Calculate for admin product
     *
     * @param array $finalcart
     * @param int $counter
     * @param float $adminTaxAmount
     * @param float $commission
     * @param float $quoteshipPrice
     * @param float $shippingDiscount
     * @param float $orderShippingTaxAmount
     * @param array $order
     * @param int $status
     * @param int $index
     * @return array $finalcart
     */
    private function calculateAdminProduct(
        $finalcart,
        $counter,
        $adminTaxAmount,
        $commission,
        $quoteshipPrice,
        $shippingDiscount,
        $orderShippingTaxAmount,
        $order,
        $status,
        $index
    ) {
        if ($status == 1) {
            $finalcart[$index]['price'] =
            $finalcart[$index]['price'] +
            $finalcart[$index]['taxamount'] +
            $quoteshipPrice;
            if (!$this->mpHelper->getConfigTaxManage()) {
                $finalcart[$index]['taxamount'] = $adminTaxAmount;
            }

            $finalcart[$index]['shippingprice'] =
            $finalcart[$index]['shippingprice'] +
            $quoteshipPrice;
        } else {
            /**
             * if commission or admin shipping both are set
             */
            if ($commission != 0 || $quoteshipPrice != 0) {
                /**
                 * if admin shipping is used
                 */
                $finalcart = $this->calculatePrice(
                    $finalcart,
                    $counter,
                    $quoteshipPrice,
                    $shippingDiscount,
                    $orderShippingTaxAmount
                );

                $stripeSecretKey = $this->getConfigValue('api_key');

                $finalcart[$counter]['seller'] = 0;
                $finalcart[$counter]['stripe_secret_key'] = $stripeSecretKey;
                
                $adminTaxAmount = $this->calculateAdminTaxAmount($adminTaxAmount, $order);

                $finalcart[$counter]['taxamount'] = $adminTaxAmount;
                $finalcart[$counter]['access_token'] = null;
                $finalcart[$counter]['publish_key'] = null;
                $finalcart[$counter]['stripe_user_id'] = null;
                $finalcart[$counter]['products'] = null;
                $finalcart[$counter]['discount'] = 0;
            }
        }
        $this->logger->critical("final cart value - ".$this->jsonHelper->jsonEncode($finalcart));
        return $finalcart;
    }

    /**
     * Calculate adminTaxAmount
     *
     * @param float $adminTaxAmount
     * @param array $order
     * @return float adminTaxAmount
     */
    private function calculateAdminTaxAmount($adminTaxAmount, $order)
    {
        if ($this->mpHelper->getConfigTaxManage()) {
            $adminTaxAmount = 0;
        }
        return $adminTaxAmount;
    }

    /**
     * GetCheckoutFinalData function
     *
     * @param array $finalCart
     * @param Object $quote
     * @return array
     */
    public function getCheckoutFinalData($finalCart, $quote)
    {
        $paymentDetailsArray = [];
        if (count($finalCart) > 0) {
            foreach ($finalCart as $cart) {
                /**
                 * check if no shipping and product is set
                 */
                if ($cart['products'] == null && $cart['shippingprice'] == 0) {
                    if (!$this->mpHelper->getConfigTaxManage() && $cart["taxamount"] == 0) {
                        continue;
                    } elseif ($this->mpHelper->getConfigTaxManage()) {
                        continue;
                    }
                }
                
                /**
                 * check if commission is not set
                 */
                if (!isset($cart['commission'])) {
                    $cart['commission'] = 0;
                }

                /**
                 * check if tax is miscalculated
                 */
                if (isset($cart['taxamount']) && $cart['taxamount'] < 0) {
                    $cart['taxamount'] = 0;
                }

                /**
                 * convert to cent by multiplying to 100
                 */
                $applicationFee = $this->calculateAmount(
                    $cart['commission'],
                    strtolower($quote->getOrderCurrencyCode())
                );
                if ($cart['seller'] == 0) {
                    $applicationFee = 0;
                }
                $sellerStripeSecretKey = $cart['stripe_secret_key'];
                $sellerAccessToken = $cart['access_token'];
                $sellerStripeKey = $cart['publish_key'];
                $stripeUserId = $cart['stripe_user_id'];
                $shippingAmount = 0;
                $sellerId = $cart['seller'];
                $invoicePrice = $cart['invoiceprice'];
                $shippingAmount = $cart['shippingprice'];
                $taxAmount = $cart['taxamount'];
                $discount = $cart['discount'];

                /*
                 * if seller is not connected to admin stripe app
                 */
                if ($cart['seller'] && !$stripeUserId) {
                    $applicationFee = 0;
                }
                $paymentDetailsArray[$sellerId]['payment_array'] = [
                        'amount' => $this->calculateAmount(
                            $quote->getGrandTotal(),
                            strtolower($quote->getOrderCurrencyCode())
                        ),
                        'currency' => strtolower($quote->getOrderCurrencyCode()),
                        'application_fee' => $applicationFee,
                        'description' => sprintf('#%s', $quote->getId()),
                        'destination' => $stripeUserId,
                        'order_id' => $quote->getId(),
                    ];
                $paymentDetailsArray[$sellerId]['cart'] = $cart;
            }
        }

        $this->logger->critical("paymentDetailsArray value - ".$this->jsonHelper->jsonEncode($paymentDetailsArray));
        return $paymentDetailsArray;
    }
    
    /**
     * Calculates price
     *
     * @param array $finalcart
     * @param int $counter
     * @param float $quoteshipPrice
     * @param float $shippingDiscount
     * @param float $orderShippingTaxAmount
     * @return array $finalcart
     */
    private function calculatePrice(
        $finalcart,
        $counter,
        $quoteshipPrice,
        $shippingDiscount,
        $orderShippingTaxAmount
    ) {
    
        if ($this->newvar == '') {
            $finalcart[$counter]['price'] =
            $this->priceCurrency->round(
                $quoteshipPrice + $orderShippingTaxAmount
            );
            $finalcart[$counter]['shippingprice'] = $quoteshipPrice - $shippingDiscount;
            $finalcart[$counter]['invoiceprice'] = 0;
        } elseif ($orderShippingTaxAmount !== 0) {
            $finalcart[$counter]['price'] = $orderShippingTaxAmount;
            $finalcart[$counter]['shippingprice'] = 0;
            $finalcart[$counter]['invoiceprice'] = 0;
        } else {
            $finalcart[$counter]['price'] = 0;
            $finalcart[$counter]['invoiceprice'] = 0;
            $finalcart[$counter]['shippingprice'] = 0;
        }
        return $finalcart;
    }

    /**
     * SetUpDefaultDetails function
     *
     * @return void
     */
    public function setUpDefaultDetails()
    {
        $stripeKey = $this->getConfigValue('api_key');
        \Stripe\Stripe::setApiKey($stripeKey);
        $this->logger->critical('stripeKey '.$stripeKey);

        \Stripe\Stripe::setAppInfo(
            self::STRIPE_MODULE_NAME,
            self::STRIPE_MODULE_VERSION,
            self::STRIPE_MODULE_URL,
            self::STRIPE_PARTNER_ID
        );
        \Stripe\Stripe::setApiVersion(self::STRIPE_API_VERSOIN);
    }
}
