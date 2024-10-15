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

use Webkul\B2BMarketplace\Api\Data\AttachmentInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface AttachmentRepositoryInterface
 */
interface AttachmentRepositoryInterface
{
    /**
     * Set the attachment for a message.
     *
     * @param AttachmentInterface $attachment.
     * @return AttachmentInterface
     * @throws CouldNotSaveException
     */
    public function save(AttachmentInterface $attachment);

    /**
     * Get message attachment by ID
     *
     * @param int $attachmentId
     * @return AttachmentInterface
     * @throws NoSuchEntityException
     */
    public function get($attachmentId);

    /**
     * Get list of message attachments
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     * @throws \InvalidArgumentException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
