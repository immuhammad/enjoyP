<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvancedBookingSystem
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Cancellation\Grid;

use Magento\Framework\Api\Search\SearchResultInterface as SearchInterface;
use Magento\Framework\Search\AggregationInterface;
use Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Cancellation\Collection as CancellationCollection;

class Collection extends CancellationCollection implements SearchInterface
{
    /**
     * @var AggregationInterface
     */
    protected $_aggregations;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entity
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetch
     * @param \Magento\Framework\Event\ManagerInterface $eventManagerInterface
     * @param mixed|null $mainTable
     * @param string $eventPrefix
     * @param object $eventObject
     * @param mixed $resourceModel
     * @param object $model
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entity,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetch,
        \Magento\Framework\Event\ManagerInterface $eventManagerInterface,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entity,
            $logger,
            $fetch,
            $eventManagerInterface,
            $connection,
            $resource
        );
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
    }

    /**
     * Get Aggregation
     *
     * @return AggregationInterface
     */
    public function getAggregations()
    {
        return $this->_aggregations;
    }

    /**
     * Set Aggregation
     *
     * @param AggregationInterface $aggregations
     * @return $this
     */
    public function setAggregations($aggregations)
    {
        $this->_aggregations = $aggregations;
    }
    /**
     * Retrieve all ids for collection
     *
     * Backward compatibility with EAV collection
     *
     * @param int $limitCount
     * @param int $offset
     * @return array
     */
    public function getAllIds($limitCount = null, $offset = null)
    {
        return $this->getConnection()->fetchCol($this->_getAllIdsSelect($limitCount, $offset), $this->_bindParams);
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
     * Set search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return $this
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
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
     * @return $this
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface[] $items
     * @return $this
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * Init Select
     */
    protected function _initSelect()
    {
        $this->addFilterToMap('created_at', 'main_table.created_at');
        $this->addFilterToMap('status', 'main_table.status');
        $this->addFilterToMap('entity_id', 'main_table.entity_id');
        parent::_initSelect();
    }

    /**
     * Join store relation table if there is store filter
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $salesOrderItem  = $this->getTable('sales_order_item');
        $salesOrder = $this->getTable('sales_order');
        $this->getSelect()->join(
            $salesOrderItem.' as soi',
            'main_table.order_id = soi.order_id && main_table.order_item_id = soi.item_id',
            ['name' => 'name']
        );
        $this->getSelect()->join(
            $salesOrder.' as so',
            'so.entity_id = soi.order_id && main_table.order_id = so.entity_id',
            ['increment_id' => 'increment_id']
        );
        parent::_renderFiltersBefore();
    }
}
