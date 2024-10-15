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
use Webkul\Mpquotesystem\Model\QuoteconversationFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Controller\ResultFactory;

class Updatequote extends \Magento\Customer\Controller\AbstractAccount
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
     * @var QuoteconversationFactory
     */
    protected $_quoteconversationFactory;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @var mpquoteHelper
     */
    protected $_mpquoteHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;
    
    /**
     * @param Context                                     $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param Session                                     $customerSession
     * @param PageFactory                                 $resultPageFactory
     * @param QuotesFactory                               $quotesFactory
     * @param QuoteconversationFactory                    $quoteConversationFactory
     * @param ProductFactory                              $productFactory
     * @param \Webkul\Mpquotesystem\Helper\Data           $mpquoteHelper
     * @param \Webkul\Mpquotesystem\Helper\Mail           $mpquoteMailHelper
     * @param \Webkul\Marketplace\Helper\Data             $mpHelper
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        Session $customerSession,
        PageFactory $resultPageFactory,
        QuotesFactory $quotesFactory,
        QuoteconversationFactory $quoteConversationFactory,
        ProductFactory $productFactory,
        \Webkul\Mpquotesystem\Helper\Data $mpquoteHelper,
        \Webkul\Mpquotesystem\Helper\Mail $mpquoteMailHelper,
        \Webkul\Marketplace\Helper\Data $mpHelper
    ) {
        $this->_customerSession = $customerSession;
        $this->_date = $date;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_quotesFactory = $quotesFactory;
        $this->_quoteconversationFactory = $quoteConversationFactory;
        $this->_productFactory = $productFactory;
        $this->_mpquoteHelper = $mpquoteHelper;
        $this->_mpquoteMailHelper = $mpquoteMailHelper;
        $this->mpHelper = $mpHelper;
        parent::__construct(
            $context
        );
    }

    /**
     * Retrieve customer session object.
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->_customerSession;
    }

    /**
     * Update quote
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if (!$this->_mpquoteHelper->getQuoteEnabled()) {
            $this->messageManager->addError(__("Quotesystem is disabled by admin, Please contact to admin!"));
            return $this->resultRedirectFactory
                ->create()->setPath(
                    'customer/account',
                    ['_secure'=>$this->getRequest()->isSecure()]
                );
        }
        try {
            $wholedata = $this->getRequest()->getParams();
            $wholedata['quote_price'] = preg_replace('/[^0-9,.]/', '', $wholedata['quote_price']);
            $quoteId = 0;
            if (array_key_exists('quote_id', $wholedata)) {
                $quoteId = $wholedata['quote_id'];
            } else {
                $this->messageManager->addError(
                    __('This quote no longer exists')
                );
                return $this->resultRedirectFactory
                    ->create()->setPath(
                        'mpquotesystem/sellerquote/managequote/',
                        [
                            '_secure' => $this->getRequest()->isSecure(),
                        ]
                    );
            }
            $quote = $this->_quotesFactory->create()
                ->load($quoteId);
            if ($quote->getEntityId() && $quote->getSellerId() != $this->_getSession()->getCustomerId()) {
                $this->messageManager->addError(
                    __('Quote not found')
                );
                $quoteId = 0;
            } elseif ($quote->getEntityId()) {
                $saveFileName = $this->_mpquoteHelper->saveAttachment();
                $wholedata['attachment'] = $saveFileName ? $saveFileName :
                                            $quote->getAttachment();
                $this->checkAndUpdateData($wholedata, $quote);
                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/managequote',
                    ['_secure'=>$this->getRequest()->isSecure()]
                );
            } else {
                $this->messageManager->addError(
                    __('This quote no longer exists')
                );
                $quoteId = 0;
            }
        } catch (\Exception $e) {
            $this->messageManager->addError("Quote cannot be updated in approved status.");
        }

        return $this->resultRedirectFactory
            ->create()->setPath(
                'mpquotesystem/sellerquote/managequote/',
                [
                '_secure' => $this->getRequest()->isSecure(),
                ]
            );
    }
    
    /**
     * Get Product Qty
     *
     * @param array $wholedata
     * @param object $quote
     * @param object $product
     *
     * @return $productQty
     */
    protected function getProductQty($wholedata, $quote, $product)
    {
        if ($product->getTypeId() == "bundle") {
            $bundleOption = $this->_mpquoteHelper->convertStringAccToVersion($quote->getBundleOption(), 'decode');
            $validateQty = $this->_mpquoteHelper->validateBundleProductQuantity(
                $product,
                $bundleOption,
                $quote,
                $wholedata
            );
            $productQty = 0;
            if ($validateQty) {
                $productQty = $this->_mpquoteHelper->getBundleProductQuatity(
                    $product,
                    $bundleOption
                );
            }
        } elseif ($product->getTypeId()=='configurable') {
            $productQty = $this->_mpquoteHelper->getConfigurableProductQuantity(
                $product,
                $quote
            );
        } else {
            $productQty = $product->getQuantityAndStockStatus()['qty'];
        }
        return round($productQty);
    }
    
    /**
     * Get Final Product Price
     *
     * @param object $quote
     * @param object $product
     *
     * @return void
     */
    public function getFinalProductPrice($quote, $product)
    {
        $params = [];
        if ($product->getTypeId()=='bundle') {
            $bundleOption = $this->_mpquoteHelper
                ->convertStringAccToVersion($quote->getBundleOption(), 'decode');
            $params['bundle_option_to_calculate'] = $bundleOption;
        }
        $params['options'] = $this->_mpquoteHelper
            ->convertStringAccToVersion($quote->getProductOption(), 'decode');
        $params['super_attribute'] = $this->_mpquoteHelper
            ->convertStringAccToVersion($quote->getSuperAttribute(), 'decode');
        $params['links'] = $this->_mpquoteHelper
            ->convertStringAccToVersion($quote->getLinks(), 'decode');
        $params['product'] = $product->getEntityId();
        $productPrice = $this->_mpquoteHelper->calculateProductPrice(
            $params
        );
        return($productPrice);
    }
    
    /**
     * CheckAndUpdateData
     *
     * @param array  $data
     * @param object $quote
     * @return void
     */
    public function checkAndUpdateData($data, $quote)
    {
        if ($data["status"] == \Webkul\Mpquotesystem\Model\Quotes::STATUS_SOLD
            || $quote->getStatus()== \Webkul\Mpquotesystem\Model\Quotes::STATUS_SOLD
        ) {
            $this->messageManager->addError(
                __(
                    "Not Allowed to update."
                )
            );
            return false;
        }
        $product = $this->_mpquoteHelper->getProduct($quote->getProductId());
        $productQty = $this->getProductQty($data, $quote, $product);
        if ($product->getMinQuoteQty() > $data['quote_qty']) {
            $this->messageManager->addError(
                __(
                    'Sorry!! Quote Quantity should not be less than %1.',
                    $product->getMinQuoteQty()
                )
            );
            return false;
        }
        if ($data['status'] == \Webkul\Mpquotesystem\Model\Quotes::STATUS_APPROVED && isset($data['quote_qty'])
            && $productQty < $data['quote_qty']
        ) {
            $this->messageManager->addError(
                __(
                    "Sorry!! This much product quantity is not available"
                )
            );
            return false;
        }
        
        if (array_key_exists('quote_message', $data)) {
            $this->updateQuoteData($data, $quote, $product);
        }
    }
    
    /**
     * UpdateQuoteData
     *
     * @param array  $data
     * @param object $quote
     * @param object $product
     * @return void
     */
    public function updateQuoteData($data, $quote, $product)
    {
        $statusFlag = 0;
        $qtyStatus = 0;
        $priceStatus = 0;
        $quoteConversation = $this->_quoteconversationFactory->create();
        $sellerId = $this->mpHelper->getCustomerId();
        $quoteConversation->setQuoteId($data['quote_id'])
            ->setConversation($data['quote_message'])
            ->setSender($sellerId)
            ->setReceiver($quote->getCustomerId())
            ->setCreatedAt($this->_date->gmtDate())
            ->save();
        $approvedStatus = \Webkul\Mpquotesystem\Model\Quotes::STATUS_APPROVED;
        $soldStatus = \Webkul\Mpquotesystem\Model\Quotes::STATUS_SOLD;
        if ($quote->getStatus() != $soldStatus) {
            $this->performSoldOperations($quote, $data);
            $this->messageManager->addSuccess(
                __('Message Sent Successfully')
            );
        } else {
            if ($quote->getStatus() != $data['status']) {
                if ($quote->getStatus()==\Webkul\Mpquotesystem\Model\Quotes::STATUS_SOLD) {
                    $this->messageManager->addError(
                        __('Quote Already has been sold')
                    );
                }
            } else {
                $this->messageManager->addSuccess(
                    __('Message Sent Successfully')
                );
            }
        }
    }

    /**
     * PerformSoldOperations
     *
     * @param object $quote
     * @param array  $data
     * @return void
     */
    public function performSoldOperations($quote, $data)
    {
        $qtyStatus = 0;
        $priceStatus = 0;
        $statusFlag = 0;
        if ($quote->getQuoteQty() != $data['quote_qty']) {
            $quote->setQuoteQty($data['quote_qty']);
            $qtyStatus = 1;
        }
        if ($quote->getQuotePrice() != $data['quote_price']) {
            $priceStatus = 1;
            $quote->setQuotePrice($data['quote_price']);
        }
        if ($quote->getAttachment() != $data['attachment']) {
            $priceStatus = 1;
            $quote->setAttachment($data['attachment']);
        }
        if ($quote->getStatus() != $data['status']) {
            $quote->setStatus($data['status'])
                ->save();
            $statusFlag = 1;
            if ($qtyStatus == 1 || $priceStatus == 1) {
                $this->_mpquoteMailHelper->quoteEditedByAdmin(
                    $data['quote_id'],
                    $data['quote_message'],
                    'seller'
                );
            } else {
                $this->_mpquoteMailHelper->quoteStatusMail(
                    $data['quote_id'],
                    $data['quote_message'],
                    'seller'
                );
            }
        } else {
            if ($qtyStatus != 1 && $priceStatus !=1) {
                $this->_mpquoteMailHelper->quoteMessage(
                    $data['quote_id'],
                    $data['quote_message'],
                    'seller'
                );
            }
        }
        if ($statusFlag == 0 && ($qtyStatus == 1 || $priceStatus == 1)) {
            $quote->save();
            $this->_mpquoteMailHelper->quoteEditedByAdmin(
                $data['quote_id'],
                $data['quote_message'],
                'seller'
            );
        }
    }
}
