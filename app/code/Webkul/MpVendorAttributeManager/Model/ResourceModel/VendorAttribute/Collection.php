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
namespace Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAttribute;

use \Webkul\MpVendorAttributeManager\Model\ResourceModel\AbstractCollection;

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
     * @param \Webkul\MpVendorAttributeManager\Model\VendorGroupFactory $vendorGroupFactory
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
        \Webkul\MpVendorAttributeManager\Model\VendorGroupFactory $vendorGroupFactory,
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
        $this->vendorGroupFactory = $vendorGroupFactory;
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
            \Webkul\MpVendorAttributeManager\Model\VendorAttribute::class,
            \Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAttribute::class
        );
        $this->_map['fields']['attribute_id'] = 'main_table.attribute_id';
    }
    
    /**
     * Add filter by store
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
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
     * Get available attributes by group id
     *
     * @param int|null $groupId
     * @return void
     */
    public function getUnassignedAttributeCollection($groupId)
    {
        $eavAttribute = $this->getResource()->getTable('eav_attribute');
        
        $this->getSelect()->joinLeft(
            $eavAttribute.' as eav',
            'main_table.attribute_id = eav.attribute_id'
        );

        $activeVendorGroupCollection = $this->vendorGroupFactory->create()->getCollection()
                                            ->addFieldToFilter("status", "1")
                                            ->addFieldToFilter("entity_id", ["neq" => $groupId]);
        
        if ($activeVendorGroupCollection->getSize()) {
            $vendorAttributeTable = $this->getResource()->getTable('marketplace_vendor_attribute');
            $vendorAssignGroupTable = $this->getResource()->getTable('marketplace_vendor_assign_group');
            
            $typeId = $this->entity->setType('customer')->getTypeId();
            $collection = $this->attributeCollectionFactory->create()
                            ->setEntityTypeFilter($typeId)
                            ->setOrder('sort_order', 'ASC');

            $collection->getSelect()
                ->join(
                    ['vat' => $vendorAttributeTable],
                    'vat.attribute_id = main_table.attribute_id'
                )->joinLeft(
                    ['vag' => $vendorAssignGroupTable],
                    'vag.attribute_id = vat.attribute_id',
                    [
                        'group_id' => 'vag.group_id',
                    ]
                );
                
            $activeVendorGroups = $activeVendorGroupCollection->getColumnValues("entity_id");
            $collection->addFieldToFilter("group_id", ["in" => $activeVendorGroups]);

            if ($collection->getSize()) {
                $assignedAttributes = $collection->getColumnValues("attribute_id");
                $this->addFieldToFilter("attribute_id", ["nin" => $assignedAttributes]);
            }
        }

        return $this;
    }

    /**
     * Get EAV collection filtered from Vendor Attribute Table
     *
     * @return Collection $collection
     */
    public function getVendorAttributeCollection()
    {
        $typeId = $this->entity->setType('customer')->getTypeId();
        $vendorAttributeTable = $this->getResource()->getTable('marketplace_vendor_attribute');

        $collection = $this->attributeCollectionFactory->create()
            ->setEntityTypeFilter($typeId)
            ->setOrder('sort_order', 'ASC');
        $collection->getSelect()
            ->join(
                ['vat' => $vendorAttributeTable],
                'vat.attribute_id = main_table.attribute_id'
            );

        return $collection;
    }
}
