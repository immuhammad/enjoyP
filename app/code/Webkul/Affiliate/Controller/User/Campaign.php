<?php
/**
 * Webkul Affiliate Campaign controller.
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Controller\User;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session;
use Webkul\Affiliate\Model\UserFactory;
use Webkul\Affiliate\Helper\Email as HelperEmail;

class Campaign extends \Webkul\Affiliate\Controller\User\AbstractUser
{
    /**
     * @var Webkul\Affiliate\Helper\Email
     */
    private $helperEmail;
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UserFactory $userFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Session $customerSession,
        UserFactory $userFactory,
        HelperEmail $helperEmail,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {

        $this->helperEmail = $helperEmail;
        parent::__construct($context, $resultPageFactory, $customerSession, $userFactory, $scopeConfig);
    }

    /**
     * Affiliate Email Campaign
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            try {
                $data = $this->getRequest()->getParams();
                if (!empty($data['email'])) {
                    $emailList = explode(',', $data['email']);
                    $affUserId = $this->getCustomerSession()->getCustomerId();
                    $mailCount = 0;
                    foreach ($emailList as $email) {
                        if ($email!='') {
                            /** send account approve mail notification to Affiliate User*/
                            $this->helperEmail->emailCampaignMail(
                                $affUserId,
                                $email,
                                $data['subject'],
                                nl2br($data['message'])
                            );
                            $mailCount++;
                        }
                    }
                    $this->messageManager->addSuccess(__(
                        'Total of %1 e-mail(s) have been sent successfully.',
                        $mailCount
                    ));

                }
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setUrl($this->_url->getUrl('affiliate/user/campaign'));
            } catch (\Exception $e) {
                $this->messageManager->addError(__($e->getMessage()));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setUrl($this->_url->getUrl('affiliate/user/campaign'));
            }
        } else {
            /** @var \Magento\Framework\View\Result\Page $resultPage */
            $resultPage = $this->getResultPageFactory()->create();
            $resultPage->getConfig()->getTitle()->set(__('E-mail Campaign'));
            return $resultPage;
        }
    }
}
