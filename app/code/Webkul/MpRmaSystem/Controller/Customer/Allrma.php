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
     * Initialize Dependencies
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\Url $url
     * @param \Webkul\MpRmaSystem\Helper\Data $helper
     * @param \Magento\Customer\Model\Session $session
     * @return void
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Url $url,
        \Webkul\MpRmaSystem\Helper\Data $helper,
        \Magento\Customer\Model\Session $session
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->url               = $url;
        $this->helper            = $helper;
        $this->session           = $session;
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
        $resultPage = $this->resultPageFactory->create();
        if ($this->helper->getModuleStatus()) {
            $resultPage->getConfig()->getTitle()->set(__('RMA'));
        } else {
            $resultPage = $this->resultRedirectFactory->create()->setPath('customer/account/index');
        }
        return $resultPage;
    }
}
