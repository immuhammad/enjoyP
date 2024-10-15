<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpServiceFee
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpServiceFee\Controller\Adminhtml\Fees;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;

class Create extends \Magento\Backend\App\AbstractAction
{
    
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var \Webkul\MpServiceFee\Helper\Servicehelper
     */
    protected $serviceHelper;

    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'Webkul_MpServiceFee::createfees';

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Webkul\MpServiceFee\Helper\Servicehelper $serviceHelper
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Webkul\MpServiceFee\Helper\Servicehelper $serviceHelper,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->serviceHelper = $serviceHelper;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $isEnabled = $this->serviceHelper->isModuleEnable();
        if (!$isEnabled) {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->getUrl('noroute'));
            return $resultRedirect;
        }
        
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Webkul_MpServiceFee::servicefee');
        $resultPage->getConfig()->getTitle()->prepend(__('Create New Service Fees'));
        return $resultPage;
    }
}
