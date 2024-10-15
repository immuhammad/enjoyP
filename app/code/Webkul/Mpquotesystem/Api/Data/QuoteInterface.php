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

interface QuoteInterface
{
    /**
     * Constants for keys of data array.
     */
    public const ENTITY_ID = 'entity_id';
    
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
}
