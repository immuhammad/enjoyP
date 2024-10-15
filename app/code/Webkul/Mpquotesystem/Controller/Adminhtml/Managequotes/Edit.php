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
use Webkul\Mpquotesystem\Model\QuotesFactory;

class Edit extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var Webkul\Mpquotesystem\Model\QuotesFactory
     */
    protected $_quotesFactory;

    /**
     * @param Action\Context                             $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry                $registry
     * @param \Magento\Backend\Model\Session             $backendSession
     * @param QuotesFactory                              $quotesFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\Session $backendSession,
        QuotesFactory $quotesFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->_backendSession = $backendSession;
        $this->_quotesFactory = $quotesFactory;
        parent::__construct($context);
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
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Webkul_Mpquotesystem::mpquotes')
            ->addBreadcrumb(__('Manage Marketplace Quotes'), __('Manage Marketplace Quotes'));
        return $resultPage;
    }
    
    /**
     * Edit action
     *
     * @return void
     */
    public function execute()
    {
        $id = 0;
        $wholedata = $this->getRequest()->getParams();
        if (array_key_exists('entity_id', $wholedata)) {
            $id = $wholedata['entity_id'];
        }
        $model = $this->_quotesFactory->create();
        if ($id) {
            $model->load($id);
            if (!$model->getEntityId()) {
                $this->messageManager->addError(
                    __('This quote no longer exists.')
                );
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $data = $this->_backendSession->getFormData(true);
        if (!empty($data) || $id) {
            if (!empty($data)) {
                $model->setData($data);
            }
            $this->_coreRegistry->register('quote_data', $model);
            $resultPage = $this->_initAction();
            $quoteEditEnableStatus = [
                \Webkul\Mpquotesystem\Model\Quotes::STATUS_UNAPPROVED,
                \Webkul\Mpquotesystem\Model\Quotes::STATUS_APPROVED,
                \Webkul\Mpquotesystem\Model\Quotes::STATUS_DECLINE
            ];
            if (in_array($model->getStatus(), $quoteEditEnableStatus)) {
                $resultPage->addBreadcrumb(__('Edit Quote'), __('Edit Quote'));
                $resultPage->getConfig()->getTitle()->prepend(__('Edit Marketplace Quote'));
            } else {
                $resultPage->addBreadcrumb(__('View Quote'), __('View Quote'));
                $resultPage->getConfig()->getTitle()->prepend(__('View Marketplace Quote'));
            }
            $resultPage->addContent(
                $resultPage->getLayout()->createBlock(
                    \Webkul\Mpquotesystem\Block\Adminhtml\Managequotes\Edit::class
                )
            );
            $resultPage->addLeft(
                $resultPage->getLayout()->createBlock(
                    \Webkul\Mpquotesystem\Block\Adminhtml\Managequotes\Edit\Tabs::class
                )
            );
            return $resultPage;
        } else {
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('mpquotesystem/managequotes/index');
        }
    }
}
