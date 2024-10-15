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

namespace Webkul\Mpquotesystem\Controller\Sellerquote;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Webkul\Mpquotesystem\Helper\Data;
use Magento\Framework\Controller\ResultFactory;
use Webkul\Marketplace\Helper\Data as MpHelper;

class Managequote extends \Magento\Customer\Controller\AbstractAccount
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
     * @var Webkul\Marketplace\Helper\Data
     */
    private $mpHelper;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param Data $quoteHelper
     * @param MpHelper $mpHelper
     * @param \Webkul\Mpquotesystem\Model\QuotesFactory $mpquotes
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        Data $quoteHelper,
        MpHelper $mpHelper,
        \Webkul\Mpquotesystem\Model\QuotesFactory $mpquotes
    ) {
        parent::__construct($context);
        $this->_customerSession = $customerSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_quoteHelper = $quoteHelper;
        $this->mpHelper = $mpHelper;
        $this->_mpquote = $mpquotes;
    }

    /**
     * Seller Manage Quote Page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->_quoteHelper->getQuoteEnabled()) {
            $quoteCollection = $this->_mpquote->create()
            ->getCollection()
            ->addFieldToFilter('seller_pending_notification', ['eq' => 1]);

            if ($quoteCollection->getSize()) {
                $this->updateNotificationCollection($quoteCollection);
            }
            /**
             * @var \Magento\Framework\View\Result\Page $resultPage
             */
            $resultPage = $this->_resultPageFactory->create();
            if ($this->mpHelper->getIsSeparatePanel()) {
                $resultPage->addHandle('mpquotesystem_sellerquote_managequote_layout2');
            }
            $resultPage->getConfig()->getTitle()->set(__('Manage Quotes'));
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

    /**
     * Get parameters
     *
     * @param object $collection
     *
     * @return void
     */
    public function updateNotificationCollection($collection)
    {
        foreach ($collection as $modelData) {
            $isNotification = $modelData->getSellerPendingNotification();
            if ($isNotification) {
                $modelData->setSellerPendingNotification(0);
                $modelData->save();
            }
        }
    }
}
