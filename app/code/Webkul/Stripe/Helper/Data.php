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
namespace Webkul\Stripe\Helper;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Customer\Model\Session;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;

/**
 * Stripe data helper.
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const METHOD_CODE = \Webkul\Stripe\Model\PaymentMethod::METHOD_CODE;

    public const MAX_SAVED_CARDS = 30;

    public const CARD_IS_ACTIVE = 1;

    public const CARD_NOT_ACTIVE = 0;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Locale\Resolver
     */
    protected $_resolver;

    /**
     * @var \Magento\Framework\View\Element\Template
     */
    protected $template;

    /**
     * @var \Webkul\Stripe\Model\PaymentMethod
     */
    protected $paymentMethod;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $_curl;

    /**
     * __construct
     *
     * @param Session $customerSession
     * @param \Magento\Framework\App\Helper\Context $context
     * @param FormKeyValidator $formKeyValidator
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\Element\Template $template
     * @param \Magento\Framework\Locale\Resolver $resolver
     * @param \Webkul\Stripe\Model\PaymentMethod $paymentMethod
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     */
    public function __construct(
        Session $customerSession,
        \Magento\Framework\App\Helper\Context $context,
        FormKeyValidator $formKeyValidator,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Element\Template $template,
        \Magento\Framework\Locale\Resolver $resolver,
        \Webkul\Stripe\Model\PaymentMethod $paymentMethod,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\HTTP\Client\Curl $curl
    ) {
        $this->_customerSession = $customerSession;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_storeManager = $storeManager;
        $this->template =  $template;
        $this->_resolver =  $resolver;
        $this->_paymentMethod = $paymentMethod;
        $this->_encryptor = $encryptor;
        $this->_curl = $curl;
        parent::__construct($context);
    }

    /**
     * Function to get Config Data.
     *
     * @param bool $field
     * @return string
     */
    public function getConfigValue($field = false)
    {
        if ($field) {
            if ($field == 'api_secret_key' || $field == 'api_publish_key') {
                return $this->_encryptor->decrypt($this->scopeConfig
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
     * SaveStripeCustomer save stripe customer id for future payment.
     *
     * @param int $stripeCustomerId
     * @param array $paymentData
     * @param int $isDuplicateCard
     * @return string stripe customer id
     */
    public function saveStripeCustomer($stripeCustomerId, $paymentData = [], $isDuplicateCard = 0)
    {
        if ($this->_customerSession->isLoggedIn()) {
            $paymentLabel = $paymentData['cardNumber'];
            if ($paymentData['type'] == 'alipay_account') {
                $paymentLabel = __("Alipay");
            } elseif ($paymentData['type'] == 'bitcoin') {
                $paymentLabel = "Bitcoin";
            } else {
                $paymentLabel = "****".$paymentLabel;
            }
            $savedCards = $this->getSavedCards($paymentData['type']);
            $cardCount = 0;
            if ($savedCards) {
                $cardCount = $savedCards->getSize();
            } else {
                $cardCount = 0;
            }
            if ($cardCount < self::MAX_SAVED_CARDS) {
                $data = [
                    'customer_id' => $this->_customerSession->getCustomer()->getId(),
                    'is_active' => self::CARD_IS_ACTIVE,
                    'stripe_customer_id' => $stripeCustomerId,
                    'label' => $paymentLabel,
                    'type' => $paymentData['type'],
                    'brand'=> $paymentData['brand'],
                    'website_id' => $this->_storeManager->getStore()->getWebsiteId(),
                    'store_id' => $this->_storeManager->getStore()->getId(),
                    'fingerprint' => $paymentData['fingerprint'],
                    'expiry_month' => $paymentData['expiry_month'],
                    'expiry_year' => $paymentData['expiry_year'],
                ];
                try {
                    $model = $this->stripeCustomer->create();
                    if ($isDuplicateCard) {
                        $model->load($paymentData['fingerprint'], 'fingerprint');
                        $model->setExpiryYear($paymentData['expiry_year']);
                        $model->setExpiryMonth($paymentData['expiry_month']);
                        $model->setStripeCustomerId($stripeCustomerId);
                        $model->save();
                    } else {
                        $model->setData($data);
                        $model->save();
                    }
                } catch (\Exception $e) {
                    return $e->getMessage();
                }
            }
        }
    }

    /**
     * GetSavedCards function to get saved cards of the customer.
     *
     * @param string $type
     * @return Webkul\Stripe\Model\StripeCustomer
     */
    public function getSavedCards($type = null)
    {
        if ($this->_customerSession->isLoggedIn()) {
            $customerId = $this->_customerSession->getCustomer()->getId();
            $collection = $this->stripeCustomer->create()
                ->getCollection()
                ->addFieldToFilter('customer_id', ['eq' => $customerId]);
            if ($type) {
                $collection->addFieldToFilter('type', ['eq' => $type]);
            }
            if ($collection->getSize() > 0) {
                return $collection;
            } else {
                return false;
            }
        }
    }

    /**
     * GetLogo get payment type logo
     *
     * @param  string $brand brand type like visa,master
     * @return string
     */
    public function getLogo($brand = '')
    {
        if ($brand == '') {
            return $this->template->getViewFileUrl('Webkul_Stripe/images/wkstripe/logos/placeholder.png');
        } else {
            return $this->template->getViewFileUrl('Webkul_Stripe/images/wkstripe/logos')."/".strtolower($brand).".png";
        }
    }

    /**
     * GetMediaUrl get media url
     *
     * @return string
     */
    public function getMediaUrl()
    {
        return $this->_storeManager->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * GetLocaleFromConfiguration get the current set locale code from configuration.
     *
     * @return String
     */
    public function getLocaleFromConfiguration()
    {
        return $this->_resolver->getLocale();
    }

    /**
     * GetLocaleForStripe return the locale value exixt in stripe api
     *
     * Other wise return "auto"
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
     * @param String $configLocale
     * @return String
     */
    public function matchCodeSupportedByStripeApi($configLocale)
    {
        switch ($configLocale) {
            case "zh":
                $locale = $configLocale;
                break;
            case "da":
                $locale = $configLocale;
                break;
            case "nl":
                $locale = $configLocale;
                break;
            case "en":
                $locale = $configLocale;
                break;
            case "fi":
                $locale = $configLocale;
                break;
            case "fr":
                $locale = $configLocale;
                break;
            case "de":
                $locale = $configLocale;
                break;
            case "it":
                $locale = $configLocale;
                break;
            case "ja":
                $locale = $configLocale;
                break;
            case "no":
                $locale = $configLocale;
                break;
            case "es":
                $locale = $configLocale;
                break;
            case "sv":
                $locale = $configLocale;
                break;
            default:
                $locale = "auto";
                break;
        }

        return $locale;
    }

    /**
     * DebugData debug the data and error message
     *
     * @param mixed $data
     * @return void
     */
    public function debugData($data)
    {
        $this->_paymentMethod->debugData($data);
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
            $secretKey = $this->getConfigValue('api_secret_key');
            $url = "https://api.stripe.com/v1/customers/".$custID;
            $headers =['Authorization: Bearer '.$secretKey,];
            $arr = [
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTPHEADER =>$headers,
                CURLOPT_HEADER => true,
                CURLOPT_RETURNTRANSFER => true,
            ];
            $this->_curl->addHeader('Authorization: Bearer ', $secretKey);
            $this->_curl->setOptions($arr);
            $this->_curl->get($url);
            if ($this->_curl->getStatus() == 200) {
                return 1;
            } else {
                return 0;
            }
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
    }
}
