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
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Webkul\Mpquotesystem\Model\QuotesFactory;
use Webkul\Mpquotesystem\Api\QuoteRepositoryInterface;
use Webkul\Mpquotesystem\Helper\Data;
use Magento\Framework\Controller\ResultFactory;

class Delete extends \Magento\Customer\Controller\AbstractAccount
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
     * @var QuoteRepositoryInterface
     */
    protected $_quoteRepository;

    /**
     * @var Webkul\Mpquotesystem\Helper\Data
     */
    protected $_quoteHelper;

    /**
     * @param Context                  $context
     * @param Session                  $customerSession
     * @param PageFactory              $resultPageFactory
     * @param QuotesFactory            $quotesFactory
     * @param QuoteRepositoryInterface $quoteRepository
     * @param Data                     $quoteHelper
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        QuotesFactory $quotesFactory,
        QuoteRepositoryInterface $quoteRepository,
        Data $quoteHelper
    ) {
        parent::__construct(
            $context
        );
        $this->_customerSession = $customerSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_quotesFactory = $quotesFactory;
        $this->_quoteRepository = $quoteRepository;
        $this->_quoteHelper = $quoteHelper;
    }

    /**
     * Retrieve customer session object
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->_customerSession;
    }

    /**
     * Delete quote from model.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->_quoteHelper->getQuoteEnabled()) {
            try {
                $wholedata = $this->getRequest()->getParams();
                $entityId = 0;
                if (array_key_exists('id', $wholedata)) {
                    $entityId = $wholedata['id'];
                }
                if ($entityId) {
                    $quoteModel = $this->_quoteRepository->getById($entityId);
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
                    $this->_quoteRepository->deleteById($entityId);
                    $this->messageManager->addSuccess(__('Quotes are successfully deleted.'));
                } else {
                    $this->messageManager->addError(__('Quote Does Not exists.'));
                }
                return $this->resultRedirectFactory
                    ->create()->setPath(
                        'mpquotesystem/sellerquote/managequote',
                        ['_secure'=>$this->getRequest()->isSecure()]
                    );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $this->resultRedirectFactory
                    ->create()->setPath(
                        'mpquotesystem/sellerquote/managequote',
                        ['_secure'=>$this->getRequest()->isSecure()]
                    );
            }
        } else {
            $this->messageManager->addError(__("Quotesystem is disabled by admin, Please contact to admin!"));
            return $this->resultRedirectFactory
                ->create()->setPath(
                    'customer/account',
                    ['_secure'=>$this->getRequest()->isSecure()]
                );
        }
    }
}
