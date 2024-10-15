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

use Webkul\B2BMarketplace\Api\Data\QuoteAttachmentInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface QuoteAttachmentRepositoryInterface
 * @api
 * @since 100.0.0
 */
interface QuoteAttachmentRepositoryInterface
{
    /**
     * Set the quote attachment for a b2b quote.
     *
     * @param QuoteAttachmentInterface $quoteAttachment quote.
     * @return QuoteAttachmentInterface
     * @throws CouldNotSaveException
     */
    public function save(QuoteAttachmentInterface $quoteAttachment);

    /**
     * Get quote attachment by ID
     *
     * @param int $attachmentId
     * @return QuoteAttachmentInterface
     * @throws NoSuchEntityException
     */
    public function get($attachmentId);

    /**
     * Get list of quote attachments
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     * @throws \InvalidArgumentException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
