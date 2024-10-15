<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Stripe
 * @author Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\Stripe\Observer;

use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use \Webkul\Stripe\Model\PaymentMethod;

class PreDispatchConfigSaveObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var Magento\Framework\Message\ManagerInterface
     */
    private $_messageManager;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ManagerInterface $messageManager
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ManagerInterface $messageManager,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->_messageManager = $messageManager;
        $this->configWriter = $configWriter;
    }

    /**
     * Pre dispatch save admin config
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        try {

            $observerRequestData = $observer['request'];
            $params = $observerRequestData->getParams();
            if ($params['section'] == 'payment') {
                $currentDebugMode = $params['groups'][PaymentMethod::METHOD_CODE]['fields']['debug']['value'];
                $previousDebugMode = $this->getConfig('payment/stripe/debug');
                $webhook_id = $this->getConfig('payment/stripe/webhook_id');

                if ($previousDebugMode != $currentDebugMode) {
                    $this->configWriter->save('payment/stripe/webhook_id', '');

                }

                $apiPublishKey = $params['groups'][PaymentMethod::METHOD_CODE]['fields']['api_publish_key']['value'];
                $apiSecretKey = $params['groups'][PaymentMethod::METHOD_CODE]['fields']['api_secret_key']['value'];
                
                if (!preg_match('/^\*+$/', $apiPublishKey) || !preg_match('/^\*+$/', $apiSecretKey)) {
                    $this->configWriter->save('payment/stripe/webhook_id', '');
                }
               
            }
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
    }

    /**
     * Get config
     *
     * @param config_path $configPath
     */
    public function getConfig($configPath)
    {
        return $value = $this->scopeConfig->getValue($configPath);
    }
}
