<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpVendorAttributeManager
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpVendorAttributeManager\Api\Data;

interface VendorGroupInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    public const ENTITY_ID = 'entity_id';
    public const GROUP_NAME = 'group_name';
    public const STATUS = 'status';
    /**#@-*/

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get group name
     *
     * @return int|null
     */
    public function getGroupName();

    /**
     * Get status
     *
     * @return int|null
     */
    public function getStatus();

    /**
     * Set ID
     *
     * @param int $id
     * @return int|null
     */
    public function setId($id);

    /**
     * Set Attribute ID
     *
     * @param int $name
     * @return int|null
     */
    public function setGroupName($name);

    /**
     * Set Show in Front
     *
     * @param int $status
     * @return int|null
     */
    public function setStatus($status);
}
