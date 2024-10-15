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

use Webkul\MpVendorAttributeManager\Api\Data\VendorAssignGroupInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class VendorAssignGroup extends AbstractModel implements VendorAssignGroupInterface, IdentityInterface
{

    public const CACHE_TAG = 'assign_group';
     /**#@-*/
    /**
     * @var string
     */
    protected $_cacheTag = 'assign_group';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'assign_group';

    /**
     * Function _construct
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAssignGroup::class);
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
    public function getAttributeId()
    {
        return $this->getData(self::ATTRIBUTE_ID);
    }

    /**
     * Get status.
     *
     * @return int|null
     */
    public function getGroupId()
    {
        return $this->getData(self::GROUP_ID);
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
     * @param int $atttributeId
     * @return int|null
     */
    public function setAttributeId($atttributeId)
    {
        $this->setData(self::ATTRIBUTE_ID, $atttributeId);
    }

    /**
     * Set Show in Front.
     *
     * @param int $groupId
     * @return int|null
     */
    public function setGroupId($groupId)
    {
        $this->setData(self::GROUP_ID, $groupId);
    }
}
