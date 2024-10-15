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

namespace Webkul\B2BMarketplace\Model;

use Webkul\B2BMarketplace\Api\AttachmentRepositoryInterface;
use Webkul\B2BMarketplace\Api\Data\AttachmentInterface;
use Webkul\B2BMarketplace\Api\Data\AttachmentInterfaceFactory;
use Webkul\B2BMarketplace\Model\ResourceModel\Attachment as AttachmentResource;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Webkul\B2BMarketplace\Logger\Logger;
use Webkul\B2BMarketplace\Model\ResourceModel\Attachment\SearchProvider;

class AttachmentRepository implements AttachmentRepositoryInterface
{
    /**
     * @var \Webkul\B2BMarketplace\Api\Data\AttachmentInterface[]
     */
    private $instances = [];

    /**
     * B2b quote item attachment resource
     *
     * @var \Webkul\B2BMarketplace\Model\ResourceModel\Attachment
     */
    private $attachmentResource;

    /**
     * @var \Webkul\B2BMarketplace\Api\Data\AttachmentInterfaceFactory`
     */
    private $attachmentFactory;

    /**
     * Logger
     *
     * @var Logger
     */
    private $logger;

    /**
     * @var \Webkul\B2BMarketplace\Model\Attachment\SearchProvider
     */
    private $searchProvider;

    /**
     * @param AttachmentResource         $attachmentResource
     * @param AttachmentInterfaceFactory $attachmentFactory
     * @param Logger                         $logger
     * @param SearchProvider                 $searchProvider
     */
    public function __construct(
        AttachmentResource $attachmentResource,
        AttachmentInterfaceFactory $attachmentFactory,
        Logger $logger,
        SearchProvider $searchProvider
    ) {
        $this->attachmentResource = $attachmentResource;
        $this->attachmentFactory = $attachmentFactory;
        $this->logger = $logger;
        $this->searchProvider = $searchProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function save(AttachmentInterface $attachment)
    {
        try {
            $this->attachmentResource->save($attachment);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            throw new CouldNotSaveException(__('There was an error saving quote item attachment.'));
        }

        return $attachment;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        if (!isset($this->instances[$id])) {
            /** @var AttachmentInterface $attachment */
            $attachment = $this->attachmentFactory->create();
            $attachment->load($id);
            if (!$attachment->getId()) {
                throw NoSuchEntityException::singleField('id', $id);
            }
            $this->instances[$id] = $attachment;
        }
        return $this->instances[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        return $this->searchProvider->getList($searchCriteria);
    }
}
