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
namespace Webkul\B2BMarketplace\Model\ResourceModel\Item\Grid;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Search\AggregationInterface;
use Webkul\B2BMarketplace\Model\ResourceModel\Item\Collection as ItemsCollection;

class Collection extends ItemsCollection implements SearchResultInterface
{
    /**
     * @var AggregationInterface
     */
    protected $_aggregations;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entity
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $event
     * @param \Magento\Store\Model\StoreManagerInterface $store
     * @param mixed|null $mainTable
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $eventPrefix
     * @param mixed $eventObject
     * @param mixed $resourceModel
     * @param string $model
     * @param null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entity,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $event,
        \Magento\Store\Model\StoreManagerInterface $store,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entity, $logger, $fetchStrategy, $event, $store, $connection, $resource);
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
        $this->addFilterToMap("quote_id", "main_table.quote_id");
    }

    /**
     * @return AggregationInterface
     */
    public function getAggregations()
    {
        return $this->_aggregations;
    }

    /**
     * @param AggregationInterface $aggregations
     *
     * @return $this
     */
    public function setAggregations($aggregations)
    {
        $this->_aggregations = $aggregations;
    }

    /**
     * Retrieve all ids for collection
     * Backward compatibility with EAV collection.
     *
     * @param int $limitCount
     * @param int $offset
     *
     * @return array
     */
    public function getAllIds($limitCount = null, $offset = null)
    {
        return $this->getConnection()->fetchCol($this->_getAllIdsSelect($limitCount, $offset), $this->_bindParams);
    }

    /**
     * Set search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return $this
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }
    
    /**
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Set items list.
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface[] $items
     *
     * @return $this
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set total count.
     *
     * @param int $totalCount
     *
     * @return $this
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Join store relation table if there is store filter.
     */
    protected function _renderFiltersBefore()
    {
        $quoteTable = $this->getTable('wk_b2b_requestforquote_quote');
     
        $sql = $quoteTable.' as rfq';
        $cond = 'main_table.quote_id = rfq.entity_id';
        $fields = [];
        $fields['quote_title'] = 'quote_title';
        $fields['quote_desc'] = 'quote_desc';
        $fields['customer_name'] = 'customer_name';
        $fields['quote_created_date'] = 'created_at';
        $this->getSelect()->join($sql, $cond, $fields);
        // $this->addFilterToMap('name', 'cgf.name');

        $tableName = $this->getTable('wk_b2b_requestforquote_quote_item');
        $sql = "main_table.entity_id = quote_item.entity_id ";
        $columns = [];
        $columns['item_status'] = "quote_item.status";
        $field = 'sum(quote_item.qty)';
        $columns['total_qty'] = new \Zend_Db_Expr($field);
        $field = 'sum(quote_item.price)';
        $columns['total_price'] = new \Zend_Db_Expr($field);
        $field = 'count(quote_item.product_id)';
        $columns['total_products'] = new \Zend_Db_Expr($field);
        $columns['item_product_id'] = "quote_item.product_id";

        $field = 'GROUP_CONCAT(quote_item.name)';
        $columns['names'] = new \Zend_Db_Expr($field);

        $this->getSelect()->join(['quote_item' => $tableName], $sql, $columns);

        $this->getSelect()->group("main_table.quote_id");

        parent::_renderFiltersBefore();
    }
}
