<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_B2BMarketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\B2BMarketplace\Api;

use Webkul\B2BMarketplace\Model\ResourceModel\QuoteAttachment\Collection as QuoteAttachmentCollection;

/**
 * Interface for managing b2b quotes.
 * @api
 * @since 100.0.0
 */
interface QuoteManagementInterface
{
    /**
     * Save b2b quote.
     *
     * @param array $data
     * @return bool
     */
    public function saveQuote(
        array $data = []
    );

    /**
     * Save b2b quote items.
     *
     * @param array $rfqProductData
     * @param int $supplierId
     * @param int $quoteId
     * @return void
     */
    public function saveQuoteItem($rfqProductData, $supplierId, $quoteId);

    /**
     * Save file and create attachment for quote.
     *
     * @param array $data
     * @param int $quoteId
     * @return void
     */
    public function saveQuoteAttachment($data, $quoteId);

    /**
     * Save file and create attachment for quote item.
     *
     * @param array $data
     * @param int $itemId
     * @return void
     */
    public function saveQuoteItemAttachment($data, $itemId);

    /**
     * Save file and create attachment for message.
     *
     * @param array $data
     * @param int $messageId
     * @return void
     */
    public function saveMessageAttachment($data, $messageId);

    /**
     * Returns collection of attachment for quote.
     *
     * @param int $quoteId
     * @return QuoteAttachmentCollection
     */
    public function getQuoteAttachments($quoteId);

    /**
     * Returns collection of attachment for quote item.
     *
     * @param int $itemId
     * @return ItemAttachmentCollection
     */
    public function getQuoteItemAttachments($itemId);

    /**
     * Returns collection of attachment for message.
     *
     * @param int $messageId
     * @return AttachmentCollection
     */
    public function getAttachments($messageId);

    /**
     * Returns array data for quote.
     *
     * @param array $params
     * @return array
     */
    public function getRequestData($params);

    /**
     * Returns array data for quote.
     *
     * @param \Webkul\B2BMarketplace\Api\Data\QuoteInterface
     * @param string $itemsHtml
     * @param int $supplierId
     * @param int $customerId
     * @return void
     */
    public function processRFQMailToSupplier($quote, $itemsHtml, $supplierId, $customerId);
}
