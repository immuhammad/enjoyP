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

namespace Webkul\B2BMarketplace\Model\ResourceModel\Attachment;

/**
 * Search Provider for message Attachment entity.
 */
class SearchProvider
{
    /**
     * @var \Magento\Framework\Api\SearchResultsFactory
     */
    private $searchResultsFactory;

    /**
     * @var \Webkul\B2BMarketplace\Model\ResourceModel\Attachment\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param \Magento\Framework\Api\SearchResultsFactory $searchResultsFactory
     * @param \Webkul\B2BMarketplace\Model\ResourceModel\Attachment\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        \Magento\Framework\Api\SearchResultsFactory $searchResultsFactory,
        \Webkul\B2BMarketplace\Model\ResourceModel\Attachment\CollectionFactory $collectionFactory,
        \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResults
     * @throws \InvalidArgumentException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Magento\Framework\Api\SearchResults $searchResult */
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $collection = $this->collectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }
}
