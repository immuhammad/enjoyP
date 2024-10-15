<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpmembership
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpmembership\Controller\Adminhtml\Producttransaction;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
     /**
      * @var \Magento\Framework\View\Result\PageFactory
      */
    protected $_resultPageFactory;

    /**
     * @var \Webkul\Mpmembership\Helper\Data
     */
    protected $helper;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Webkul\Mpmembership\Helper\Data $helper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Webkul\Mpmembership\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->helper     = $helper;
    }

    /**
     * Mpmembership Producttransaction list page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        try {
            if ($this->helper->isModuleEnabled()) {
                /**
                 * @var \Magento\Backend\Model\View\Result\Page $resultPage
                */
                $resultPage = $this->_resultPageFactory->create();
                $resultPage->setActiveMenu('Webkul_Mpmembership::producttransaction');
                $resultPage->getConfig()->getTitle()->prepend(__("View Product Transactions"));
                return $resultPage;
            } else {
                $this->_redirect('404');
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Controller_Adminhtml_Producttransaction_Index_execute Exception : ".$e->getMessage()
            );
        }
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Mpmembership::producttransaction');
    }
}
