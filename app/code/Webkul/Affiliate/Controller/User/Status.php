<?php
/**
 * Webkul Affiliate User Status controller.
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
use Magento\Framework\App\Config\ScopeConfigInterface;
use Webkul\Affiliate\Model\UserFactory;
use Webkul\Affiliate\Model\UserBalanceFactory;
use Webkul\Affiliate\Helper\Data as AffDataHelper;

class Status extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var Webkul\Affiliate\Helper\Data
     */
    private $affDataHelper;

    /**
     * @var UserFactory
     */
    private $userFactory;

    /**
     * @var UserBalanceFactory
     */
    private $userBalance;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Session $customerSession,
        AffDataHelper $affDataHelper,
        UserFactory $userFactory,
        UserBalanceFactory $userBalance,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {

        $this->resultPageFactory = $resultPageFactory;
        $this->customerSession = $customerSession;
        $this->affDataHelper = $affDataHelper;
        $this->userFactory = $userFactory;
        $this->userBalance = $userBalance;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * Add Auction on product page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $affEnable=$this->scopeConfig->getValue('affiliate/general/enable');
        if ($affEnable==1) {
            /** @var \Magento\Framework\View\Result\Page $resultPage */
            if ($this->affDataHelper->getConfigData()) {
                if ($this->getRequest()->isPost()) {
                    $customerId = $this->customerSession->getCustomerId();
                    $affiliateColl = $this->userFactory->create()->getCollection()
                                                    ->addFieldToFilter('customer_id', $customerId);
                    $postValues = $this->getRequest()->getPostValue();
                    $affiConfig = $this->affDataHelper->getAffiliateConfig();
                    $autoApprove = $affiConfig['auto_approve'];
                    if ($affiliateColl->getSize()) {
                        $this->saveBlog($affiliateColl, $postValues, $autoApprove);
                    } else {
                        if (isset($postValues['aff_conf']) && $postValues['aff_conf'] == 1) {
                            $affiUserTmp = $this->userFactory->create();
                            $affData = [
                                'customer_id' => $customerId,
                                'blog_url' => $postValues['blog_url'],
                                'enable' => $autoApprove,
                                'pay_per_click' => $affiConfig['per_click'],
                                'pay_per_unique_click' => $affiConfig['unique_click'],
                                'commission_type' => $affiConfig['type_on_sale'],
                                'commission' => $affiConfig['rate'],
                                ];
                            $affiUserTmp->setData($affData);
                            $affiUserTmp->save();

                            $tempUserBal = $this->userBalance->create();
                            $tempUserBal->setData(['aff_customer_id' => $customerId]);
                            $tempUserBal->save();
                            $this->messageManager
                                    ->addSuccess(__('Successfully applied for affiliate user.'));
                        } else {
                            $this->messageManager
                                    ->addError(__('Accept affiliate terms for register as affiliate user.'));
                        }
                    }
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $defaultUrl = $this->_url->getUrl('*/*/status', ['_secure' => true]);
                    $resultRedirect->setUrl($defaultUrl);
                    return $resultRedirect;
                } else {
                    $resultPage = $this->resultPageFactory->create();
                    $resultPage->getConfig()->getTitle()->set(__('Affiliate User Status'));
                    return $resultPage;
                }
            } else {
                $url=$this->affDataHelper->getRegistrationUrl();
                $this->_redirect($url);
            }
        } else {
            $this->_redirect("customer/account/");
        }
    }
    
    /**
     * Save Object
     * @param Object $object
     */
    private function _saveObject($object)
    {
        $object->save();
    }
   
    public function saveBlog($affiliateColl, $postValues, $autoApprove)
    {
        foreach ($affiliateColl as $affiliateUser) {
            if ($postValues['blog_url'] !== $affiliateUser->getBlogUrl()) {
                if (filter_var($postValues['blog_url'], FILTER_VALIDATE_URL)) {
                    $affiliateUser->setBlogUrl($postValues['blog_url']);
                    $affiliateUser->setEntityId($affiliateUser->getEntityId());
                    $affiliateUser->setEnable($autoApprove);
                    $this->_saveObject($affiliateUser);
                    $this->messageManager->addSuccess(__('Blog Url Successfully saved.'));
                } else {
                    $this->messageManager->addError(__('Blog Url is invalid.'));
                }
            }
        }
    }
}
