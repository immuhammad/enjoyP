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
namespace Webkul\MpStripe\Model\Payment;

use Webkul\MpStripe\Api\WebhookInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;

class WebHook implements WebhookInterface
{
    /**
     * @param \Webkul\MpStripe\Helper\Data $helper
     * @param JsonHelper $jsonHelper
     * @param \Magento\Framework\Filesystem\Driver\File $driver
     * @param \Webkul\MpStripe\Logger\StripeLogger $logger
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
     * @param array $webhookEvent
     */
    public function __construct(
        \Webkul\MpStripe\Helper\Data $helper,
        JsonHelper $jsonHelper,
        \Magento\Framework\Filesystem\Driver\File $driver,
        \Webkul\MpStripe\Logger\StripeLogger $logger,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        $webhookEvent = []
    ) {
        $this->helper = $helper;
        $this->driver = $driver;
        $this->jsonHelper = $jsonHelper;
        $this->logger = $logger;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->_webhookEvent = $webhookEvent;
    }

    /**
     * Handle Webhook implementation
     *
     * @return \Magento\Framework\Controller\Result\JsonFactory
     */
    public function executeWebhook()
    {
        $data = $this->driver->fileGetContents('php://input');
        $stripeResponse = $this->jsonHelper->jsonDecode($data);
        $webhookType = $stripeResponse['type'];
        $this->logger->critical('webhookType '.$webhookType);
        
        if ($webhookType && isset($this->_webhookEvent[$webhookType])) {
            try {
                $this->_webhookEvent[$webhookType]->process($stripeResponse);
            } catch (\Exception $e) {
                $this->logger->critical('webhook error '.$e->getMessage());
                $this->logger->critical('webhook trace '.json_encode($e->getTrace()));
            }
        }
        $result = $this->jsonResultFactory->create();
        
        $result->setHttpResponseCode(200);
        return $result;
    }
}
