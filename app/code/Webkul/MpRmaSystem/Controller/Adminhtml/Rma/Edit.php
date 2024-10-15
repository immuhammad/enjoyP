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

use Magento\Framework\Controller\ResultFactory;

class Edit extends \Webkul\MpRmaSystem\Controller\Adminhtml\Rma
{
    /**
     * Using for Rma admin resource
     */
    public const ADMIN_RESOURCE = 'Webkul_MpRmaSystem::rma';

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

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
     * @param \Webkul\MpRmaSystem\Model\DetailsFactory $details
     * @param \Magento\Framework\Registry $coreRegistry
     * @return void
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Webkul\MpRmaSystem\Model\DetailsFactory $details,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->backendSession = $context->getSession();
        $this->details        = $details;
        $this->coreRegistry   = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Edit Action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        try {
            $id = $this->getRequest()->getParam('id');
            $detailsModel = $this->details->create();
            if ($this->getRequest()->getParam('id')) {
                $detailsModel->load($this->getRequest()->getParam('id'));
            }

            $data = $this->backendSession->getFormData(true);
            if (!empty($data)) {
                $detailsModel->setData($data);
            }

            $this->coreRegistry->register('mprmasystem_rma', $detailsModel);
            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            $resultPage->getConfig()->getTitle()->prepend(__('Details'));
            return $resultPage;
        } catch (\Exception $e) {
            $this->messageManager->addError(__('The requested RMA no longer exist.'));
            return $this->resultRedirectFactory->create()->setPath(
                'mprmasystem/rma/index',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
