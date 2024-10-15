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
 * Customer's Quote(Sent By Supplier) View Block
 */
namespace Webkul\B2BMarketplace\Block\Quote;

use Webkul\B2BMarketplace\Helper\Data as HelperData;
use Webkul\B2BMarketplace\Helper\Quote as B2bQuoteHelper;
use Webkul\B2BMarketplace\Model\ResourceModel\Conversation\CollectionFactory;
use Webkul\B2BMarketplace\Model\ResourceModel\Item\CollectionFactory as ItemCollectionFactory;
use Webkul\B2BMarketplace\Model\ResourceModel\Quotation\CollectionFactory as QuotationCollectionFactory;
use Webkul\B2BMarketplace\Api\QuoteManagementInterface;

class Request extends View
{
    /**
     * getQuoteId
     *
     * @return Webkul\B2BMarketplace\Model\Quote
     */
    public function getQuote()
    {
        $requestId = $this->_b2bQuoteHelper->getRequestQuoteId();
        return $this->_b2bQuoteHelper->getQuoteByRequestId($requestId);
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
        $requestId = $this->_b2bQuoteHelper->getRequestQuoteId();
        $quoteId = $this->getQuoteId();
        $collection = $this->itemCollectionFactory->create()
            ->addFieldToFilter('main_table.quote_id', $quoteId);
        return $collection->getBuyerQuoteWithItems($requestId);
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
        $requestId = $this->_b2bQuoteHelper->getRequestQuoteId();
        $customerId = $this->_b2bQuoteHelper->getCustomerId();
        $collection = $this->quotationCollectionFactory->create()
            ->addFieldToFilter('main_table.request_id', $requestId)
            ->addFieldToFilter('main_table.quote_item_id', ['in' => $itemIds])
            ->getQuoteCollectionByCustomerId($customerId);

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
            $result[$itemId]["quotaions"][$item['entity_id']]["can_update_quote"] = 1;
            if ($item['status'] == B2bQuoteHelper::QUOTE_STATUS_APPROVED ||
            $item['status'] == B2bQuoteHelper::QUOTE_STATUS_REJECTED) {
                $result[$itemId]["quotaions"][$item['entity_id']]["can_update_quote"] = 0;
            }
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
                $thread["label"] = __('You');
            } else { // seller
                $thread["name"] = $item['sender_firstname']." ".$item['sender_lastname'];
                $thread["class"] = "wk-vendor";
                $thread["label"] = __('Supplier');
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
     * Returns attachment URL.
     *
     * @param int $attachmentId
     * @return string
     */
    public function getAttachmentUrl($attachmentId)
    {
        return $this->getUrl('b2bmarketplace/customer_quote_attachment/download', ['attachmentId' => $attachmentId]);
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
            'b2bmarketplace/customer_quote_item_attachment/download',
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
