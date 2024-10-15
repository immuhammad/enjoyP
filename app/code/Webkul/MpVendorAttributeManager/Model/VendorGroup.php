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

use Webkul\MpVendorAttributeManager\Api\Data\VendorGroupInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class VendorGroup extends AbstractModel implements VendorGroupInterface, IdentityInterface
{

    public const CACHE_TAG = 'vendor_group';
    /**#@+
     * Block's statuses
     */
    public const STATUS_ENABLED = 1;
    public const STATUS_DISABLED = 0;

     /**#@-*/
    /**
     * @var string
     */
    protected $_cacheTag = 'vendor_group';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'vendor_group';

    /**
     * Function _construct
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorGroup::class);
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
     * Get group name.
     *
     * @return int|null
     */
    public function getGroupName()
    {
        return $this->getData(self::GROUP_NAME);
    }

    /**
     * Get status.
     *
     * @return int|null
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
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
     * @param string $name
     * @return int|null
     */
    public function setGroupName($name)
    {
        $this->setData(self::GROUP_NAME, $name);
    }

    /**
     * Set Show in Front.
     *
     * @param int $status
     * @return int|null
     */
    public function setStatus($status)
    {
        $this->setData(self::STATUS, $status);
    }
    /**
     * Prepare block's statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    /**
     * Function getAnyAttributeAssignedToCustomer
     */
    public function getAnyAttributeAssignedToCustomer()
    {
        if (!$this->hasAnyAttributeAssignedToCustomer()) {
            return $this->_getResource()->getAnyAttributeAssignedToCustomer($this);
        }

        return false;
    }

    /**
     * Function getAnyAttributeAssignedToSeller
     */
    public function getAnyAttributeAssignedToSeller()
    {
        if (!$this->hasAnyAttributeAssignedToSeller()) {
            return $this->_getResource()->getAnyAttributeAssignedToSeller($this);
        }

        return false;
    }
}
