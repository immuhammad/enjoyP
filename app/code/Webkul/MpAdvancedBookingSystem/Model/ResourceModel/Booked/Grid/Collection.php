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
namespace Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Booked\Grid;

use Magento\Framework\Api\Search\SearchResultInterface as SearchInterface;
use Magento\Framework\Search\AggregationInterface;
use Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Booked\Collection as BookingCollection;
use Magento\Framework\Api\SearchCriteriaInterface as SearchCriteria;

/**
 * Webkul MpAdvancedBookingSystem Booked Grid Collection
 */
class Collection extends BookingCollection implements SearchInterface
{
    /**
     * @var AggregationInterface
     */
    protected $_aggregations;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $eavAttribute;

    /**
     * @var mixed
     */
    protected $_eventPrefix;

    /**
     * @var mixed
     */
    protected $_eventObject;

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entity
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetch
     * @param \Magento\Framework\Event\ManagerInterface $event
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param mixed $mainTable
     * @param mixed $eventPrefix
     * @param mixed $eventObject
     * @param mixed $resourceModel
     * @param string $model
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entity,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetch,
        \Magento\Framework\Event\ManagerInterface $event,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->eavAttribute = $eavAttribute;
        parent::__construct(
            $entity,
            $logger,
            $fetch,
            $event,
            $storeManager,
            $connection,
            $resource
        );
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
    }

    /**
     * GetAggregations
     *
     * @return AggregationInterface
     */
    public function getAggregations()
    {
        return $this->_aggregations;
    }

    /**
     * SetAggregations
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
     * @param  int $limitCount
     * @param  int $offset
     * @return array
     */
    public function getAllIds($limitCount = null, $offset = null)
    {
        return $this->getConnection()->fetchCol(
            $this->_getAllIdsSelect($limitCount, $offset),
            $this->_bindParams
        );
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
     * @param SearchCriteria $searchCriteria
     * @return $this
     */
    public function setSearchCriteria(SearchCriteria $searchCriteria = null)
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
     * Join store relation table if there is store filter.
     */
    protected function _renderFiltersBefore()
    {
        $proAttId = $this->eavAttribute->getIdByCode('catalog_product', 'name');
        $orderGridTable = $this->getTable('sales_order_grid');
        $catalogProductEntityVarchar = $this->getTable('catalog_product_entity_varchar');
        $sql = $catalogProductEntityVarchar.' as cpev';
        $cond = 'main_table.product_id = cpev.entity_id';
        $fields = ['product_name' => 'value'];
        $this->getSelect()
            ->join($sql, $cond, $fields)
            ->where('cpev.store_id = 0 AND cpev.attribute_id = '.$proAttId);
        $this->addFilterToMap('product_name', 'cpev.value');

        $sql = $orderGridTable.' as ogt';
        $cond = 'main_table.order_id = ogt.entity_id';
        $fields = [
            'increment_id' => 'increment_id',
            'customer_email' => 'main_table.customer_email',
            'status' => 'ogt.status'
        ];
        $this->getSelect()
            ->join($sql, $cond, $fields);
        $this->addFilterToMap('increment_id', 'ogt.increment_id');
        parent::_renderFiltersBefore();
    }
}
