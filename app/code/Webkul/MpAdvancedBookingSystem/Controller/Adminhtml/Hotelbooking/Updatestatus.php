<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvancedBookingSystem
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedBookingSystem\Controller\Adminhtml\Hotelbooking;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

/**
 * Controller Updatestatus class
 */
class Updatestatus extends Action
{
    /**
     * @var Webkul\MpAdvancedBookingSystem\Model\QuestionFactory
     */
    protected $questionModel;

    /**
     * @var \Webkul\MpAdvancedBookingSystem\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * Constructor
     *
     * @param Context $context
     * @param \Webkul\MpAdvancedBookingSystem\Model\QuestionFactory $questionModel
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Data $helper
     */
    public function __construct(
        Context $context,
        \Webkul\MpAdvancedBookingSystem\Model\QuestionFactory $questionModel,
        \Webkul\MpAdvancedBookingSystem\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->redirect = $context->getRedirect();
        $this->questionModel = $questionModel;
        $this->helper = $helper;
    }

    /**
     * Execute
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $postItems = $this->getRequest()->getParams();
        $statuses = $this->helper->getQuestionStatuses();
        if (empty($postItems)) {
            $messages[] = __('Please correct the data sent.');
        } else {
            try {
                $model = $this->questionModel->create()->load((int)$postItems['id']);
                $model->setStatus($postItems['status'])->save();
                $this->messageManager->addSuccessMessage(__('Query has been %1.', $statuses[$postItems['status']]));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__($e->getMessage()));
            }
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->redirect->getRefererUrl());
        return $resultRedirect;
    }
}
