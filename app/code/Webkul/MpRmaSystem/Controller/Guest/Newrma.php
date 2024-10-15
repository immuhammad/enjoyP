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

class Newrma extends \Magento\Framework\App\Action\Action
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
     * @param PageFactory $_resultPageFactory
     * @param \Webkul\MpRmaSystem\Helper\Data $mpRmaHelper
     * @return void
     */
    public function __construct(
        Context $context,
        PageFactory $_resultPageFactory,
        \Webkul\MpRmaSystem\Helper\Data $mpRmaHelper
    ) {
        $this->resultPageFactory = $_resultPageFactory;
        $this->mpRmaHelper       = $mpRmaHelper;
        parent::__construct($context);
    }

    /**
     * Guest New Rma Action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $helper = $this->mpRmaHelper;
        if ($helper->isLoggedIn()) {
            return $this->resultRedirectFactory->create()->setPath('*/customer/allrma');
        }

        if (!$helper->isGuestLoggedIn()) {
            return $this->resultRedirectFactory->create()->setPath('*/*/login');
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('New RMA'));
        return $resultPage;
    }
}
