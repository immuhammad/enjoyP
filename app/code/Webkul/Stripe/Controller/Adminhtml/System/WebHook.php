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
namespace Webkul\Stripe\Controller\Adminhtml\System;

use Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Catalog\Model\Product\Visibility;
use Magento\Store\Model\StoreManagerInterface;

class WebHook extends \Magento\Backend\App\Action
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Webkul\Stripe\Helper\Data $helper
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\Url $urlHelper
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Webkul\Stripe\Helper\Data $helper,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Url $urlHelper,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->messageManager = $messageManager;
        $this->_logger = $logger;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->configWriter = $configWriter;
        $this->storeManager = $storeManager;
        $this->urlHelper = $urlHelper;
        $this->helper = $helper;
        parent::__construct($context);
    }
    
    /**
     *  To create webhooks on Stripe
     *
     * @return json data
     */
    public function execute()
    {
        $resultJson = $this->jsonResultFactory->create();
        $webHookId = $this->scopeConfig
        ->getValue('payment/stripe/webhook_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $secretkey = $this->scopeConfig
        ->getValue('payment/stripe/api_secret_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (!$webHookId || !$secretkey) {
            \Stripe\Stripe::setApiKey($this->helper->getConfigValue('api_secret_key'));
            
            \Stripe\Stripe::setAppInfo(
                "Webkul Stripe Payment Gateway For Magento 2",
                "3.0.0",
                "https://store.webkul.com/magento2-stripe-payment-gateway.html",
                "pp_partner_FLJSvfbQDaJTyY"
            );
            \Stripe\Stripe::setApiVersion("2019-12-03");
            $storeId = $this->storeManager->getDefaultStoreView()->getStoreId();

            $webHookResponse = \Stripe\WebhookEndpoint::create([
                "url" => $this->urlHelper->getBaseUrl().'rest/V1/stripe/webhook',
                "enabled_events" => [
                    "checkout.session.completed",
                    "payment_intent.payment_failed",
                    "payment_intent.succeeded",
                    "charge.captured",
                    "charge.failed",
                    "charge.refunded",
                    "charge.succeeded"
                ]
            ]);
            if ($webHookResponse['id']) {
                $this->configWriter
                    ->save(
                        'payment/stripe/webhook_id',
                        $webHookResponse['id'],
                        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        $scopeId = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    );
                $response['error'] = 0;
                $message = __('WebHooks Generated Successfully');
                $this->messageManager->addSuccess($message);
            } else {
                $response['error'] = 1;
                $message = __('Invalid Request Check Credentials');
                $this->messageManager->addError($message);
            }
            
            return $resultJson->setData($response);
        } elseif ($webHookId) {
            $response['error'] = 1;
            $message = __('WebHooks Already Generated');
            $this->messageManager->addSuccess($message);
            return $resultJson->setData($response);
        } else {
            $response['error'] = 1;
            $message = __('Invalid Request Check Credentials');
            $this->messageManager->addError($message);
            return $resultJson->setData($response);
        }
    }
}
