<?php
/**
 * Webkul Affiliate Preferences controller.
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Controller\User;

class Preferences extends \Webkul\Affiliate\Controller\User\AbstractUser
{
    /**
     * Affiliate Preferences
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            $customerId = $this->getCustomerSession()->getCustomerId();
            $data = $this->getRequest()->getPostValue();
            $userColl = $this->getAffiliateUserFactory()->create()->getCollection()
                                ->addFieldToFilter('customer_id', $customerId);
            if ($userColl->getSize()) {
                foreach ($userColl as $userData) {
                    unset($data['form_key']);
                    $userData->setCurrentPaymentMethod(json_encode($data));
                    $this->saveObject($userData);
                    $this->messageManager->addSuccess(__('Payment method saved successfully.'));
                }
            }
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setUrl($this->_url->getUrl('affiliate/user/preferences'));
        } else {
            /** @var \Magento\Framework\View\Result\Page $resultPage */
            $resultPage = $this->getResultPageFactory()->create();
            $resultPage->getConfig()->getTitle()->set(__('Payment Preference'));
            return $resultPage;
        }
    }
}
