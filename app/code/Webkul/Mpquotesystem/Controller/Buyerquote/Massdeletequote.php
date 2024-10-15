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
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Webkul\Mpquotesystem\Model\QuotesFactory;
use Webkul\Mpquotesystem\Api\QuoteRepositoryInterface;
use Webkul\Mpquotesystem\Helper\Data;
use Magento\Framework\Controller\ResultFactory;

class Massdeletequote extends Action
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
     * Undocumented function
     *
     * @param Context                     $context
     * @param Session                     $customerSession
     * @param PageFactory                 $resultPageFactory
     * @param \Magento\Customer\Model\Url $urlModel
     * @param QuotesFactory               $quotesFactory
     * @param QuoteRepositoryInterface    $quoteRepository
     * @param Data                        $quoteHelper
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Url $urlModel,
        QuotesFactory $quotesFactory,
        QuoteRepositoryInterface $quoteRepository,
        Data $quoteHelper
    ) {
        parent::__construct(
            $context
        );
        $this->_customerSession = $customerSession;
        $this->_urlModel = $urlModel;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_quotesFactory = $quotesFactory;
        $this->_quoteRepository = $quoteRepository;
        $this->_quoteHelper = $quoteHelper;
    }

    /**
     * Check customer authentication
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_urlModel->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
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
     * Delete mass quotes from model.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->_quoteHelper->getQuoteEnabled()) {
            try {
                $wholedata = $this->getRequest()->getParams();
                $ids = [];
                if (array_key_exists('quote_mass_delete', $wholedata)) {
                    $ids = $wholedata['quote_mass_delete'];
                }
                if (!empty($ids)) {
                    foreach ($ids as $entityId) {
                        $quoteModel = $this->_quoteRepository->getById($entityId);
                        $quoteCustomerId = $quoteModel->getCustomerId();
                        $customerId = $this->_quoteHelper->getCustomerSession()->getId();
                        if ($quoteCustomerId!=$customerId) {
                            $this->messageManager->addError(__('Unauthorized to access quote id %1', $entityId));
                            continue;
                        }
                        $this->_quoteRepository->deleteById($entityId);
                    }
                    $this->messageManager->addSuccess(__('Quotes are successfully deleted.'));
                } else {
                    $this->messageManager->addError(__('Please select checkbox first.'));
                }
                return $this->resultRedirectFactory
                    ->create()->setPath(
                        '*/*/index',
                        ['_secure'=>$this->getRequest()->isSecure()]
                    );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $this->resultRedirectFactory
                    ->create()->setPath(
                        '*/*/index',
                        ['_secure'=>$this->getRequest()->isSecure()]
                    );
            }
        } else {
            $this->messageManager->addError(__("Quotesystem is disabled by admin, Please contact to admin!"));
            return $this->resultRedirectFactory
                ->create()->setPath(
                    'customer/ac!empty',
                    ['_secure'=>$this->getRequest()->isSecure()]
                );
        }
    }
}
