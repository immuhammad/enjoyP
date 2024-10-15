<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_B2BMarketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

/**
 * Supplier Quote View Block
 */
namespace Webkul\B2BMarketplace\Block\Quote;

use Webkul\B2BMarketplace\Helper\Data as HelperData;
use Webkul\B2BMarketplace\Helper\Quote as B2bQuoteHelper;
use Webkul\B2BMarketplace\Model\ResourceModel\Conversation\CollectionFactory;
use Webkul\B2BMarketplace\Model\ResourceModel\Item\CollectionFactory as ItemCollectionFactory;
use Webkul\B2BMarketplace\Model\ResourceModel\Quotation\CollectionFactory as QuotationCollectionFactory;
use Webkul\B2BMarketplace\Api\QuoteManagementInterface;

class View extends \Magento\Framework\View\Element\Template
{
    /**
     * HelperData
     */
    protected $_helper;

    /**
     * CollectionFactory
     */
    protected $collectionFactory;

    /**
     * ItemCollectionFactory
     */
    protected $itemCollectionFactory;

    /**
     * QuotationCollectionFactory
     */
    protected $quotationCollectionFactory;

    /**
     * @var QuoteManagementInterface
     */
    protected $quoteManagement;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $helperFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param HelperData $helper
     * @param B2bQuoteHelper $b2bQuoteHelper
     * @param \Webkul\B2BMarketplace\Model\ItemFactory $itemFactory
     * @param CollectionFactory $collectionFactory
     * @param ItemCollectionFactory $itemCollectionFactory
     * @param QuotationCollectionFactory $quotationCollectionFactory
     * @param QuoteManagementInterface $quoteManagement
     * @param array $data [optional]
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        HelperData $helper,
        B2bQuoteHelper $b2bQuoteHelper,
        \Webkul\B2BMarketplace\Model\ItemFactory $itemFactory,
        CollectionFactory $collectionFactory,
        ItemCollectionFactory $itemCollectionFactory,
        QuotationCollectionFactory $quotationCollectionFactory,
        QuoteManagementInterface $quoteManagement,
        \Magento\Framework\ObjectManagerInterface $helperFactory,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->_b2bQuoteHelper = $b2bQuoteHelper;
        $this->_itemFactory = $itemFactory;
        $this->collectionFactory = $collectionFactory;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->quotationCollectionFactory = $quotationCollectionFactory;
        $this->quoteManagement = $quoteManagement;
        $this->helperFactory = $helperFactory;
        parent::__construct($context, $data);
    }

    /**
     * getQuoteId
     *
     * @return Webkul\B2BMarketplace\Model\Quote
     */
    public function getQuote()
    {
        return $this->_b2bQuoteHelper->getQuote();
    }

    /**
     * getQuoteId
     *
     * @return int
     */
    public function getQuoteId()
    {
        return $this->getQuote()->getEntityId();
    }

    /**
     * getQuoteSupplierId
     *
     * @return int
     */
    public function getQuoteSupplierId()
    {
        return $this->getQuote()->getSellerId();
    }

    /**
     * getAllRequestedQuoteItems
     *
     * @return Webkul\B2BMarketplace\Model\Item
     */
    public function getAllRequestedQuoteItems()
    {
        $sellerId = $this->_b2bQuoteHelper->getSupplierId();
        $quoteId = $this->getQuoteId();
        $collection = $this->itemCollectionFactory->create()
            ->addFieldToFilter('main_table.quote_id', $quoteId)
            ->getAllSellerQuoteItemsBySellerId($sellerId);
        return $collection;
    }

    /**
     * getSampleRequiredLabel
     *
     * @param bool $isSampleRequired
     * @return string
     */
    public function getSampleRequiredLabel($isSampleRequired = 0)
    {
        if ($isSampleRequired) {
            return __('Yes');
        } else {
            return __('No');
        }
    }

    /**
     * getQuotations
     *
     * @param array $itemIds
     * @return array
     */
    public function getQuotations($itemIds)
    {
        $type = 0;
        $supplierId = $this->_b2bQuoteHelper->getSupplierId();
        $collection = $this->quotationCollectionFactory->create()
            ->addFieldToFilter('main_table.quote_item_id', ['in' => $itemIds])
            ->getQuoteCollectionBySupplierId($supplierId);

        $data = $collection->getData();
        $result = [];
        $ids = [];

        foreach ($data as $item) {
            $itemId = $item['quote_item_id'];
            if (!array_key_exists($itemId, $result)) {
                $result[$itemId]["name"] = $item['name'];
                $result[$itemId]["buyer_qty"] = $item['buyer_qty'];
                $result[$itemId]["buyer_price"] = $item['buyer_price'];
                $result[$itemId]["buyer_has_samples"] = $item['buyer_has_samples'];
            }
            if (!isset($result[$itemId]["count"])) {
                $result[$itemId]["count"] = 0;
            }
            $result[$itemId]["count"] = $result[$itemId]["count"] + $item['total_thread'];

            $result[$itemId]["quotaions"][$item['entity_id']] = $item;
        }

        return $result;
    }

    /**
     * getThreadsByQuotationId
     *
     * @param int $quotaionId
     * @return array
     */
    public function getThreadsByQuotationId($quotaionId)
    {
        $type = 0;
        $result = [];
        $customerId = $this->_b2bQuoteHelper->getCustomerId();
        $collection = $this->collectionFactory->create()
            ->addFieldToFilter('main_table.seller_quote_id', $quotaionId)
            ->getAllQuoteCoversations();

        $data = $collection->getData();
        foreach ($data as $item) {
            $date = $this->_helper->formatDate(
                $item['created_at'],
                \IntlDateFormatter::LONG,
                true
            );
            $thread = [];
            $thread["created_at"] = $date;
            $thread["msg"] = $item['response'];
            $thread["type"] = $item['sender_type'];
            if ($item['sender_type'] == B2bQuoteHelper::TYPE_BUYER) {
                // for customer
                $thread["name"] = $item['sender_firstname']." ".$item['sender_lastname'];
                $thread["class"] = "wk-customer";
                $thread["label"] = __('Customer');
            } else { // seller
                $thread["name"] = $item['sender_firstname']." ".$item['sender_lastname'];
                $thread["class"] = "wk-vendor";
                $thread["label"] = __('You');
            }
            $thread["note"] = "";
            $thread["note_class"] = "";
            if ($item['quote_status'] == 2) {
                $thread["note"] = __("Quote Rejected");
            } elseif ($item['quote_status'] == 1) {
                $thread["note"] = __("Quote Approved");
                $thread["note_class"] = "wk-quote-approved";
            }
            $result[] = $thread;
        }

        return $result;
    }

    /**
     * Returns collection of quote attachments.
     *
     * @param int $quoteId
     * @return array|\Webkul\B2BMarketplace\Model\ResourceModel\QuoteAttachment\Collection
     */
    public function getQuoteAttachments($quoteId)
    {
        if ($quoteId) {
            return $this->quoteManagement->getQuoteAttachments($quoteId);
        }
        return [];
    }

    /**
     * Returns collection of quote item attachments.
     *
     * @param int $itemId
     * @return array|\Webkul\B2BMarketplace\Model\ResourceModel\ItemAttachment\Collection
     */
    public function getQuoteItemAttachments($itemId)
    {
        if ($itemId) {
            return $this->quoteManagement->getQuoteItemAttachments($itemId);
        }
        return [];
    }

    /**
     * Returns attachment URL.
     *
     * @param int $attachmentId
     * @return string
     */
    public function getAttachmentUrl($attachmentId)
    {
        return $this->getUrl('b2bmarketplace/supplier_quote_attachment/download', ['attachmentId' => $attachmentId]);
    }

    /**
     * Returns item attachment URL.
     *
     * @param int $quoteId
     * @param int $attachmentId
     * @return string
     */
    public function getItemAttachmentUrl($quoteId, $attachmentId)
    {
        return $this->getUrl(
            'b2bmarketplace/supplier_quote_item_attachment/download',
            [
                'quoteId' => $quoteId,
                'attachmentId' => $attachmentId
            ]
        );
    }
    
    /**
     * @param String $className
     * @return object
     */
    public function helper($className)
    {
        $helper = $this->helperFactory->get($className);
        if (false === $helper instanceof \Magento\Framework\App\Helper\AbstractHelper) {
            throw new \LogicException($className . ' doesn\'t extends Magento\Framework\App\Helper\AbstractHelper');
        }
        return $helper;
    }
}
