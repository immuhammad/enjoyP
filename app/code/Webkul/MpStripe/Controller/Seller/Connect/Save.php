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
namespace Webkul\MpStripe\Controller\Seller\Connect;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;

class Save extends Action
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;
    /**
     * @var Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Webkul\MpStripe\Helper\Data
     */
    private $helper;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    private $marketplaceHelper;

    /**
     * @var \Magento\Customer\Model\Url
     */
    private $customerUrl;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param \Webkul\MpStripe\Helper\Data $helper
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelper
     * @param \Magento\Customer\Model\Url $customerUrl
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Session $customerSession,
        \Webkul\MpStripe\Helper\Data $helper,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Magento\Customer\Model\Url $customerUrl
    ) {
        $this->helper = $helper;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->customerUrl = $customerUrl;
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
        $loginUrl =
        $this->customerUrl
        ->getLoginUrl();

        if (!$this->customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * Connect to stripe.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $isPartner = $this->marketplaceHelper->isSeller();
        if ($isPartner == 1) {
            $res = $this->helper->saveStripeSeller($this->getRequest()->getParams());
            if ($res) {
                $this
                    ->messageManager
                    ->addError(
                        __('There some error, not able connect you with stripe, please contact admin')
                    );
            } else {
                $this->messageManager->addSuccess(__('You are successfully connected to stripe'));
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                '*/*/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
