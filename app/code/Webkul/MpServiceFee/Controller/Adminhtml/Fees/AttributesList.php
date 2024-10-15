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

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;

class AttributesList extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'Webkul_MpServiceFee::createfees';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     **/
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Controller\Result\Forward
     */
    protected $resultForward;

    /**
     * Class constructor
     *
     * @param Context $context
     * @param \Webkul\MpServiceFee\Helper\Servicehelper $serviceHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Webkul\MpServiceFee\Helper\Servicehelper $serviceHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->serviceHelper = $serviceHelper;
        $this->_resultPageFactory = $resultPageFactory;
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

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Webkul_MpServiceFee::createfees');
        $resultPage->getConfig()->getTitle()->prepend(__("Service Fees List"));
        return $resultPage;
    }
}
