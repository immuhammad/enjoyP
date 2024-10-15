<?php
/**
 * Webkul Affiliate User Controller
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Affiliate\Controller\Adminhtml\User;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Webkul\Affiliate\Helper\Email as AffiliateEmail;
use Webkul\Affiliate\Model\UserFactory;

class EmailNotify extends \Magento\Backend\App\Action
{
    /**
     * @var AffiliateEmail
     */
    private $affiliateEmail;

    /**
     * @var UserFactory
     */
    private $userFactory;

    /**
     * @param Context        $context,
     * @param AffiliateEmail $affiliateEmail,
     * @param UserFactory    $userFactory
     */
    public function __construct(
        Context $context,
        AffiliateEmail $affiliateEmail,
        UserFactory $userFactory
    ) {

        parent::__construct($context);
        $this->affiliateEmail = $affiliateEmail;
        $this->userFactory = $userFactory;
    }

    /**
     * Mapped Mapcategory List page.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            try {
                $data = $this->getRequest()->getParams();
                if (isset($data['affiliate_user'])) {
                    $affiliateUserList = $this->userFactory->create()->getCollection()
                                                            ->addFieldTOFilter('enable', ['eq' => 1]);

                    if ((int)$data['affiliate_user']) {
                        $affiliateUserList = $affiliateUserList->addFieldTOFilter(
                            'customer_id',
                            ['eq' => $data['affiliate_user']]
                        );
                    }
                    foreach ($affiliateUserList as $affiliateUser) {
                        $this->affiliateEmail->sendMailToAffUserForNotify($affiliateUser->getCustomerId(), $data);
                    }
                    $this->messageManager->addSuccess(__('Your e-mail was successfully sent.'));
                } else {
                    $this->messageManager->addError(__('Invalid request'));
                }
            } catch (\Exception $e) {
                $this->messageManager->addError(__($e->getMessage()));
            }
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/emailnotify');
        } else {
            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            $resultPage->getConfig()->getTitle()->prepend(__('Send Email To Affiliate User'));
            $resultPage->addBreadcrumb(__('Send Mail To Affiliate User'), __('Send Mail To Affiliate User'));
            $resultPage->addContent(
                $resultPage->getLayout()->createBlock(\Webkul\Affiliate\Block\Adminhtml\User\EmailNotify::class)
            );
            return $resultPage;
        }
    }

    /**
     * Check permission
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Affiliate::affiliate_email');
    }
}
