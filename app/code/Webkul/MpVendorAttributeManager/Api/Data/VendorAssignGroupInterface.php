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

interface VendorAssignGroupInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    public const ENTITY_ID = 'entity_id';
    public const ATTRIBUTE_ID = 'attribute_id';
    public const GROUP_ID = 'group_id';
    /**#@-*/

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get Attribute ID
     *
     * @return int|null
     */
    public function getAttributeId();

    /**
     * Get Group ID
     *
     * @return int|null
     */
    public function getGroupId();

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
     * @param int $atttributeId
     * @return int|null
     */
    public function setAttributeId($atttributeId);

    /**
     * Set Group Id
     *
     * @param int $groupId
     * @return int|null
     */
    public function setGroupId($groupId);
}
