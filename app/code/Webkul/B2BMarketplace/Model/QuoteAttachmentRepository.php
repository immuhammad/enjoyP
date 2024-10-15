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

use Webkul\B2BMarketplace\Api\QuoteAttachmentRepositoryInterface;
use Webkul\B2BMarketplace\Api\Data\QuoteAttachmentInterface;
use Webkul\B2BMarketplace\Api\Data\QuoteAttachmentInterfaceFactory;
use Webkul\B2BMarketplace\Model\ResourceModel\QuoteAttachment as QuoteAttachmentResource;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Webkul\B2BMarketplace\Logger\Logger;
use Webkul\B2BMarketplace\Model\ResourceModel\QuoteAttachment\SearchProvider;

class QuoteAttachmentRepository implements QuoteAttachmentRepositoryInterface
{
    /**
     * @var \Webkul\B2BMarketplace\Api\Data\QuoteAttachmentInterface[]
     */
    private $instances = [];

    /**
     * B2b quoteAttachment attachment resource
     *
     * @var \Webkul\B2BMarketplace\Model\ResourceModel\QuoteAttachment
     */
    private $quoteAttachmentResource;

    /**
     * @var \Webkul\B2BMarketplace\Api\Data\QuoteAttachmentInterfaceFactory`
     */
    private $quoteAttachmentFactory;

    /**
     * Logger
     *
     * @var Logger
     */
    private $logger;

    /**
     * @var \Webkul\B2BMarketplace\Model\QuoteAttachment\SearchProvider
     */
    private $searchProvider;

    /**
     * @param QuoteAttachmentResource         $quoteAttachmentResource
     * @param QuoteAttachmentInterfaceFactory $quoteAttachmentFactory
     * @param Logger                $logger
     * @param SearchProvider  $searchProvider
     */
    public function __construct(
        QuoteAttachmentResource $quoteAttachmentResource,
        QuoteAttachmentInterfaceFactory $quoteAttachmentFactory,
        Logger $logger,
        SearchProvider $searchProvider
    ) {
        $this->quoteAttachmentResource = $quoteAttachmentResource;
        $this->quoteAttachmentFactory = $quoteAttachmentFactory;
        $this->logger = $logger;
        $this->searchProvider = $searchProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function save(QuoteAttachmentInterface $quoteAttachment)
    {
        try {
            $this->quoteAttachmentResource->save($quoteAttachment);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            throw new CouldNotSaveException(__('There was an error saving quote attachment.'));
        }

        return $quoteAttachment;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        if (!isset($this->instances[$id])) {
            /** @var QuoteAttachmentInterface $quoteAttachment */
            $quoteAttachment = $this->quoteAttachmentFactory->create();
            $quoteAttachment->load($id);
            if (!$quoteAttachment->getId()) {
                throw NoSuchEntityException::singleField('id', $id);
            }
            $this->instances[$id] = $quoteAttachment;
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
