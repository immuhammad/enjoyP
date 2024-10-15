<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpRmaSystem
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpRmaSystem\Controller\Seller;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;

class Allrma extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $url;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * @var \Webkul\MpRmaSystem\Helper\Data
     */
    protected $helper;

    /**
     * Initialize Dependencies
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\Url $url
     * @param \Magento\Customer\Model\Session $session
     * @param \Webkul\MpRmaSystem\Helper\Data $helper
     * @param \Webkul\Marketplace\Helper\Data $mphelper
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     * @return void
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Url $url,
        \Magento\Customer\Model\Session $session,
        \Webkul\MpRmaSystem\Helper\Data $helper,
        \Webkul\Marketplace\Helper\Data $mphelper,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
    ) {
        $this->resultPageFactory    = $resultPageFactory;
        $this->url                  = $url;
        $this->session              = $session;
        $this->helper               = $helper;
        $this->mphelper             = $mphelper;
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
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
        if (!$this->session->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * All Rma Action
     *
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\Result\Forward
     */
    public function execute()
    {
        if ($this->helper->getModuleStatus()) {
            $resultPage = $this->resultPageFactory->create();
            $isPartner = $this->mphelper->isSeller();
            if ($isPartner == 1) {
                if ($this->helper->getIsSeparatePanel()) {
                    $resultPage->addHandle('mprmasystem_seller_allrma_layout2');
                }

                $resultPage = $this->resultPageFactory->create();
                $resultPage->getConfig()->getTitle()->set(__('Marketplace RMA'));
                return $resultPage;
            } else {
                return $this->resultRedirectFactory->create()->setPath(
                    'marketplace/account/becomeseller',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } else {
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('noroute');
            return $resultForward;
        }
    }
}
