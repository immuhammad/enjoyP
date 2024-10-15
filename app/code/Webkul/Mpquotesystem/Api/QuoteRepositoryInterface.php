<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpquotesystem
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpquotesystem\Api;

/**
 * Quote interface.
 *
 * @api
 */
interface QuoteRepositoryInterface
{
    /**
     * Create or update a quote.
     *
     * @param \Webkul\Mpquotesystem\Api\Data\QuoteInterface $quote
     * @return \Webkul\Mpquotesystem\Api\Data\QuoteInterface
     */
    public function save(\Webkul\Mpquotesystem\Api\Data\QuoteInterface $quote);

    /**
     * Get quote by quote Id
     *
     * @param int $quoteId
     * @return \Webkul\Mpquotesystem\Api\Data\QuoteInterface
     */
    public function getById($quoteId);

    /**
     * Delete quote.
     *
     * @param \Webkul\Mpquotesystem\Api\Data\QuoteInterface $quote
     * @return bool true on success
     */
    public function delete(\Webkul\Mpquotesystem\Api\Data\QuoteInterface $quote);

    /**
     * Delete quote by ID.
     *
     * @param int $quoteId
     * @return bool true on success
     */
    public function deleteById($quoteId);
}
