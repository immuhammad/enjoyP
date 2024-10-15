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

use Webkul\B2BMarketplace\Api\Data\ItemAttachmentInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface ItemAttachmentRepositoryInterface
 * @api
 * @since 100.0.0
 */
interface ItemAttachmentRepositoryInterface
{
    /**
     * Set the attachment for a b2b quote item.
     *
     * @param ItemAttachmentInterface $itemAttachment.
     * @return ItemAttachmentInterface
     * @throws CouldNotSaveException
     */
    public function save(ItemAttachmentInterface $itemAttachment);

    /**
     * Get quote item attachment by ID
     *
     * @param int $attachmentId
     * @return ItemAttachmentInterface
     * @throws NoSuchEntityException
     */
    public function get($attachmentId);

    /**
     * Get list of quote item attachments
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     * @throws \InvalidArgumentException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
