<?php

/**
 * Webkul_Affiliate email helper
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Affiliate\Helper;

use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Webkul\Affiliate\Helper\Data as AffiliateHelper;

/**
 * Webkul Affiliate Email helper
 */
class Email extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Magento\Framework\Translate\Inline\StateInterface
     */
    private $inlineTranslation;

    /**
     * @var Magento\Framework\Mail\Template\TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customer;

    /**
     * @var Webkul\Affiliate\Helper\Data
     */
    private $affiliateHelper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    public $logger;

    private $filterProvider;
    /**
     * @param \Magento\Framework\App\Helper\Context  $context,
     * @param tateInterface                          $inlineTranslation,
     * @param TransportBuilder                       $transportBuilder,
     * @param StoreManagerInterface                  $storeManager,
     * @param CustomerRepositoryInterface            $customer,
     * @param AffiliateHelper                        $affiliateHelper
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Helper\Context $context,
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        CustomerRepositoryInterface $customer,
        \Psr\Log\LoggerInterface $logger,
        AffiliateHelper $affiliateHelper,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider
    ) {
        $this->messageManager = $messageManager;
        $this->logger = $logger;
        parent::__construct($context);
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->customer = $customer;
        $this->affiliateHelper = $affiliateHelper;
        $this->filterProvider=$filterProvider;
    }

    /**
     * [generateTemplate description]
     * @param  Mixed $emailTemplateVariables
     * @param  Mixed $senderInfo
     * @param  Mixed $receiverInfo
     * @return void
     */
    public function generateTemplate(
        $emailTemplateVariables,
        $senderInfo,
        $receiverInfo,
        $emailTempId
    ) {
        try {
          
            $template =  $this->transportBuilder->setTemplateIdentifier($emailTempId)->setTemplateOptions(
                ['area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->storeManager->getStore()->getId()]
            )->setTemplateVars($emailTemplateVariables)->setFrom($senderInfo)->addTo(
                $receiverInfo['email'],
                $receiverInfo['name']
            );
            return $this;
        } catch (\Exception $e) {
            $this->messageManager->addError(__("Unable to Send Mail"));
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * send mail to Affiliate Manager
     * @param int $affUserId affiliate customer id
     * @param int $orderId id
     * @return void
     */

    public function sendMailToAffiliateAdmin($affUserId, $orderId)
    {
        try {
            $customer = $this->customer->getById($affUserId);
            $affiliateConfig = $this->affiliateHelper->getAffiliateConfig();
            $senderInfo = [
                'name' => (string)__('noreply'),
                'email' => $this->scopeConfig->getValue('trans_email/ident_sales/email')
            ];
            $receiverInfo = [
                'name' => (string)__('Affiliate Manager'),
                'email' => $affiliateConfig['manager_email']
            ];
            $action = __('Please review this order'). '<a href="#">#'.$orderId.'</a>'
                            .__(' and approve affiliate order status');
                           
            $emailTempVariables = [
                'notify' => "Order #".$orderId." Placed by reference of Affiliate User "
                                            .$customer->getFirstName()." ".$customer->getLastName(),
                'action' => (string)$action
            ];
            $this->generateTemplate(
                $emailTempVariables,
                $senderInfo,
                $receiverInfo,
                $affiliateConfig['manager_email_template']
            );
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->messageManager->addError(__("Unable to Send Mail"));
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * send mail to Affiliate User on order approve
     * @param int $affUserId affiliate customer id
     * @param int $orderId id
     * @return void
     */
    public function sendMailToAffiliateUser($affUserId, $orderId)
    {
        try {
            $customer = $this->customer->getById($affUserId);
            $affuserName = $customer->getFirstName()." ".$customer->getLastName();
            $affiliateConfig = $this->affiliateHelper->getAffiliateConfig();
            $senderName = __('Affiliate Manager');
            $senderInfo = [
                'name' =>(string)$senderName ,
                'email' => $affiliateConfig['manager_email']
            ];
            $receiverInfo = [
                'name' => (string)$affuserName,
                'email' => $customer->getEmail()
            ];

            $emailTempVariables = [
                'notify' => "Order #".$orderId." Approved For Affiliate User ".$affuserName,
                'message' => __('Order No. #').$orderId
                                .__(' placed with your reference approved and payment added to your account')
            ];

            $this->generateTemplate(
                $emailTempVariables,
                $senderInfo,
                $receiverInfo,
                $this->scopeConfig->getValue('affiliate/general/aff_user_email_template')
            );

            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->messageManager->addError(__("Unable to Send Mail"));
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * send mail to Affiliate User For Account Update
     * @param int $affUserId affiliate customer id
     * @param int $orderId id
     * @return void
     */
    public function accountUpdateNotify($affUserId, $status)
    {
        try {
            $customer = $this->customer->getById($affUserId);
            $affiliateConfig = $this->affiliateHelper->getAffiliateConfig();
            $name = __('Affiliate Manager');
            $senderInfo = [
                            'name' => (string)$name,
                            'email' => $affiliateConfig['manager_email']
                        ];
            $receiverInfo = [
                'name' => $customer->getFirstName()." ".$customer->getLastName(),
                'email' => $customer->getEmail()
            ];

            $emailTempVariables = [
                'notify' => __('Your Affiliate account status updated'),
                'message' => __('Your Affiliate account ').$status
            ];

            $this->generateTemplate(
                $emailTempVariables,
                $senderInfo,
                $receiverInfo,
                $this->scopeConfig->getValue('affiliate/general/aff_user_update_email_template')
            );

            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->messageManager->addError(__("Unable to Send Mail"));
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * send mail to Affiliate User for normal notification
     * @param int $affUserId affiliate customer id
     * @param int $orderId id
     * @return void
     */

    public function sendMailToAffUserForNotify($affUserId, $data)
    {
        try {
            $customer = $this->customer->getById($affUserId);
            $affiliateConfig = $this->affiliateHelper->getAffiliateConfig();
            $name = __('Affiliate Manager');
            $senderInfo = [
                            'name' => (string)$name,
                            'email' => $affiliateConfig['manager_email']
                        ];

            $receiverInfo = [
                'name' => $customer->getFirstName()." ".$customer->getLastName(),
                'email' => $customer->getEmail()
            ];

            $emailTempVariables = [
                'message' => $this->filterProvider->getBlockFilter()
                                ->setStoreId($this->storeManager->getStore()->getId())
                                ->filter($data['email_content']),
                'subject' => $data['email_subject'],
                'name'    => $receiverInfo['name']
            ];

            $this->generateTemplate(
                $emailTempVariables,
                $senderInfo,
                $receiverInfo,
                $affiliateConfig['user_notify_by_admin']
            );

            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->messageManager->addError(__("Unable to Send Mail"));
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * send mail to User for email campaign
     * @param int $affUserId
     * @param string $email
     * @param string $subject
     * @param string $message
     * @return void
     */
    public function emailCampaignMail($affUserId, $email, $subject, $message)
    {
        try {
            $customer = $this->customer->getById($affUserId);
            $affiliateConfig = $this->affiliateHelper->getAffiliateConfig();

            $senderInfo = [
                'name' => $customer->getFirstName()." ".$customer->getLastName(),
                'email' => $customer->getEmail()
            ];
            $receiverInfo = ['name' => 'Friends', 'email' => $email];
            $messageList = explode(' ', $message);
            $message='';
            foreach ($messageList as $mess) {
                if ($mess!='' && filter_var($mess, FILTER_VALIDATE_URL)) {
                    $message = $message.'<a href="'.$mess.'">"'.$mess.'"</a>, ';
                } elseif ($mess!='') {
                    $message = $message.$mess." ";
                }
            }
            $message=trim(trim($message), ",");
            $emailTempVariables = ['message' => $message,'subject' => $subject];
            $this->generateTemplate(
                $emailTempVariables,
                $senderInfo,
                $receiverInfo,
                $affiliateConfig['aff_email_campaign']
            );

            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->messageManager->addError(__("Unable to Send Mail"));
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * Send mail to affiliate user when paymet credited in his bank account
     * @param int $affUserId affiliate customer id
     * @param int $orderId id
     * @return void
     */

    public function mailToAffPaymentCreditedNotify($affUserId, $data)
    {
        try {
            $customer = $this->customer->getById($affUserId);
            $affiliateConfig = $this->affiliateHelper->getAffiliateConfig();

            $senderInfo = [
                'name' => (string)__('Affiliate Manager'),
                'email' =>  (string) $affiliateConfig['manager_email']
            ];
            $receiverInfo = [
                'name' => $customer->getFirstName()." ".$customer->getLastName(),
                'email' => (string)$customer->getEmail()
            ];

            $emailTempVariables = [
                'message' => (string)$data['email_content'],
                'subject' => (string)$data['email_subject'],
                'name'    => (string)$receiverInfo['name']
            ];

            $this->generateTemplate(
                $emailTempVariables,
                $senderInfo,
                $receiverInfo,
                $affiliateConfig['payment_credit_template']
            );

            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->messageManager->addError(__("Unable to Send Mail"));
            $this->logger->info($e->getMessage());
        }
    }
}
