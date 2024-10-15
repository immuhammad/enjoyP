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

namespace Webkul\Mpquotesystem\Api\Data;

interface QuoteConfigInterface
{
    /**
     * Constants for keys of data array.
     */
    public const ENTITY_ID = 'entity_id';
    public const CATEGORIES = 'categories';
    public const MIN_QTY = 'min_qty';
    public const SELLER_ID = 'seller_id';

    /**
     * Get entity ID
     *
     * @return int|null
     */
    public function getEntityId();

    /**
     * Set entity ID
     *
     * @param int $id
     * @return \Webkul\Mpquotesystem\Api\Data\QuoteInterface
     */
    public function setEntityId($id);

    /**
     * Get categeory IDs
     *
     * @return string|null
     */
    public function getCategories();

    /**
     * Set categories
     *
     * @param string $categoryIds
     * @return \Webkul\Mpquotesystem\Api\Data\QuoteInterface
     */
    public function setCategories($categoryIds);
    
    /**
     * Get seller id
     *
     * @return int|null
     */
    public function getSellerId();

    /**
     * Set categories
     *
     * @param string $sellerId
     *
     * @return \Webkul\Mpquotesystem\Api\Data\QuoteInterface
     */
    public function setSellerId($sellerId);

    /**
     * Get min qty
     *
     * @return int|null
     */
    public function getMinQty();

    /**
     * Set min qty
     *
     * @param int $minQty
     * @return \Webkul\Mpquotesystem\Api\Data\QuoteInterface
     */
    public function setMinQty($minQty);
}
