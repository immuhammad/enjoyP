<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_B2BMarketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\B2BMarketplace\Model\ResourceModel\Message;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Store manager.
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->_storeManager = $storeManager;
    }

    /**
     * Define resource model.
     */
    protected function _construct()
    {
        $this->_init('Webkul\B2BMarketplace\Model\Message', 'Webkul\B2BMarketplace\Model\ResourceModel\Message');
        $this->_map['fields']['entity_id'] = 'main_table.id';
    }

    /**
     * Add filter by store.
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool                                 $withAdmin
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
     * Retrieve clear select
     *
     * @return \Magento\Framework\DB\Select
     */
    protected function _getClearSelect()
    {
        return $this->_buildClearSelect();
    }

    /**
     * Build clear select
     *
     * @param \Magento\Framework\DB\Select $select
     * @return \Magento\Framework\DB\Select
     */
    protected function _buildClearSelect($select = null)
    {
        if (null === $select) {
            $select = clone $this->getSelect();
        }
        $select->reset(\Magento\Framework\DB\Select::ORDER);
        $select->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $select->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $select->reset(\Magento\Framework\DB\Select::COLUMNS);

        return $select;
    }

    /**
     * Retrieve sum of customer's response_rate_cal_flag for collection
     *
     * @param int|string $limit
     * @param int|string $offset
     * @return array
     */
    public function getCustomerResponseRateCalFlagCount($limit = null, $offset = null)
    {
        $idsSelect = $this->_getClearSelect();
        $idsSelect->columns('SUM(response_rate_cal_flag) AS flagCount');
        $idsSelect->group('type');
        $idsSelect->Where('type = 0');
        $idsSelect->limit($limit, $offset);
        $idsSelect->resetJoinLeft();
        return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);
    }

    /**
     * Retrieve sum of supplier's response_rate_cal_flag for collection
     *
     * @param int|string $limit
     * @param int|string $offset
     * @return array
     */
    public function getSupplierResponseRateCalFlagCount($limit = null, $offset = null)
    {
        $idsSelect = $this->_getClearSelect();
        $idsSelect->columns('SUM(response_rate_cal_flag) AS flagCount');
        $idsSelect->group('type');
        $idsSelect->Where('type = 1');
        $idsSelect->limit($limit, $offset);
        $idsSelect->resetJoinLeft();
        return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);
    }

    public function getCustomerTotalUnseenMsgData($listId, $limit = null, $offset = null)
    {
        $idsSelect = $this->_getClearSelect();
        $idsSelect->columns('COUNT(*) AS total_unseen');
        $idsSelect->group('list_id');
        $idsSelect->Where(
            'seen_status = 0 AND list_id='.$listId.' AND type = '.\Webkul\B2BMarketplace\Helper\Quote::TYPE_SUPPLIER
        );
        $idsSelect->limit($limit, $offset);
        $idsSelect->resetJoinLeft();
        return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);
    }

    public function getSupplierTotalUnseenMsgData($listId, $limit = null, $offset = null)
    {
        $idsSelect = $this->_getClearSelect();
        $idsSelect->columns('COUNT(*) AS total_unseen');
        $idsSelect->group('list_id');
        $idsSelect->Where(
            'seen_status = 0 AND list_id='.$listId.' AND type = '.\Webkul\B2BMarketplace\Helper\Quote::TYPE_BUYER
        );
        $idsSelect->limit($limit, $offset);
        $idsSelect->resetJoinLeft();
        return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);
    }

    /**
     * Retrieve supplier's customer list collection
     * @param int $supplierId
     *
     * @return \Webkul\B2BMarketplace\Model\ResourceModel\Message\Collection
     */
    public function getCustomerList($supplierId)
    {
        $tableName = $this->getTable('wk_b2b_message_list');
        $sql = "list.id = main_table.list_id ";
        $this->getSelect()->join(['list' => $tableName], $sql, ['*']);
        $tableName = $this->getTable('customer_entity');
        $sql = "customer.entity_id = list.customer_id";
        $this->getSelect()->join(['customer' => $tableName], $sql, ['firstname', 'lastname']);
        $tableName = $this->getTable('wk_b2b_message_history');
        $sql = "(list.supplier_id = $supplierId)";
        $sql .= " and main_table.id in(select max(id) from $tableName group by list_id)";
        $this->getSelect()->where($sql);
        $this->setOrder("main_table.created_at");
        return $this;
    }

    /**
     * Retrieve customer's supplier list collection
     * @param int $supplierId
     *
     * @return \Webkul\B2BMarketplace\Model\ResourceModel\Message\Collection
     */
    public function getSupplierList($customerId)
    {
        $tableName = $this->getTable('wk_b2b_message_list');
        $sql = "list.id = main_table.list_id ";
        $this->getSelect()->join(['list' => $tableName], $sql, ['*']);
        $tableName = $this->getTable('customer_entity');
        $sql = "customer.entity_id = list.supplier_id";
        $this->getSelect()->join(['customer' => $tableName], $sql, ['firstname', 'lastname']);
        $tableName = $this->getTable('wk_b2b_message_history');
        $sql = "(list.customer_id = $customerId)";
        $sql .= " and main_table.id in(select max(id) from $tableName group by list_id)";
        $this->getSelect()->where($sql);
        $this->setOrder("main_table.created_at");
        return $this;
    }
}
