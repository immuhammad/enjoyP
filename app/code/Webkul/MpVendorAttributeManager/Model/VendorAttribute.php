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
namespace Webkul\MpVendorAttributeManager\Model;

use Webkul\MpVendorAttributeManager\Api\Data\VendorAttributeInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class VendorAttribute extends AbstractModel implements VendorAttributeInterface, IdentityInterface
{

    public const CACHE_TAG = 'vendor_block';
    /**#@+
     * Block's statuses
     */
    public const STATUS_ENABLED = 1;
    public const STATUS_DISABLED = 0;

     /**#@-*/
    /**
     * @var string
     */
    protected $_cacheTag = 'vendor_block';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'vendor_block';

    /**
     * Function _construct
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAttribute::class);
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId(), self::CACHE_TAG . '_' . $this->getIdentifier()];
    }

    /**
     * Get ID.
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Get Attribute ID.
     *
     * @return int|null
     */
    public function getAttributeId()
    {
        return $this->getData(self::ATTRIBUTE_ID);
    }

    /**
     * Get Show in Front.
     *
     * @return int|null
     */
    public function getShowInFront()
    {
        return $this->getData(self::SHOW_IN_FRONT);
    }

    /**
     * Get is required.
     *
     * @return int|null
     */
    public function getRequiredField()
    {
        return $this->getData(self::REQUIRED_FIELD);
    }

    /**
     * Get WkAttribute Status
     *
     * @return int|null
     */
    public function getWkAttributeStatus()
    {
        return $this->getData(self::WK_ATTRIBUTE_STATUS);
    }

    /**
     * Get Attribute UsedFor
     *
     * @return int|null
     */
    public function getAttributeUsedFor()
    {
        return $this->getData(self::ATTRIBUTE_USED_FOR);
    }

    /**
     * Set ID.
     *
     * @param int $id
     * @return int|null
     */
    public function setId($id)
    {
        $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Set Attribute ID.
     *
     * @param int $attributeId
     * @return int|null
     */
    public function setAttributeId($attributeId)
    {
        $this->setData(self::ATTRIBUTE_ID, $attributeId);
    }

    /**
     * Set Show in Front.
     *
     * @param int $show
     * @return int|null
     */
    public function setShowInFront($show)
    {
        $this->setData(self::SHOW_IN_FRONT, $show);
    }

    /**
     * Set Show in Front.
     *
     * @param int $required
     * @return int|null
     */
    public function setRequiredField($required)
    {
        $this->setData(self::REQUIRED_FIELD, $required);
    }

    /**
     * Set WkAttribute Status
     *
     * @param int $status
     * @return int|null
     */
    public function setWkAttributeStatus($status)
    {
        $this->setData(self::WK_ATTRIBUTE_STATUS, $status);
    }

    /**
     * Set WkAttribute Status
     *
     * @param int $usedFor
     * @return int|null
     */
    public function setAttributeUsedFor($usedFor)
    {
        $this->setData(self::ATTRIBUTE_USED_FOR, $usedFor);
    }

    /**
     * Prepare block's statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Yes'), self::STATUS_DISABLED => __('No')];
    }

    /**
     * Prepare block's statuses.
     *
     * @return array
     */
    public function getAttrbiteStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }
    /**
     * Prepare block's statuses.
     *
     * @return array
     */
    public function getIsRequiredStatus()
    {
        return [self::STATUS_ENABLED => __('Yes'), self::STATUS_DISABLED => __('No')];
    }
}
