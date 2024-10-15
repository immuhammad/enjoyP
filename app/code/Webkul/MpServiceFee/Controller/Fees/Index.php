<?php
/**
 * Webkul Software.
 *
 * @category   Webkul
 * @package    Webkul_MpServiceFee
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpServiceFee\Controller\Fees;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;

class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var Webkul\MpGDPR\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $url;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelperData;
    /**
     * Class constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param \Webkul\MpServiceFee\Helper\Servicehelper $helper
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Customer\Model\Url $url
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelperData
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Session $customerSession,
        \Webkul\MpServiceFee\Helper\Servicehelper $helper,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Model\Url $url,
        \Webkul\Marketplace\Helper\Data $marketplaceHelperData
    ) {
    
        $this->customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        $this->coreRegistry = $coreRegistry;
        $this->url = $url;
        $this->marketplaceHelperData = $marketplaceHelperData;
        parent::__construct($context);
    }

    /**
     * Retrieve customer session object.
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->customerSession;
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->url->getLoginUrl();
        if (!$this->customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }
    
    /**
     * Execute
     *
     * @return void
     */
    public function execute()
    {
        $pageLabel = 'Service Fees';
        $resultPage = $this->resultPageFactory->create();
        $isPartner = $this->marketplaceHelperData->isSeller();
        if ($isPartner == 1) {
            if ($this->helper->isModuleEnable()) {
                $resultPage = $this->resultPageFactory->create();
                if ($this->marketplaceHelperData->getIsSeparatePanel()) {
                    $resultPage->addHandle('mpservicefee_layout2_servicefee_fees_index');
                }
                $resultPage->getConfig()->getTitle()->set(__("Service Fees"));
                return $resultPage;
            } else {
                return $this->resultRedirectFactory->create()->setPath(
                    'marketplace/account/dashboard',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
