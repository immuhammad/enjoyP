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
namespace Webkul\MpVendorAttributeManager\Controller\Adminhtml\Attribute;

class Index extends \Magento\Backend\App\Action
{
    /**
     * Page Factory
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
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
     *
     * @return void
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpVendorAttributeManager::index');
    }

    /**
     * Execute action
     *
     * @return void
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        
        $resultPage->setActiveMenu('Webkul_Marketplace::menu');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Attribute'));
        $resultPage->addBreadcrumb(__('Manage Vendor Attribute'), __('Manage Vendor Attribute'));
        
        return $resultPage;
    }
}
