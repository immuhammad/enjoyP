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
namespace Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAssignGroup;

use Webkul\MpVendorAttributeManager\Model\ResourceModel\AbstractCollection;
use Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory;
use Magento\Eav\Model\Entity;
use Magento\Framework\DB\Select;

/**
 * Webkul Marketplace ResourceModel Seller collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * @var \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory
     */
    protected $attributeCollectionFactory;

    /**
     * @var \Magento\Eav\Model\Entity
     */
    protected $entity;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $loggerInterface
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategyInterface
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollectionFactory
     * @param \Magento\Eav\Model\Entity $entity
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $loggerInterface,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategyInterface,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollectionFactory,
        \Magento\Eav\Model\Entity $entity,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $loggerInterface,
            $fetchStrategyInterface,
            $eventManager,
            $storeManagerInterface,
            $connection,
            $resource
        );
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->entity = $entity;
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Webkul\MpVendorAttributeManager\Model\VendorAssignGroup::class,
            \Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAssignGroup::class
        );
        $this->_map['fields']['attribute_id'] = 'main_table.attribute_id';
    }

    /**
     * Get result sorted ids
     *
     * @return array
     */
    public function getResultingIds()
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(Select::LIMIT_COUNT);
        $idsSelect->reset(Select::LIMIT_OFFSET);
        $idsSelect->reset(Select::COLUMNS);
        $idsSelect->reset(Select::ORDER);
        $idsSelect->columns('entity_id');
        return $this->getConnection()->fetchCol($idsSelect);
    }
    
    /**
     * Add filter by store
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     *
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
        }
        return $this;
    }

    /**
     * Get Vendor Attribute Id's assigned to Group ID
     *
     * @param int $groupId
     *
     * @return Collection $this
     */
    public function getAssignedAttributeIds($groupId)
    {
        $vendorAttribute = $this->getResource()->getTable('marketplace_vendor_attribute');
        
        $eavCollection = $this->getSelect()->joinLeft(
            ['attr'=>$vendorAttribute],
            "main_table.attribute_id = attr.attribute_id",
            [
                'vendorAttrId' => 'attr.entity_id',
            ]
        )->where("main_table.group_id =".$groupId);
        
        return $this;
    }

    /**
     * Get Vendor Attribute Id's assigned to Group ID
     *
     * @param int $groupId
     *
     * @return Collection $this
     */
    public function getGroupAttributes($groupId)
    {
        $vendorAttributeTable = $this->getResource()->getTable('marketplace_vendor_attribute');
        $vendorAssignGroupTable = $this->getResource()->getTable('marketplace_vendor_assign_group');

        $typeId = $this->entity->setType('customer')->getTypeId();
        
        $collection = $this->attributeCollectionFactory->create()
                        ->setEntityTypeFilter($typeId)
                        ->setOrder('sort_order', 'ASC');

        $collection->getSelect()
            ->join(
                ['vat' => $vendorAttributeTable],
                'vat.attribute_id = main_table.attribute_id',
                [
                    'required_field' => 'vat.required_field',
                    'wk_attribute_status' => 'vat.wk_attribute_status',
                    'attribute_used_for' => 'vat.attribute_used_for',
                    'show_in_front' => 'vat.show_in_front'
                ]
            )->join(
                ['vag' => $vendorAssignGroupTable],
                'vag.attribute_id = vat.attribute_id',
                [
                    'group_id' => 'vag.group_id',
                ]
            )->where("vag.group_id =".$groupId)->where("vat.wk_attribute_status = '1'");

        return $collection;
    }
}
