<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpRmaSystem
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpRmaSystem\Model\ResourceModel\Details\FrontendGrid\Customer;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Search\AggregationInterface;
use Webkul\MpRmaSystem\Model\ResourceModel\Details\Collection as DetailsCollection;

/**
 * Class Collection
 * Collection for displaying grid of mprmasystem details.
 */
class Collection extends DetailsCollection implements SearchResultInterface
{
    /**
     * @var AggregationInterface
     */
    protected $aggregations;

    /**
     * Initialize Dependencies
     *
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entity
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $event
     * @param \Magento\Store\Model\StoreManagerInterface $store
     * @param mixed $mainTable
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $eventPrefix
     * @param mixed $eventObject
     * @param mixed $resourceModel
     * @param \Magento\Framework\View\Element\UiComponent\DataProvider\Document $model
     * @param mixed|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     * @return void
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
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
        parent::__construct(
            $entity,
            $logger,
            $fetchStrategy,
            $event,
            $store,
            $connection,
            $resource
        );
        $this->customerSession = $customerSession;
        $this->_eventPrefix    = $eventPrefix;
        $this->_eventObject    = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
    }

    /**
     * Retrieve all ids for collection Backward compatibility with EAV collection.
     *
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    public function getAllIds($limit = null, $offset = null)
    {
        return $this->getConnection()->fetchCol($this->_getAllIdsSelect($limit, $offset), $this->_bindParams);
    }

    /**
     * Set aggregations
     *
     * @param AggregationInterface $aggregations
     *
     * @return $this
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
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
     * Get aggregations
     *
     * @return AggregationInterface
     */
    public function getAggregations()
    {
        return $this->aggregations;
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
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
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
     * Render
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $customerId = $this->customerSession->getCustomer()->getId();
        $this->getSelect()->group("main_table.id");
        $this->addFieldToFilter('customer_id', ['eq' => $customerId]);
        parent::_renderFiltersBefore();
    }
}
