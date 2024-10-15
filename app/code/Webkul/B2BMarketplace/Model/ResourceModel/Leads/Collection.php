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
namespace Webkul\B2BMarketplace\Model\ResourceModel\Leads;

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
        $this->_init('Webkul\B2BMarketplace\Model\Leads', 'Webkul\B2BMarketplace\Model\ResourceModel\Leads');
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

    public function getBuyingLeadsCollection()
    {
        // Getting Requested Quote Item Details
        $tableName = $this->getTable('wk_b2b_requestforquote_quote_item');
        $sql = "main_table.quote_item_id = quote_item.entity_id ";
        $columns = [];
        $columns['name'] = 'name';
        $columns['price'] = 'price';
        $columns['qty'] = 'qty';
        $columns['description'] = 'description';
        $columns['categories'] = 'categories';
        $this->getSelect()->join(['quote_item' => $tableName], $sql, $columns);

        // Getting Customer Id
        $tableName = $this->getTable('wk_b2b_requestforquote_quote');
        $sql = "main_table.quote_id = quote.entity_id ";
        $columns = [];
        $columns['customer_id'] = 'customer_id';
        $columns['date'] = 'created_at';
        $columns['customer_address'] = 'customer_address';
        $this->getSelect()->join(['quote' => $tableName], $sql, $columns);

        // Getting Customer Name
        $tableName = $this->getTable('customer_entity');
        $sql = "quote.customer_id = customer.entity_id ";
        $columns = [];
        $columns["firstname"] = "firstname";
        $columns["lastname"] = "lastname";
        $this->getSelect()->joinLeft(['customer' => $tableName], $sql, $columns);

        // Query for getting records from wk_b2b_requestforquote_buying_leads
        //that are not exist in wk_b2b_requestforquote_sent_quotes
        $sentQuotes = $this->getTable('wk_b2b_requestforquote_sent_quotes');
        $this->getSelect()->joinLeft(
            $sentQuotes.' as sent_quotes',
            'main_table.quote_item_id = sent_quotes.quote_item_id AND main_table.seller_id = sent_quotes.seller_id',
            [
                'sent_quotes.quote_item_id as sent_quotes_item_id',
                'sent_quotes.seller_id as sent_quotes_seller_id'
            ]
        )->where('sent_quotes.quote_item_id IS NULL AND sent_quotes.seller_id IS NULL');

        return $this;
    }
}
