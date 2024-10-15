<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpquotesystem
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpquotesystem\Controller\Adminhtml\Managequotes;

use Magento\Backend\App\Action;
use Webkul\Mpquotesystem;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var Webkul\Mpquotesystem\Helper\Data
     */
    protected $_mpQuoteHelper;

    /**
     * @param Action\Context            $context
     * @param Mpquotesystem\Helper\Data $mpQuoteHelper
     */
    public function __construct(
        Action\Context $context,
        Mpquotesystem\Helper\Data $mpQuoteHelper
    ) {
        parent::__construct($context);
        $this->_mpQuoteHelper = $mpQuoteHelper;
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            'Webkul_Mpquotesystem::mpquotes'
        );
    }
    
    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        /**
         * @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect
         */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $model = $this->_mpQuoteHelper->getWkQuoteModel();
            $id = $this->getRequest()->getParam('entity_id');
            if ($id) {
                $model->load($id);
            }
            try {
                $model->delete();
                $this->messageManager->addSuccess(__('Quote is Successfully deleted'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError(__($e->getMessage()));
            } catch (\RuntimeException $e) {
                $this->messageManager->addError(__($e->getMessage()));
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __('Something went wrong while Deleting the data.')
                );
            }
            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/');
        }
        return $resultRedirect->setPath('*/*/');
    }
}
