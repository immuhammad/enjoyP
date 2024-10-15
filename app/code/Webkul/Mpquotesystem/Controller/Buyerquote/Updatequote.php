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
use Webkul\Mpquotesystem\Model\QuotesFactory;
use Webkul\Mpquotesystem\Helper;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory;
use Webkul\Mpquotesystem\Model\QuoteconversationFactory;
use Magento\Framework\Controller\ResultFactory;

class Updatequote extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_catalogProduct;

    /**
     * @var \Webkul\Mpquotesystem\Model\Quotes
     */
    protected $_mpquote;

    /**
     * @var Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_mpproductCollection;

    /**
     * @var \Webkul\Mpquotesystem\Helper\Mail
     */
    protected $_mailHelper;

    /**
     * @var \Webkul\Mpquotesystem\Helper\Data
     */
    protected $_helperData;

    /**
     * @var QuotesFactory
     */
    protected $_quotesFactory;

    /**
     * @var QuoteconversationFactory
     */
    protected $_quoteconversationFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @param Context                                     $context
     * @param \Magento\Catalog\Model\ProductFactory       $catalogProduct
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Customer\Model\Session             $customerSession
     * @param \Webkul\Mpquotesystem\Model\QuotesFactory   $mpquotes
     * @param QuoteconversationFactory                    $quoteConversationFactory
     * @param Helper\Mail                                 $helperMail
     * @param CollectionFactory                           $mpproductCollection
     * @param Helper\Data                                 $helperData
     */
    public function __construct(
        Context $context,
        \Magento\Catalog\Model\ProductFactory $catalogProduct,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\Mpquotesystem\Model\QuotesFactory $mpquotes,
        QuoteconversationFactory $quoteConversationFactory,
        Helper\Mail $helperMail,
        CollectionFactory $mpproductCollection,
        Helper\Data $helperData
    ) {
        parent::__construct($context);
        $this->_customerSession = $customerSession;
        $this->_catalogProduct = $catalogProduct;
        $this->_date = $date;
        $this->_mpquote = $mpquotes;
        $this->_mailHelper = $helperMail;
        $this->_mpproductCollection = $mpproductCollection;
        $this->_quoteconversationFactory = $quoteConversationFactory;
        $this->_helperData = $helperData;
    }

    /**
     * Save quote from buyer.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if (!$this->_helperData->getQuoteEnabled()) {
            $this->messageManager->addError(__("Quotesystem is disabled by admin, Please contact to admin!"));
            return $this->resultRedirectFactory
                ->create()->setPath(
                    'customer/account',
                    ['_secure'=>$this->getRequest()->isSecure()]
                );
        }
        $wholedata = $this->getRequest()->getParams();
        $quoteSuccess = 0;
        $messageSuccess = 0;
        $quoteId = 0;
        if (array_key_exists('quote_id', $wholedata)) {
            $quoteId = $wholedata['quote_id'];
        } else {
            $this->messageManager->addError(__('Something Went Wrong, Please try again later.'));
            return $this->resultRedirectFactory
                ->create()->setPath(
                    'mpquotesystem/buyerquote/index/',
                    [
                    '_secure' => $this->getRequest()->isSecure(),
                    ]
                );
        }
        $quote = $this->_mpquote->create()->load($wholedata['quote_id']);

        $collectionProduct = $this->_mpproductCollection->create()
            ->addFieldToFilter(
                'mageproduct_id',
                ['eq' => $quote->getProductId()]
            );
        $sellerId = 0;
        foreach ($collectionProduct as $value) {
            $sellerId = $value->getSellerId();
        }
        if ($quote->getEntityId() && $quote->getCustomerId() != $this->_customerSession->getCustomerId()) {
            $this->messageManager->addError(
                __('Quote not found')
            );
            return $this->resultRedirectFactory
                ->create()->setPath(
                    'mpquotesystem/buyerquote/index/',
                    [
                    '_secure' => $this->getRequest()->isSecure(),
                    ]
                );
        } elseif ($wholedata) {
            if (isset($wholedata['quote_update_switch'])) {
                if (isset($wholedata['quote_price']) && isset($wholedata['quote_qty'])) {
                    $saveFileName = $this->_helperData->saveAttachment();
                    $saveFileName = $saveFileName ? $saveFileName :
                                        $quote->getAttachment();
                    if ($quote->getStatus() == \Webkul\Mpquotesystem\Model\Quotes::STATUS_UNAPPROVED) {
                        $product = $this->_helperData->getProduct($quote->getProductId());
                        if ($product->getMinQuoteQty() <= $wholedata['quote_qty']) {
                            $quote->setQuotePrice($wholedata['quote_price'])
                                ->setQuoteQty($wholedata['quote_qty'])
                                ->setAttachment($saveFileName)
                                ->save();
                            $quoteSuccess = 1;
                            $this->messageManager->addSuccess(__('Quote Successfully updated'));
                        } else {
                            $this->messageManager->addError(__('Quote Quantity can not less then default value.'));
                        }
                    } else {
                        $this->messageManager->addError(__('Sorry!! Quote Status Changed previously'));
                    }
                }
            }
            if (isset($wholedata['quote_message'])) {
                $customerId = $this->_customerSession->getCustomerId();
                $quoteConversation = $this->_quoteconversationFactory->create()
                    ->setSender($customerId)
                    ->setReceiver($sellerId)
                    ->setConversation($wholedata['quote_message'])
                    ->setQuoteId($wholedata['quote_id'])
                    ->setCreatedAt($this->_date->gmtDate())
                    ->save();
                $messageSuccess = 1;
                $this->messageManager->addSuccess(__('Message Sent Successfully'));
            }
            $this->sendMailForQuote($quoteSuccess, $messageSuccess, $wholedata);
            return $this->resultRedirectFactory
                ->create()->setPath(
                    'mpquotesystem/buyerquote/index/',
                    [
                    'id' => $wholedata['quote_id'],
                    '_secure' => $this->getRequest()->isSecure(),
                    ]
                );
        } else {
            $this->messageManager->addError(__('Something Went Wrong, Please try again later.'));

            return $this->resultRedirectFactory
                ->create()->setPath(
                    'mpquotesystem/buyerquote/index/',
                    [
                    '_secure' => $this->getRequest()->isSecure(),
                    ]
                );
        }
    }

    /**
     * Send Mail For Quote
     *
     * @param bool $quoteSuccess
     * @param bool $messageSuccess
     * @param array $wholedata
     *
     * @return void
     */
    public function sendMailForQuote($quoteSuccess, $messageSuccess, $wholedata)
    {
        if ($quoteSuccess == 1 && $messageSuccess == 1) {
            $this->_mailHelper->quoteEdited(
                $wholedata['quote_id'],
                $wholedata['quote_message']
            );
        } elseif ($messageSuccess == 1) {
            $this->_mailHelper->quoteMessage(
                $wholedata['quote_id'],
                $wholedata['quote_message'],
                'customer'
            );
        }
    }
    
    /**
     * Get Product Qty
     *
     * @param array $wholedata
     *
     * @return $productQty
     */
    protected function getProductQty($wholedata)
    {
        $quote = $this->_mpquote->create()->load($wholedata['quote_id']);
        $productId = $wholedata['product_id'];
        $product = $this->_helperData->getProduct($productId);
        if ($product->getTypeId() == "bundle") {
            $bundleOption = $this->_helperData->convertStringAccToVersion($quote->getBundleOption(), 'decode');
            $productQty = $this->_helperData->getBundleProductQuatity(
                $product,
                $bundleOption
            );
        } elseif ($product->getTypeId()=='configurable') {
            $productQty = $this->_helperData->getConfigurableProductQuantity(
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
     * @return string
     */
    public function getFinalProductPrice($quote, $product)
    {
        $params = [];
        if ($product->getTypeId()=='bundle') {
            $bundleOption = $this->_helperData->convertStringAccToVersion(
                $quote->getBundleOption(),
                'decode'
            );
            $params['bundle_option_to_calculate'] = $bundleOption;
        }
        $params['options'] = $this->_helperData->convertStringAccToVersion(
            $quote->getProductOption(),
            'decode'
        );
        $params['super_attribute'] = $this->_helperData->convertStringAccToVersion(
            $quote->getSuperAttribute(),
            'decode'
        );
        $params['links'] = $this->_helperData->convertStringAccToVersion(
            $quote->getLinks(),
            'decode'
        );
        $params['product'] = $product->getEntityId();
        $productPrice = $this->_helperData->calculateProductPrice(
            $params
        );
        return($productPrice);
    }
}
