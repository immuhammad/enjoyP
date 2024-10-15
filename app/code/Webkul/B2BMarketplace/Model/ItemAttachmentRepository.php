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

use Webkul\B2BMarketplace\Api\ItemAttachmentRepositoryInterface;
use Webkul\B2BMarketplace\Api\Data\ItemAttachmentInterface;
use Webkul\B2BMarketplace\Api\Data\ItemAttachmentInterfaceFactory;
use Webkul\B2BMarketplace\Model\ResourceModel\ItemAttachment as ItemAttachmentResource;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Webkul\B2BMarketplace\Logger\Logger;
use Webkul\B2BMarketplace\Model\ResourceModel\ItemAttachment\SearchProvider;

class ItemAttachmentRepository implements ItemAttachmentRepositoryInterface
{
    /**
     * @var \Webkul\B2BMarketplace\Api\Data\ItemAttachmentInterface[]
     */
    private $instances = [];

    /**
     * B2b quote item attachment resource
     *
     * @var \Webkul\B2BMarketplace\Model\ResourceModel\ItemAttachment
     */
    private $itemAttachmentResource;

    /**
     * @var \Webkul\B2BMarketplace\Api\Data\ItemAttachmentInterfaceFactory`
     */
    private $itemAttachmentFactory;

    /**
     * Logger
     *
     * @var Logger
     */
    private $logger;

    /**
     * @var \Webkul\B2BMarketplace\Model\ItemAttachment\SearchProvider
     */
    private $searchProvider;

    /**
     * @param ItemAttachmentResource         $itemAttachmentResource
     * @param ItemAttachmentInterfaceFactory $itemAttachmentFactory
     * @param Logger                         $logger
     * @param SearchProvider                 $searchProvider
     */
    public function __construct(
        ItemAttachmentResource $itemAttachmentResource,
        ItemAttachmentInterfaceFactory $itemAttachmentFactory,
        Logger $logger,
        SearchProvider $searchProvider
    ) {
        $this->itemAttachmentResource = $itemAttachmentResource;
        $this->itemAttachmentFactory = $itemAttachmentFactory;
        $this->logger = $logger;
        $this->searchProvider = $searchProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function save(ItemAttachmentInterface $itemAttachment)
    {
        try {
            $this->itemAttachmentResource->save($itemAttachment);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            throw new CouldNotSaveException(__('There was an error saving quote item attachment.'));
        }

        return $itemAttachment;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        if (!isset($this->instances[$id])) {
            /** @var ItemAttachmentInterface $itemAttachment */
            $itemAttachment = $this->itemAttachmentFactory->create();
            $itemAttachment->load($id);
            if (!$itemAttachment->getId()) {
                throw NoSuchEntityException::singleField('id', $id);
            }
            $this->instances[$id] = $itemAttachment;
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
