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

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Backend\App\Action;
use Magento\Store\Model\StoreManagerInterface;
use Webkul\Mpquotesystem;
use Magento\Backend\Model\Session;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var storeManager
     */
    protected $_storeManager;

    /**
     * @var Webkul\Mpquotesystem\Helper\Data
     */
    protected $_mpquotehelper;

    /**
     * @var Webkul\Mpquotesystem\Helper\Mail
     */
    protected $_mpquoteMailHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;

    /**
     * Undocumented function
     *
     * @param Action\Context                               $context
     * @param StoreManagerInterface                        $storeManager
     * @param Mpquotesystem\Helper\Data                    $mpQuoteHelper
     * @param Mpquotesystem\Helper\Mail                    $mailHelper
     * @param Mpquotesystem\Model\QuoteconversationFactory $quoteConversationFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime  $date
     * @param \Webkul\Marketplace\Helper\Data              $mpHelper
     */
    public function __construct(
        Action\Context $context,
        StoreManagerInterface $storeManager,
        Mpquotesystem\Helper\Data $mpQuoteHelper,
        Mpquotesystem\Helper\Mail $mailHelper,
        Mpquotesystem\Model\QuoteconversationFactory $quoteConversationFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\Marketplace\Helper\Data $mpHelper
    ) {
        $this->_storeManager = $storeManager;
        $this->_mpquoteHelper = $mpQuoteHelper;
        $this->_mpquoteMailHelper = $mailHelper;
        $this->_quoteconversationFactory = $quoteConversationFactory;
        $this->_date = $date;
        $this->mpHelper = $mpHelper;
        parent::__construct(
            $context
        );
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
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getParams();
        $quoteId = 0;
        if (array_key_exists('id', $data)) {
            $quoteId = $data['id'];
        }
        if ($this->getRequest()->isPost()) {
            try {
                if (!$this->_formKeyValidator->validate($this->getRequest())) {
                    return $resultRedirect->setPath(
                        '*/*/index',
                        ['_secure'=>$this->getRequest()->isSecure()]
                    );
                }
                $quote = $this->_mpquoteHelper->getWkQuoteModel()->load($data['id']);
                if (!$quote->getEntityId()) {
                    $this->messageManager->addError(
                        __('Quote is not exists.')
                    );
                    $quoteId = 0;
                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/index',
                        ['_secure'=>$this->getRequest()->isSecure()]
                    );
                }
                $saveFileName = $this->_mpquoteHelper->saveAttachment();
                $data['attachment'] = $saveFileName ? $saveFileName :
                                            $quote->getAttachment();
                $this->checkAndUpdateData($data, $quote);
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        return $this->resultRedirectFactory->create()->setPath(
            '*/*/index',
            ['_secure'=>$this->getRequest()->isSecure()]
        );
    }
    
    /**
     * Check And Update Data
     *
     * @param array  $data
     * @param object $quote
     *
     * @return bool
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
        if ($data['status'] == \Webkul\Mpquotesystem\Model\Quotes::STATUS_APPROVED
            && isset($data['quote_qty']) && $productQty < $data['quote_qty']
        ) {
            $this->messageManager->addError(
                __(
                    "Sorry!! This much product quantity is not available"
                )
            );
            return false;
        }
        $data['quote_price'] = preg_replace('/[^0-9,.]/', '', $data['quote_price']);
        if (array_key_exists('quote_message', $data)) {
            $this->updateQuoteData($data, $quote, $product);
        }
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
     * @return array
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
     * Update Quote Data
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
        $quoteConversation->setQuoteId($data['quote_id'])
            ->setConversation($data['quote_message'])
            ->setSender(0)
            ->setReceiver($quote->getCustomerId())
            ->setCreatedAt($this->_date->gmtDate())
            ->save();
        $approvedStatus = \Webkul\Mpquotesystem\Model\Quotes::STATUS_APPROVED;
        $soldStatus = \Webkul\Mpquotesystem\Model\Quotes::STATUS_SOLD;
        if ($quote->getStatus() != $soldStatus) {
            if ($quote->getQuoteQty() != $data['quote_qty']) {
                $quote->setQuoteQty($data['quote_qty']);
                $qtyStatus = 1;
            }
            if ($quote->getAttachment() != $data['attachment']) {
                $priceStatus = 1;
                $quote->setAttachment($data['attachment']);
            }
            if ($quote->getQuotePrice() != $data['quote_price']) {
                $priceStatus = 1;
                $quote->setQuotePrice($data['quote_price']);
            }
            if ($quote->getStatus() != $data['status']) {
                $quote->setStatus($data['status'])
                    ->save();
                $statusFlag = 1;
                if ($qtyStatus == 1 || $priceStatus == 1) {
                    $this->_mpquoteMailHelper->quoteEditedByAdmin(
                        $data['quote_id'],
                        $data['quote_message'],
                        'admin'
                    );
                } else {
                    $this->_mpquoteMailHelper->quoteStatusMail(
                        $data['quote_id'],
                        $data['quote_message'],
                        'admin'
                    );
                }
            } else {
                if ($qtyStatus != 1 && $priceStatus !=1) {
                    $this->_mpquoteMailHelper->quoteMessage(
                        $data['quote_id'],
                        $data['quote_message'],
                        'admin'
                    );
                }
            }
            if ($statusFlag == 0 && ($qtyStatus == 1 || $priceStatus == 1)) {
                $quote->save();
                $this->_mpquoteMailHelper->quoteEditedByAdmin(
                    $data['quote_id'],
                    $data['quote_message'],
                    'admin'
                );
            }
            $this->messageManager->addSuccess(
                __('Message Sent Successfully')
            );
        } else {
            if ($quote->getStatus() != $data['status']) {
                if ($quote->getStatus() == \Webkul\Mpquotesystem\Model\Quotes::STATUS_SOLD) {
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
}
