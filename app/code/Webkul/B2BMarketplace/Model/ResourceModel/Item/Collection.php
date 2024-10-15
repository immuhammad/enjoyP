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
namespace Webkul\B2BMarketplace\Model\ResourceModel\Item;

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
        $this->_init('Webkul\B2BMarketplace\Model\Item', 'Webkul\B2BMarketplace\Model\ResourceModel\Item');
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

    /**
     * Retrieve sum of supplier's response_rate_cal_flag for collection
     *
     * @param int|string $limit
     * @param int|string $offset
     * @return array
     */
    public function getCollectionBySupplier($limit = null, $offset = null)
    {
        $idsSelect = $this->_getClearSelect();
        $requestforquoteBuyingLeads = $this->getTable('wk_b2b_requestforquote_buying_leads');
        $idsSelect->columns(
            [
                '*'
            ]
        );
        $idsSelect->join(
            $requestforquoteBuyingLeads.' as buying_leads',
            'main_table.quote_id = buying_leads.quote_id AND main_table.entity_id = buying_leads.quote_item_id',
            [
                'count(buying_leads.seller_id) AS total_suppliers'
            ]
        );
        $idsSelect->group('buying_leads.quote_item_id');
        $idsSelect->limit($limit, $offset);
        $idsSelect->resetJoinLeft();
        return $this->getConnection()->fetchAll($idsSelect, $this->_bindParams);
    }

    /**
     * Retrieve sum of supplier's response_rate_cal_flag for collection
     *
     * @param int $supplierId
     * @return array
     */
    public function getCollectionBySupplierId($supplierId = 0)
    {
        $requestforquoteBuyingLeads = $this->getTable('wk_b2b_requestforquote_buying_leads');
        $this->getSelect()->columns(
            [
                '*'
            ]
        );
        $this->getSelect()->join(
            $requestforquoteBuyingLeads.' as buying_leads',
            'main_table.quote_id = buying_leads.quote_id AND main_table.entity_id = buying_leads.quote_item_id',
            [
                'count(buying_leads.seller_id) AS total_suppliers'
            ]
        );
        $this->getSelect()->where('buying_leads.seller_id='.$supplierId);
        $this->getSelect()->group('buying_leads.quote_item_id');
        return $this;
    }

    public function getBuyerQuoteWithItems($requestId)
    {
        $tableName = $this->getTable('wk_b2b_requestforquote_quote');
        $sql = "quote.entity_id = main_table.quote_id ";
        $columns = [];
        $columns[] = "customer_id";
        $this->getSelect()->join(['quote' => $tableName], $sql, $columns);

        $tableName = $this->getTable('wk_b2b_requestforquote_sent_quotes');
        $sql = "main_table.entity_id = quotation.quote_item_id ";
        $columns = [];
        $columns[] = "request_id";
        $columns["request_status"] = "status";
        $columns["request_entity_id"] = "entity_id";
        $this->getSelect()->join(['quotation' => $tableName], $sql, $columns);

        $this->getSelect()->group("main_table.entity_id");

        $sql = "quotation.request_id = $requestId";
        $this->getSelect()->where($sql);
        return $this;
    }

    /**
     * getAllSellerQuote
     *
     * @param int $sellerId
     * @return $this
     */
    public function getAllSellerQuoteItemsBySellerId($sellerId)
    {
        // Query for getting records from wk_b2b_requestforquote_sent_quotes if seller sent quote for buyingleads
        $sentQuotes = $this->getTable('wk_b2b_requestforquote_sent_quotes');
        $this->getSelect()->joinLeft(
            $sentQuotes.' as sent_quotes',
            'main_table.entity_id = sent_quotes.quote_item_id',
            [
                'sent_quotes.request_id as request_id',
                'sent_quotes.quote_item_id as sent_quotes_item_id',
                'sent_quotes.seller_id as sent_quotes_seller_id'
            ]
        )->where(
            '(main_table.seller_id='.$sellerId.') OR (main_table.seller_id=0 AND sent_quotes.seller_id='.$sellerId.')'
        );

        $this->getSelect()->group('main_table.entity_id');
        return $this;
    }
}
