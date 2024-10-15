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

interface VendorAttributeInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    public const ENTITY_ID = 'entity_id';
    public const ATTRIBUTE_ID = 'attribute_id';
    public const SHOW_IN_FRONT = 'show_in_front';
    public const REQUIRED_FIELD = 'required_field';
    public const WK_ATTRIBUTE_STATUS = 'wk_attribute_status';
    public const ATTRIBUTE_USED_FOR = 'attribute_used_for';
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
     * Get Show in Front
     *
     * @return int|null
     */
    public function getShowInFront();

    /**
     * Get is required status
     *
     * @return int|null
     */
    public function getRequiredField();

    /**
     * Get WkAttribute Status
     *
     * @return int|null
     */
    public function getWkAttributeStatus();

    /**
     * Get Attribute UsedFor
     *
     * @return int|null
     */
    public function getAttributeUsedFor();

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
     * @param int $attributeId
     * @return int|null
     */
    public function setAttributeId($attributeId);

    /**
     * Set Show in Front
     *
     * @param int $show
     * @return int|null
     */
    public function setShowInFront($show);

    /**
     * Set is required status
     *
     * @param int $required
     * @return int|null
     */
    public function setRequiredField($required);

    /**
     * Set WkAttribute Status
     *
     * @param int $status
     * @return int|null
     */
    public function setWkAttributeStatus($status);

    /**
     * Set WkAttribute Status
     *
     * @param int $usedFor
     * @return int|null
     */
    public function setAttributeUsedFor($usedFor);
}
