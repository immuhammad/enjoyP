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

class Save extends Action
{
    /**
     * Using for Rma admin resource
     */
    public const ADMIN_RESOURCE = 'Webkul_MpRmaSystem::reasons';
    
    /**
     * @var \Webkul\MpRmaSystem\Model\ReasonsFactory
     */
    protected $reasons;

    /**
     * Initialize Dependencies
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Webkul\MpRmaSystem\Model\ReasonsFactory $reasons
     * @param \Webkul\MpRmaSystem\Model\ReasonsRepository $reasonsRepository
     * @return void
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Webkul\MpRmaSystem\Model\ReasonsFactory $reasons,
        \Webkul\MpRmaSystem\Model\ReasonsRepository $reasonsRepository
    ) {
        $this->reasons = $reasons;
        $this->reasonsRepository = $reasonsRepository;
        parent::__construct($context);
    }

    /**
     * Save action.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getParams();
            $id = (int) $this->getRequest()->getParam('id');
            $resultRedirect = $this->resultRedirectFactory->create();
            if ($id) {
                $model = $this->reasonsRepository->getById($id);
                $model->setData($data);
                $this->reasonsRepository->save($model);
                $this->messageManager->addSuccess(__('Reason edited successfully.'));
            } else {
                $model = $this->reasons->create();
                $model->addData($data);
                $this->reasonsRepository->save($model);
                $this->messageManager->addSuccess(__('Reason saved successfully.'));
            }
        } else {
            $error = 'There was some error while processing your request.';
            $this->messageManager->addError(__($error));
            return $resultRedirect->setPath('*/*/');
        }

        return $resultRedirect->setPath('*/*/');
    }
}
