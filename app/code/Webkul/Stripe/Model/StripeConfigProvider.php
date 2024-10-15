<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Stripe
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Stripe\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Payment\Helper\Data as PaymentHelper;

class StripeConfigProvider implements ConfigProviderInterface
{
    public const DEFAULT_IMAGE = 'stripe-logo.png';

    /**
     * @var string[]
     */
    protected $_methodCode = PaymentMethod::METHOD_CODE;

    /**
     * @var Magento\Payment\Helper\Data
     */
    protected $_method;

    /**
     * @var \Webkul\Stripe\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\View\Element\Template
     */
    protected $template;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_session;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @param PaymentHelper $paymentHelper
     * @param \Webkul\Stripe\Helper\Data $helper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\Element\Template $template
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        \Webkul\Stripe\Helper\Data $helper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Element\Template $template,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->_method = $paymentHelper->getMethodInstance($this->_methodCode);  //it seems no needed. so query
        $this->_helper = $helper;
        $this->_urlBuilder = $urlBuilder;
        $this->_storeManager = $storeManager;
        $this->template = $template;
        $this->_session = $session;
        $this->assetRepo = $assetRepo;
        $this->fileDriver = $fileDriver;
        $this->request = $request;
    }

    /**
     * GetConfig function to return cofig data to payment renderer.
     *
     * @return []
     */
    public function getConfig()
    {
        if (!$this->_helper->getIsActive()) {
            return [];
        }

        /*
         * [$mediaUrl base media folder to get image.
         *
         * @var [type]
         */
        $imageOnForm = $this->_helper->getConfigValue('image_on_form');
        if ($imageOnForm) {
            $mediaImageUrl = $this->_storeManager->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'wkstripe/config/';
            $mediaImageUrl .= $imageOnForm;
        } else {
            $mediaImageUrl = $this->template->getViewFileUrl('Webkul_Stripe/images/wkstripe/config/stripe-logo.png');
            if (!$this->fileDriver->isExists($mediaImageUrl)) {
                $mediaImageUrl = "";
                $params = ['_secure' => $this->request->isSecure()];
                $mediaImageUrl =  $this->assetRepo
                ->getUrlWithParams('Webkul_Stripe::images/wkstripe/config/'.self::DEFAULT_IMAGE, $params);
            }
        }
        /**
         * $config array to pass config data to payment renderer component.
         *
         * @var array
         */
        $config = [
            'payment' => [
                \Webkul\Stripe\Model\PaymentMethod::METHOD_CODE => [
                    'title' => $this->_helper->getConfigValue('title'),
                    'debug' => $this->_helper->getConfigValue('debug'),
                    'api_secret_key' => $this->_helper->getConfigValue('api_secret_key'),
                    'api_publish_key' => $this->_helper->getConfigValue('api_publish_key'),
                    'name_on_form' => $this->_helper->getConfigValue('name_on_form'),
                    'image_on_form' => $mediaImageUrl,
                    'order_status' => $this->_helper->getConfigValue('order_status'), //it seems no needed. so query
                    'payment_action' => $this->_helper->getConfigValue('payment_action'),
                    'min_order_total' => $this->_helper->getConfigValue('min_order_total'),
                    'max_order_total' => $this->_helper->getConfigValue('max_order_total'),
                    'sort_order' => $this->_helper->getConfigValue('sort_order'),
                    'method' => $this->_methodCode,
                    'currency' => $this->_storeManager->getStore()->getCurrentCurrency()->getCode(),
                    'locale' => $this->_helper->getLocaleForStripe(),
                    'billingAddress' => (boolean)$this->_helper->getConfigValue('billing_address'),
                    'shippingAddress' => (boolean)$this->_helper->getConfigValue('shipping_address'),
                    "get_session_url" => $this->_urlBuilder->getUrl("stripe/payment/getsession")
                ],
            ],
        ];
        return $config;
    }
}
