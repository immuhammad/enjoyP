<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpmembership
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpmembership\Helper;

/**
 * Webkul Mpmembership Helper Email.
 */
class Email extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_EMAIL_SUCCESS_TRANSACTION = 'marketplace/mpmembership_settings/transaction_completed_template';

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $_inlineTranslation;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    protected $_template;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Webkul\Mpmembership\Logger\Logger
     */
    protected $logger;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Webkul\Mpmembership\Logger\Logger $logger
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\Mpmembership\Logger\Logger $logger
    ) {
        parent::__construct($context);
        $this->_inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * Return store configuration value.
     *
     * @param string $path
     * @param int    $storeId
     *
     * @return mixed
     */
    protected function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Return store.
     *
     * @return Store
     */
    public function getStore()
    {
        try {
            return $this->_storeManager->getStore();
        } catch (\Exception $e) {
            $this->logger->info(
                "Helper_Email_getStore Exception : ".$e->getMessage()
            );
        }
    }

    /**
     * Return template id.
     *
     * @return mixed
     */
    public function getTemplateId($xmlPath)
    {
        return $this->getConfigValue($xmlPath, $this->getStore()->getStoreId());
    }

    /**
     * [generateTemplate description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        try {
            $template = $this->_transportBuilder->setTemplateIdentifier($this->_template)
                ->setTemplateOptions(
                    [
                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                            'store' => $this->_storeManager->getStore()->getId(),
                        ]
                )
                ->setTemplateVars($emailTemplateVariables)
                ->setFrom($senderInfo)
                ->addTo($receiverInfo['email'], $receiverInfo['name']);
        } catch (\Exception $e) {
            $this->logger->info(
                "Helper_Email_generateTemplate Exception : ".$e->getMessage()
            );
        }
        return $this;
    }

    /**
     * [sendTransactionEmailToSeller description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function sendTransactionEmailToSeller($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        try {
            $this->_template = $this->getTemplateId(self::XML_PATH_EMAIL_SUCCESS_TRANSACTION);
            $this->_inlineTranslation->suspend();
            $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
            $this->_inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->logger->info(
                "Helper_Email_sendTransactionEmailToSeller Exception : ".$e->getMessage()
            );
        }
    }
}
