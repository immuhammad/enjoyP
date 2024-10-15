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
namespace Webkul\MpRmaSystem\Controller\Adminhtml\Reasons;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    /**
     * Using for Rma admin resource
     */
    public const ADMIN_RESOURCE = 'Webkul_MpRmaSystem::reasons';
    
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\Page
     */
    protected $resultPage;

    /**
     * Initialize Dependencies
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param PageFactory $resultPageFactory
     * @return void
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Using for Reasons Action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $_resultPage */
        $_resultPage = $this->resultPageFactory->create();
        $_resultPage->setActiveMenu('Webkul_Marketplace::mprmasystem');
        $_resultPage->getConfig()->getTitle()->prepend(__('Reasons'));
        return $_resultPage;
    }
}
