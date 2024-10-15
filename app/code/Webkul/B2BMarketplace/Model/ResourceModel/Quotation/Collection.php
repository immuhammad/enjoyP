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
namespace Webkul\B2BMarketplace\Model\ResourceModel\Quotation;

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
        $this->_init('Webkul\B2BMarketplace\Model\Quotation', 'Webkul\B2BMarketplace\Model\ResourceModel\Quotation');
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
        $this->_map['fields']['created_at'] = 'main_table.created_at';
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
     * getQuoteCollectionByCustomerId
     *
     * @param int $customerId
     * @return array
     */
    public function getQuoteCollectionByCustomerId($customerId = 0)
    {
        $quote = $this->getTable('wk_b2b_requestforquote_quote');
        $quoteItem = $this->getTable('wk_b2b_requestforquote_quote_item');
        $quoteConversation = $this->getTable('wk_b2b_requestforquote_quote_conversation');
        $customerEntity = $this->getTable('customer_entity');
        $this->getSelect()->join(
            $quote.' as main_quote',
            'main_table.quote_id = main_quote.entity_id',
            [
                'main_quote.customer_id',
                'main_quote.status as main_quote_status',
                'main_quote.quote_title',
                'main_quote.quote_desc',
                'main_quote.customer_name',
                'main_quote.customer_company_name',
                'main_quote.customer_address',
                'main_quote.customer_contact_no',
                'main_quote.forward_quote',
                'main_quote.is_buying_lead',
                'main_quote.created_at as quote_created_at',
                'main_quote.updated_at as quote_updated_at'
            ]
        );
        $this->getSelect()->join(
            $quoteItem.' as quote_item',
            'main_table.quote_item_id = quote_item.entity_id',
            [
                'name',
                'entity_id AS mp_quote_item_id',
                'price AS buyer_price',
                'qty AS buyer_qty',
                'has_samples AS buyer_has_samples',
                'buying_lead_status'
            ]
        );
        $this->getSelect()->join(
            $customerEntity.' as customer_entity',
            'main_table.seller_id = customer_entity.entity_id',
            [
                'CONCAT(customer_entity.firstname, " ", customer_entity.lastname) AS supplier_name'
            ]
        );
        $this->getSelect()->join(
            $quoteConversation.' as quote_conversation',
            'main_table.entity_id = quote_conversation.seller_quote_id',
            ['count(quote_conversation.entity_id) AS total_thread']
        );
        $this->getSelect()->group('quote_conversation.seller_quote_id');
        $this->getSelect()->where('main_quote.customer_id='.$customerId);
        return $this;
    }

    /**
     * getQuoteCollectionBySupplierId
     *
     * @param int $supplierId
     * @return array
     */
    public function getQuoteCollectionBySupplierId($supplierId = 0)
    {
        $quote = $this->getTable('wk_b2b_requestforquote_quote');
        $quoteItem = $this->getTable('wk_b2b_requestforquote_quote_item');
        $quoteConversation = $this->getTable('wk_b2b_requestforquote_quote_conversation');
        $customerEntity = $this->getTable('customer_entity');
        $this->getSelect()->join(
            $quote.' as main_quote',
            'main_table.quote_id = main_quote.entity_id',
            [
                'main_quote.customer_id',
                'main_quote.status as main_quote_status',
                'main_quote.quote_title',
                'main_quote.quote_desc',
                'main_quote.customer_name',
                'main_quote.customer_company_name',
                'main_quote.customer_address',
                'main_quote.customer_contact_no',
                'main_quote.forward_quote',
                'main_quote.is_buying_lead',
                'main_quote.created_at',
                'main_quote.updated_at'
            ]
        );
        $this->getSelect()->join(
            $quoteItem.' as quote_item',
            'main_table.quote_item_id = quote_item.entity_id',
            [
                'name',
                'entity_id AS mp_quote_item_id',
                'price AS buyer_price',
                'qty AS buyer_qty',
                'has_samples AS buyer_has_samples',
                'buying_lead_status'
            ]
        );
        $this->getSelect()->join(
            $customerEntity.' as customer_entity',
            'main_table.seller_id = customer_entity.entity_id',
            [
                'CONCAT(customer_entity.firstname, " ", customer_entity.lastname) AS supplier_name'
            ]
        );
        $this->getSelect()->join(
            $quoteConversation.' as quote_conversation',
            'main_table.entity_id = quote_conversation.seller_quote_id',
            ['count(quote_conversation.entity_id) AS total_thread']
        );
        $this->getSelect()->group('quote_conversation.seller_quote_id');
        $this->getSelect()->where('main_table.seller_id='.$supplierId);
        return $this;
    }
}
