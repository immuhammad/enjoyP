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
namespace Webkul\MpRmaSystem\Controller\Adminhtml\Rma;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    /**
     * Using for Rma admin resource
     */
    public const ADMIN_RESOURCE = 'Webkul_MpRmaSystem::rma';

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Initialize Dependencies
     *
     * @param Context $context
     * @param PageFactory $_resultPageFactory
     * @return void
     */
    public function __construct(
        Context $context,
        PageFactory $_resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $_resultPageFactory;
    }

    /**
     * All Rma Action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $_resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Webkul_Marketplace::mprmasystem');
        $resultPage->getConfig()->getTitle()->prepend(__('All RMA'));
        return $resultPage;
    }
}
