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
namespace Webkul\MpVendorAttributeManager\Model\ResourceModel;

class VendorGroup extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var null|\Magento\Store\Model\Store
     */
    protected $_store = null;

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('marketplace_vendor_group', 'entity_id');
    }

    /**
     * Load an object using 'identifier' field if there's no field specified and value is not numeric
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param mixed $value
     * @param string $field
     * @return $this
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        if (!is_numeric($value) && ($field === null)) {
            $field = 'identifier';
        }

        return parent::load($object, $value, $field);
    }

    /**
     * Set store model
     *
     * @param \Magento\Store\Model\Store $store
     * @return $this
     */
    public function setStore($store)
    {
        $this->_store = $store;
        return $this;
    }

    /**
     * Retrieve store model
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->_storeManager->getStore($this->_store);
    }

    /**
     * Function getAnyAttributeAssignedToCustomer
     *
     * @param object $vendorGroup
     * @return void
     */
    public function getAnyAttributeAssignedToCustomer($vendorGroup)
    {
        if ($vendorGroup instanceof \Webkul\MpVendorAttributeManager\Model\VendorGroup) {
            $connection = $this->getConnection();
            $select = $connection->select()
            ->from(
                ['main_table' => $this->getMainTable()],
                '*'
            )
            ->join(
                ['assign_group' => $this->getTable('marketplace_vendor_assign_group')],
                'main_table.entity_id = assign_group.group_id',
                'attribute_id'
            )
            ->join(
                ['vendor_attribute' => $this->getTable('marketplace_vendor_attribute')],
                'vendor_attribute.attribute_id = assign_group.attribute_id',
                'attribute_id'
            )
            ->where(
                'status = 1 and attribute_used_for in (0, 1)'
            )
            ->reset(
                \Magento\Framework\DB\Select::COLUMNS
            )
            ->columns(['count(*) as cnt']);

            $row = $connection->fetchRow($select);
            if ($row['cnt'] > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Function getAnyAttributeAssignedToSeller
     *
     * @param object $vendorGroup
     * @return void
     */
    public function getAnyAttributeAssignedToSeller($vendorGroup)
    {
        if ($vendorGroup instanceof \Webkul\MpVendorAttributeManager\Model\VendorGroup) {
            $connection = $this->getConnection();
            $select = $connection->select()
            ->from(
                ['main_table' => $this->getMainTable()],
                '*'
            )
            ->join(
                ['assign_group' => $this->getTable('marketplace_vendor_assign_group')],
                'main_table.entity_id = assign_group.group_id',
                'attribute_id'
            )
            ->join(
                ['vendor_attribute' => $this->getTable('marketplace_vendor_attribute')],
                'vendor_attribute.attribute_id = assign_group.attribute_id',
                'attribute_id'
            )
            ->where(
                'status = 1 and attribute_used_for in (0, 2)'
            )
            ->reset(
                \Magento\Framework\DB\Select::COLUMNS
            )
            ->columns(['count(*) as cnt']);

            $row = $connection->fetchRow($select);
            if ($row['cnt'] > 0) {
                return true;
            }
        }

        return false;
    }
}
