<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvancedBookingSystem
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedBookingSystem\Api\Data;

interface CancellationInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    public const ENTITY_ID = 'entity_id';
    /**
     * #@-
     */

    /**
     * Get ID.
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID.
     *
     * @param int $id
     *
     * @return \Webkul\MpAdvancedBookingSystem\Api\Data\CancellationInterface
     */
    public function setId($id);
}
