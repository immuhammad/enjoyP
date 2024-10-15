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
namespace Webkul\MpRmaSystem\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;

class Rma extends \Magento\Framework\App\Action\Action
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
    protected $mpRmaHelper;

    /**
     * Initialize Dependencies
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\Url $url
     * @param \Magento\Customer\Model\Session $session
     * @param \Webkul\MpRmaSystem\Helper\Data $mpRmaHelper
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     * @return void
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Url $url,
        \Magento\Customer\Model\Session $session,
        \Webkul\MpRmaSystem\Helper\Data $mpRmaHelper,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
    ) {
        $this->resultPageFactory    = $resultPageFactory;
        $this->url                  = $url;
        $this->session              = $session;
        $this->mpRmaHelper          = $mpRmaHelper;
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
     * Rma Action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam("id");
        if (!$this->mpRmaHelper->getModuleStatus()) {
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('noroute');
            return $resultForward;
        }
        if (!$this->mpRmaHelper->isValidRma(1)) {
            $this->messageManager->addError(__("Invalid Request"));
            return $this->resultRedirectFactory->create()->setPath('*/*/allrma');
        } else {
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__('View RMA'));
            return $resultPage;
        }
    }
}
