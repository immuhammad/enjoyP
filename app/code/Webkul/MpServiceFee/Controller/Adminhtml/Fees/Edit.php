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

use Magento\Framework\Controller\ResultFactory;

class Edit extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Webkul\MpServiceFee\Helper\Servicehelper
     */
    protected $serviceHelper;

    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'Webkul_MpServiceFee::createfees';

    /**
     * Class constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Webkul\MpServiceFee\Helper\Servicehelper $serviceHelper
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Webkul\MpServiceFee\Helper\Servicehelper $serviceHelper,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->serviceHelper = $serviceHelper;
        parent::__construct($context);
    }

    /**
     * Execute
     *
     * @return void
     */
    public function execute()
    {
        $isEnabled = $this->serviceHelper->isModuleEnable();
        if (!$isEnabled) {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->getUrl('noroute'));
            return $resultRedirect;
        }
        
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Webkul_MpServiceFee::servicefee');
        $resultPage->getConfig()->getTitle()->prepend(__('Edit Service Fees'));
        return $resultPage;
    }
}
