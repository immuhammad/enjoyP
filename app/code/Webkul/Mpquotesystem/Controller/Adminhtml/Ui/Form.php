<?php
/**
 * Quote Edit controller, admin panel.
 *
 * @category  Webkul
 * @package   Webkul_Mpquotesystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpquotesystem\Controller\Adminhtml\ui;

use Magento\Backend\App\Action;

class Form extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @param Action\Context                                $context
     * @param \Magento\Framework\View\Result\PageFactory    $resultPageFactory
     * @param \Magento\Backend\Model\Session                $backendSession
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\Session $backendSession
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->backendSession = $backendSession;
        parent::__construct($context);
    }

    /**
     * Ui Form execution
     */
    public function execute()
    {
        $params = $this->_request->getParams();
        if (isset($params['sort']) || isset($params['filter'])) {
            $this->backendSession->setIsSort(true);
        } else {
            $this->backendSession->setIsSort(false);
        }
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Add Quote'));
        return $resultPage;
    }
    
     /**
      * Check for is allowed
      *
      * @return boolean
      */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Mpquotesystem::mpquotes');
    }
}
