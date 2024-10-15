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

namespace Webkul\MpStripe\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Webkul\MpStripe\Helper\Data as HelperData;
use Webkul\MpStripe\Model\Source\ChargeType;
use \Magento\Framework\Filesystem\Io\File as IoFile;
use \Magento\Framework\Filesystem\Driver\File;

/**
 * Webkul MpStripe PreDispatchConfigSaveObserver Observer.
 */
class PreDispatchConfigSaveObserver implements ObserverInterface
{
    /**
     * @var ManagerInterface
     */
    private $_messageManager;

    /**
     * @var HelperData
     */
    private $_helper;

    /**
     * @var IoFile
     */
    protected $_filesystemFile;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_http;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param ManagerInterface $messageManager
     * @param HelperData $helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Webkul\MpStripe\Model\StripeSellerFactory $stripeSellerModel
     * @param \Magento\Framework\App\Request\Http $http
     */
    public function __construct(
        ManagerInterface $messageManager,
        HelperData $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Webkul\MpStripe\Model\StripeSellerFactory $stripeSellerModel,
        \Magento\Framework\App\Request\Http $http
    ) {
        $this->_messageManager = $messageManager;
        $this->_helper = $helper;
        $this->_http = $http;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
        $this->stripeSellerModel = $stripeSellerModel;
    }

    /**
     * Execute function
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        
        try {
            
            $observerRequestData = $observer['request'];
            $params = $observerRequestData->getParams();
            
            if ($params['section'] == 'payment') {
                $currentDebugMode = $params['groups']['mpstripe']['fields']['debug']['value'];
                $previousDebugMode = $this->getConfig('payment/mpstripe/debug');
                if (($previousDebugMode != '') && ($previousDebugMode != $currentDebugMode)) {
                    $webhookId = $this->getConfig('payment/mpstripe/webhook_id');
                    if ($webhookId != '') {
                        $this->_helper->setUpDefaultDetails();
                        $webhookEndpoint = \Stripe\WebhookEndpoint::retrieve(
                            $webhookId
                        );
                        $webhookEndpoint->delete();
                    }
                    $this->configWriter->save('payment/mpstripe/webhook_id', '');
                }
                $currentIntegration = $params['groups']['mpstripe']['fields']['integration']['value'];
                $previousIntegration = $this->getConfig('payment/mpstripe/integration');
                if (($previousIntegration != '') && ($previousIntegration != $currentIntegration)) {
                    // send email here
                    $stripeCollection = $this->stripeSellerModel->create()->getCollection()
                    ->addFieldToFilter('integration_type', $previousIntegration);
                    foreach ($stripeCollection as $stripeUser) {
                        $stripeUser->delete();
                    }
                }
                $currentChargeType = $params['groups']['mpstripe']['fields']['charge_type']['value'];
                $previousChargeType = $this->getConfig('payment/mpstripe/charge_type');
                if ($currentChargeType === ChargeType::DIRECT_CHARGE && ($previousChargeType != $currentChargeType)) {
                    $this->configWriter->save('payment/mpstripe/vault_active', 0);
                }
            }
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
    }

    /**
     * GetConfig function
     *
     * @param string $configPath
     * @return string
     */
    public function getConfig($configPath)
    {
        return $this->scopeConfig->getValue(
            $configPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
