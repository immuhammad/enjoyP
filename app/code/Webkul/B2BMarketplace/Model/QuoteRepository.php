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

use Webkul\B2BMarketplace\Api\QuoteRepositoryInterface;
use Webkul\B2BMarketplace\Api\Data\QuoteInterface;
use Webkul\B2BMarketplace\Api\Data\QuoteInterfaceFactory;
use Webkul\B2BMarketplace\Model\ResourceModel\Quote as QuoteResource;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Webkul\B2BMarketplace\Logger\Logger;
use Webkul\B2BMarketplace\Model\Quote\SearchProvider;

class QuoteRepository implements QuoteRepositoryInterface
{
    /**
     * @var \Webkul\B2BMarketplace\Api\Data\QuoteInterface[]
     */
    private $instances = [];

    /**
     * b2b quote resource
     *
     * @var \Webkul\B2BMarketplace\Model\ResourceModel\Quote
     */
    private $quoteResource;

    /**
     * @var \Webkul\B2BMarketplace\Api\Data\QuoteInterfaceFactory`
     */
    private $quoteFactory;

    /**
     * Logger
     *
     * @var Logger
     */
    private $logger;

    /**
     * @var \Webkul\B2BMarketplace\Model\Quote\SearchProvider
     */
    private $searchProvider;

    /**
     * @param QuoteResource         $quoteResource
     * @param QuoteInterfaceFactory $quoteFactory
     * @param Logger                $logger
     * @param SearchProvider  $searchProvider
     */
    public function __construct(
        QuoteResource $quoteResource,
        QuoteInterfaceFactory $quoteFactory,
        Logger $logger,
        SearchProvider $searchProvider
    ) {
        $this->quoteResource = $quoteResource;
        $this->quoteFactory = $quoteFactory;
        $this->logger = $logger;
        $this->searchProvider = $searchProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function save(QuoteInterface $quote)
    {
        try {
            $this->quoteResource->save($quote);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            throw new CouldNotSaveException(__('There was an error saving quote.'));
        }

        return $quote;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        if (!isset($this->instances[$id])) {
            /** @var QuoteInterface $quote */
            $quote = $this->quoteFactory->create();
            $quote->load($id);
            if (!$quote->getId()) {
                throw NoSuchEntityException::singleField('id', $id);
            }
            $this->instances[$id] = $quote;
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

    /**
     * {@inheritdoc}
     */
    public function delete(QuoteInterface $quote)
    {
        try {
            $this->quoteResource->delete($quote);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            throw new StateException(
                __('Cannot delete quote with id %1', $quote->getEntityId()),
                $exception
            );
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($id)
    {
        return $this->delete($this->get($id));
    }
}
