<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_B2BMarketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\B2BMarketplace\Helper;

use Magento\Customer\Model\Session;
use Magento\Framework\Exception\MailException;

/**
 * Webkul B2BMarketplace Helper Email.
 */
class Email extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_EMAIL_NEW_MESSAGE = 'marketplace/email/b2b_marketplace_new_message_template';
    const XML_PATH_EMAIL_NEW_ACCOUNT = 'customer_create_account_b2b_marketplace_email_template';
    const XML_PATH_EMAIL_VERIFIED_SUPPIER = 'marketplace/email/b2b_marketplace_verified_supplier_template';
    const XML_PATH_EMAIL_RFQ_SUPPLIER = 'marketplace/email/rfq_mail_to_seller_template';
    const XML_PATH_EMAIL_RFQ_REPLY_SUPPLIER = 'marketplace/email/rfq_reply_mail_to_seller_template';
    const XML_PATH_EMAIL_RFQ_REPLY_CUSTOMER = 'marketplace/email/rfq_reply_mail_to_customer_template';

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $_inlineTranslation;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    protected $_template;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    protected $_messageManager;

    /**
     * @param Magento\Framework\App\Helper\Context              $context
     * @param Magento\Framework\ObjectManagerInterface          $objectManager
     * @param Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param Magento\Framework\Mail\Template\TransportBuilder  $transportBuilder
     * @param Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param Session                                           $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Session $customerSession
    ) {
        $this->_objectManager = $objectManager;
        parent::__construct($context);
        $this->_inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_customerSession = $customerSession;
        $this->_storeManager = $storeManager;
        $this->_messageManager = $messageManager;
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
        return $this->_storeManager->getStore();
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
        $senderEmail = $senderInfo['email'];
        $adminEmail = $this->getConfigValue(
            'trans_email/ident_general/email',
            $this->getStore()->getStoreId()
        );
        $senderInfo['email'] = $adminEmail;
        $template = $this->_transportBuilder->setTemplateIdentifier($this->_template)
            ->setTemplateOptions(
                [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $this->_storeManager->getStore()->getId(),
                    ]
            )
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom($senderInfo)
            ->addTo($receiverInfo['email'], $receiverInfo['name'])
            ->setReplyTo($senderEmail, $senderInfo['name']);
        return $this;
    }

    /*transaction email template*/
    /**
     * [New Message Mail description].
     *
     * @param Mixed $data     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function newMessageEmail($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $this->_template = $this->getTemplateId(self::XML_PATH_EMAIL_NEW_MESSAGE);

        $this->_inlineTranslation->suspend();

        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }

        $this->_inlineTranslation->resume();
    }

    /*transaction email template*/
    /**
     * [New Account Mail description].
     *
     * @param Mixed $data     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function newAccountEmail($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $this->_inlineTranslation->suspend();

        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->_transportBuilder
            ->setTemplateIdentifier(self::XML_PATH_EMAIL_NEW_ACCOUNT)->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }

        $this->_inlineTranslation->resume();
    }

    /*transaction email template*/
    /**
     * [Verified Suppier Mail description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function verifiedSupplierEmail($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $this->_template = $this->getTemplateId(self::XML_PATH_EMAIL_VERIFIED_SUPPIER);

        $this->_inlineTranslation->suspend();

        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }

        $this->_inlineTranslation->resume();
    }

    /*transaction email template*/
    /**
     * [RFQ Email to Supplier description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function rfqSupplierEmail($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $this->_template = $this->getTemplateId(self::XML_PATH_EMAIL_RFQ_SUPPLIER);

        $this->_inlineTranslation->suspend();

        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }

        $this->_inlineTranslation->resume();
    }

    /*transaction email template*/
    /**
     * [RFQ Reply Email to Supplier description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function rfqReplyToSupplierEmail($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $this->_template = $this->getTemplateId(self::XML_PATH_EMAIL_RFQ_REPLY_SUPPLIER);

        $this->_inlineTranslation->suspend();

        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }

        $this->_inlineTranslation->resume();
    }

    /*transaction email template*/
    /**
     * [RFQ Reply Email to Customer description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function rfqReplyToCustomerEmail($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $this->_template = $this->getTemplateId(self::XML_PATH_EMAIL_RFQ_REPLY_CUSTOMER);

        $this->_inlineTranslation->suspend();

        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }

        $this->_inlineTranslation->resume();
    }
}
