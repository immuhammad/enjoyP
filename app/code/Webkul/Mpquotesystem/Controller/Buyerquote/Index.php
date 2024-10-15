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

namespace Webkul\Mpquotesystem\Controller\Buyerquote;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Webkul\Mpquotesystem\Helper\Data;
use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var Webkul\Mpquotesystem\Helper\Data
     */
    protected $_quoteHelper;

    /**
     * @param Context                         $context
     * @param PageFactory                     $resultPageFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param Data                            $quoteHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        Data $quoteHelper
    ) {
        parent::__construct($context);
        $this->_customerSession = $customerSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_quoteHelper = $quoteHelper;
    }

    /**
     * Seller Manage Quote Page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->_quoteHelper->getQuoteEnabled()) {
            /**
             * @var \Magento\Framework\View\Result\Page $resultPage
             */
            $resultPage = $this->_resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__('My Quotes'));
            return $resultPage;
        } else {
            $this->messageManager->addError(__("Quotesystem is disabled by admin, Please contact to admin!"));
            return $this->resultRedirectFactory
                ->create()->setPath(
                    'customer/account/',
                    ['_secure'=>$this->getRequest()->isSecure()]
                );
        }
    }
}
