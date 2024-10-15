<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpmembership
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpmembership\Api\Data;

/**
 * Mpmembership Product interface.
 *
 * @api
 */
interface ProductInterface
{
    /**
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case
     */
    const ENTITY_ID = 'entity_id';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @param int $id contains id
     *
     * @return \Webkul\Mpmembership\Api\Data\ProductInterface
     */
    public function setId($id);
}
