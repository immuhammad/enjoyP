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
namespace Webkul\MpRmaSystem\Controller\Guest;

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
     * @var \Webkul\MpRmaSystem\Helper\Data
     */
    protected $mpRmaHelper;

    /**
     * Initialize Dependencies
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Webkul\MpRmaSystem\Helper\Data $mpRmaHelper
     * @return void
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Webkul\MpRmaSystem\Helper\Data $mpRmaHelper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->mpRmaHelper       = $mpRmaHelper;
        parent::__construct($context);
    }

    /**
     * Guest Rma Action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $helper = $this->mpRmaHelper;
        if ($helper->isLoggedIn()) {
            return $this->resultRedirectFactory
                        ->create()
                        ->setPath('*/customer/allrma');
        }

        if (!$helper->isGuestLoggedIn()) {
            return $this->resultRedirectFactory
                        ->create()
                        ->setPath('*/*/login');
        }

        $id = $this->getRequest()->getParam("id");
        if (!$helper->isValidRma(2)) {
            $this->messageManager->addError(__("Invalid Request"));
            return $this->resultRedirectFactory
                        ->create()
                        ->setPath('*/*/allrma');
        } else {
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__('View RMA'));
            return $resultPage;
        }
    }

    /**
     * Get Helper
     *
     * @return \Webkul\MpRmaSystem\Helper\Data
     */
    public function helper()
    {
        return $this->mpRmaHelper;
    }
}
