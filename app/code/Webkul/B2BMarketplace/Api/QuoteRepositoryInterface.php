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

use Webkul\B2BMarketplace\Api\Data\QuoteInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface QuoteRepositoryInterface
 * @api
 * @since 100.0.0
 */
interface QuoteRepositoryInterface
{
    /**
     * Set the quote for a b2b quote.
     *
     * @param QuoteInterface $quote quote.
     * @return QuoteInterface
     * @throws CouldNotSaveException
     */
    public function save(QuoteInterface $quote);

    /**
     * Get quote by ID
     *
     * @param int $id
     * @return QuoteInterface
     * @throws NoSuchEntityException
     */
    public function get($id);

    /**
     * Get list of quotes
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     * @throws \InvalidArgumentException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete quote.
     *
     * @param QuoteInterface $quote
     * @return bool
     * @throws StateException
     */
    public function delete(QuoteInterface $quote);

    /**
     * Delete quote by ID
     *
     * @param int $id
     * @return bool
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteById($id);
}
