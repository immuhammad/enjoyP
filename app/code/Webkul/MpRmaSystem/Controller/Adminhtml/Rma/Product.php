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

class Product extends \Webkul\MpRmaSystem\Controller\Adminhtml\Rma
{
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var \Webkul\MpRmaSystem\Model\DetailsFactory
     */
    protected $details;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Initialize Dependencies
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Webkul\MpRmaSystem\Model\DetailsFactory $details
     * @param \Magento\Framework\Registry $coreRegistry
     * @return void
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Webkul\MpRmaSystem\Model\DetailsFactory $details,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->backendSession      = $context->getSession();
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->details             = $details;
        $this->coreRegistry        = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Rma Product Action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $rmaModel = $this->details->create();
        if ($this->getRequest()->getParam('id')) {
            $rmaModel->load($this->getRequest()->getParam('id'));
        }

        $data = $this->backendSession->getFormData(true);
        if (!empty($data)) {
            $rmaModel->setData($data);
        }

        $this->coreRegistry->register('mprmasystem_rma', $rmaModel);
        $resultLayout = $this->resultLayoutFactory->create();
        $resultLayout->getLayout()->getBlock('mprmasystem.rma.view.tab.product');
        return $resultLayout;
    }
}
