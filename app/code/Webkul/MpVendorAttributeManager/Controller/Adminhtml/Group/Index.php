<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpVendorAttributeManager
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpVendorAttributeManager\Controller\Adminhtml\Group;

class Index extends \Magento\Backend\App\Action
{
    /**
     * Result Page
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Function _isAllowed
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpVendorAttributeManager::group');
    }

    /**
     * Execute action
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        
        $resultPage->setActiveMenu('Webkul_Marketplace::menu');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Group'));
        $resultPage->addBreadcrumb(__('Manage Vendor Group'), __('Manage Vendor Group'));
        
        return $resultPage;
    }
}
