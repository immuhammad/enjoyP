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

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Payment\Helper\Data as PaymentHelper;
use Webkul\MpStripe\Model\Source\PaymentAction;
use Magento\Customer\Model\Session;

class StripeConfigProvider implements ConfigProviderInterface
{
    public const DEFAULT_IMAGE = 'stripe-logo.png';
    /**
     * @var string
     */
    private $methodCode = PaymentMethod::METHOD_CODE;

    /**
     * @var Magento\Payment\Helper\Data
     */
    private $method;

    /**
     * @var \Webkul\MpStripe\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * __construct function
     *
     * @param Session $customerSession
     * @param PaymentHelper $paymentHelper
     * @param \Webkul\MpStripe\Helper\Data $helper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\Element\Template $template
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Webkul\Marketplace\Helper\Data $marketplaceData
     * @param Escaper $escaper
     */
    public function __construct(
        Session $customerSession,
        PaymentHelper $paymentHelper,
        \Webkul\MpStripe\Helper\Data $helper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Element\Template $template,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Webkul\Marketplace\Helper\Data $marketplaceData,
        Escaper $escaper
    ) {
    
        $this->method = $paymentHelper->getMethodInstance($this->methodCode);
        $this->helper = $helper;
        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
        $this->template = $template;
        $this->fileDriver = $fileDriver;
        $this->jsonHelper = $jsonHelper;
        $this->assetRepo = $assetRepo;
        $this->request = $request;
        $this->escaper = $escaper;
        $this->_checkoutSession = $checkoutSession;
        $this->marketplaceData = $marketplaceData;
        $this->customerSession = $customerSession;
    }

    /**
     * GetConfig function to return cofig data to payment renderer.
     *
     * @return []
     */
    public function getConfig()
    {
        if (!$this->helper->getIsActive()) {
            return [];
        }

        /*
         * [$mediaUrl base media folder to get image.
         *
         * @var [type]
         */
        $imageOnForm = $this->helper->getConfigValue('image_on_form');
        if ($imageOnForm) {
            $mediaImageUrl = $this->storeManager->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'mpstripe/config/';
            $mediaImageUrl .= $imageOnForm;
        } else {
            $mediaImageUrl = $this->template->getViewFileUrl('Webkul_MpStripe/images/mpstripe/config/stripe-logo.png');
            if (!$this->fileDriver->isExists($mediaImageUrl)) {
                $mediaImageUrl = "";
                $params = ['_secure' => $this->request->isSecure()];
                $mediaImageUrl =  $this->assetRepo
                ->getUrlWithParams('Webkul_MpStripe::images/mpstripe/config/'.self::DEFAULT_IMAGE, $params);
            }
        }
        $canCapture = true;
        if ($this->helper->getConfigValue('stripe_payment_action') == PaymentAction::STRIPE_ACTION_AUTHORIZE) {
            $canCapture = false;
        }
        /**
         * $config array to pass config data to payment renderer component.
         *
         * @var array
         */
        $config = [
            'payment' => [
                'mpstripe' => [
                    'title' => $this->helper->getConfigValue('title'),
                    'canCapture' => $canCapture,
                    'vaultActive' => (
                        $this->helper->getConfigValue('vault_active') && $this->customerSession->isLoggedIn()
                    ),
                    'debug' => $this->helper->getConfigValue('debug'),
                    'api_key' => $this->helper->getConfigValue('api_key'),
                    'api_publish_key' => $this->helper->getConfigValue('api_publish_key'),
                    'name_on_form' => $this->helper->getConfigValue('name_on_form'),
                    'image_on_form' => $mediaImageUrl,
                    'min_order_total' => $this->helper->getConfigValue('min_order_total'),
                    'max_order_total' => $this->helper->getConfigValue('max_order_total'),
                    'sort_order' => $this->helper->getConfigValue('sort_order'),
                    'method' => $this->methodCode,
                    'saved_cards' => $this->getSavedCards(),
                    'stripe_user_id' => $this->getCurrentCustomerInfo(),
                    'currency' => $this->storeManager->getStore()->getCurrentCurrency()->getCode(),
                    'locale' => $this->helper->getLocaleForStripe(),
                    'getPaymentIntentUrl' => $this->urlBuilder->getUrl("mpstripe/payment/createintent"),
                    'cancelOrder' => $this->urlBuilder->getUrl("mpstripe/payment/cancelorder"),
                    'checkoutCart' => $this->urlBuilder->getUrl("checkout/cart"),
                    'is_direct_charge' => $this->helper->isDirectCharge()
                ],
            ],
        ];

        return $config;
    }

    /**
     * GetSavedCards function to get customers cards json data.
     *
     * @return json
     */
    public function getSavedCards()
    {
        $cardsArray = [];
        $cards = $this->helper->getSavedCards();
        if (!empty($cards) && count($cards['data']) > 0 && $cards != false) {
            foreach ($cards['data'] as $card) {
                array_push(
                    $cardsArray,
                    [
                        'exp_month' => $card['card']['exp_month'],
                        'exp_year' => $card['card']['exp_year'],
                        'stripe_customer_id' => $card['customer'],
                        'last4' => '****'.$card['card']['last4'],
                        'payment_method_id' => $card['id'],
                        'brand' => strtoupper($card['card']['brand'])
                    ]
                );
            }
        }

        return $this->jsonHelper->jsonEncode($cardsArray);
    }

    /**
     * Get sellet account id
     *
     * @return accountId
     */
    public function getCurrentCustomerInfo()
    {
        $stripeCustomerId = '';
        $items = $this->_checkoutSession->getQuote()->getAllItems();
        $productId = "";
        foreach ($items as $item) {
            $productId = $item->getProductId();
        }
        $stripeCustomerId = $this->marketplaceData->getSellerIdByProductId($productId);
        $customerId = $stripeCustomerId;
        if ($customerId == null || $customerId == 0) {
            return 0;
        }
        if ($customerId != null && $customerId > 0) {
            $collection = $this->helper->getStripeSeller($customerId);
            if ($collection != false) {
                return $collection->getData()["stripe_user_id"];
            } else {
                return false;
            }
        }
    }
}
