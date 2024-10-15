<?php
/**
 * Webkul Affiliate User Summary controller.
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Controller\User;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session;
use Webkul\Affiliate\Model\UserFactory;

class AbstractUser extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * @var Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var Webkul\Affiliate\Model\UserFactory
     */
    private $userFactory;

    private $scopeConfig;

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
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->customerSession = $customerSession;
        $this->userFactory = $userFactory;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * Check customer authentication
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $affEnable=$this->scopeConfig->getValue('affiliate/general/enable');

        if ($affEnable==1) {
            $customerId = $this->customerSession->getCustomerId();
            $affiUserColl = $this->userFactory->create()->getCollection()
                                                ->addFieldToFilter('customer_id', $customerId);
            $redirect = true;
            if ($affiUserColl->getSize()) {
                foreach ($affiUserColl as $affiliateUser) {
                    $redirect = $affiliateUser->getEnable()==1 ? false : true;
                }
            }
            if ($redirect) {
                $resultRedirect = $this->resultRedirectFactory->create();
                $defaultUrl = $this->_url->getUrl('*/*/status', ['_secure' => true]);
                $resultRedirect->setUrl($defaultUrl);
                return $resultRedirect;
            }
            return parent::dispatch($request);
        } else {
            $resultRedirect = $this->resultRedirectFactory->create();
            $defaultUrl = $this->_url->getUrl('customer/account', ['_secure' => true]);
            $resultRedirect->setUrl($defaultUrl);
            return $resultRedirect;
        }
    }

    /**
     * User Controller page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Affiliate User Summary'));
        return $resultPage;
    }

    /**
     * getCustomerSession
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function getCustomerSession()
    {
        return $this->customerSession;
    }

    /**
     * getAffiliateUserFactory
     *
     * @return Webkul\Affiliate\Model\UserFactory
     */
    protected function getAffiliateUserFactory()
    {
        return $this->userFactory;
    }

    /**
     * getResultPageFactory
     *
     * @return Magento\Framework\View\Result\PageFactory
     */
    protected function getResultPageFactory()
    {
        return $this->resultPageFactory;
    }

    /**
     * saveObject
     * @return void
     */
    protected function saveObject($object)
    {
        $object->save();
    }
}
