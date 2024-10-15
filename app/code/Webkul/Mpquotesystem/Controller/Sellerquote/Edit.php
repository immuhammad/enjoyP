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
use Webkul\Mpquotesystem\Model\QuotesFactory;
use Webkul\Mpquotesystem\Helper\Data;
use Magento\Framework\Controller\ResultFactory;
use Webkul\Marketplace\Helper\Data as MpHelper;

class Edit extends \Magento\Customer\Controller\AbstractAccount
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
     * @var QuotesFactory
     */
    protected $_quotesFactory;

    /**
     * @var Webkul\Mpquotesystem\Helper\Data
     */
    protected $_quoteHelper;

    /**
     * @var Webkul\Marketplace\Helper\Data
     */
    private $mpHelper;

    /**
     * @param Context                         $context
     * @param PageFactory                     $resultPageFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param QuotesFactory                   $quotesFactory
     * @param Data                            $quoteHelper
     * @param MpHelper                        $mpHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        QuotesFactory $quotesFactory,
        Data $quoteHelper,
        MpHelper $mpHelper
    ) {
        parent::__construct($context);
        $this->_customerSession = $customerSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_quotesFactory = $quotesFactory;
        $this->_quoteHelper = $quoteHelper;
        $this->mpHelper = $mpHelper;
    }

    /**
     * Seller quote edit page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->_quoteHelper->getQuoteEnabled()) {
            $wholedata = $this->getRequest()->getParams();
            $entityId = 0;
            if (array_key_exists('id', $wholedata)) {
                $entityId = $wholedata['id'];
            }
            if ($entityId) {
                $quoteModel = $this->_quotesFactory->create()->load($entityId);
                if ($quoteModel) {
                    $sellerId = $this->_quoteHelper->getSellerIdByProductId($quoteModel->getProductId());
                    $customerId = $this->_quoteHelper->getCustomerSession()->getId();
                    if ($sellerId!=$customerId) {
                        $this->messageManager->addError(__('You are not authorized to access this quote'));
                        return $this->resultRedirectFactory
                            ->create()->setPath(
                                'mpquotesystem/sellerquote/managequote',
                                ['_secure'=>$this->getRequest()->isSecure()]
                            );
                    }
                    /**
                     * @var \Magento\Framework\View\Result\Page $resultPage
                     */
                    $resultPage = $this->_resultPageFactory->create();
                    if ($this->mpHelper->getIsSeparatePanel()) {
                        $resultPage->addHandle('mpquotesystem_sellerquote_edit_layout2');
                    }
                    $resultPage->getConfig()->getTitle()->set(__('Edit Quote'));
                    return $resultPage;
                } else {
                    $this->messageManager->addError(__('Quote Does Not exists.'));
                }
            } else {
                $this->messageManager->addError(__('Quote Does Not exists.'));
            }
            return $this->resultRedirectFactory
                ->create()->setPath(
                    'mpquotesystem/sellerquote/managequote',
                    ['_secure'=>$this->getRequest()->isSecure()]
                );
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
