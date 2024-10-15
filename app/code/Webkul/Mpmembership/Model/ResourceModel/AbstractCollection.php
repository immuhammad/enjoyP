<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpmembership
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpmembership\Model\ResourceModel;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Abstract collection of webkul Mpmembership model
 */
abstract class AbstractCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @param EntityFactoryInterface                              $entityFactory
     * @param \Psr\Log\LoggerInterface                            $loggerInterface
     * @param FetchStrategyInterface                              $fetchStrategyInterface
     * @param \Magento\Framework\Event\ManagerInterface           $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface          $storeManagerInterface
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param AbstractDb|null                                     $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $loggerInterface,
        FetchStrategyInterface $fetchStrategyInterface,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $loggerInterface,
            $fetchStrategyInterface,
            $eventManager,
            $connection,
            $resource
        );
    }

    /**
     * performAfterLoad method for performing operations after collection load
     *
     * @param string $tableName
     * @param string $columnName
     *
     * @return void
     */
    protected function performAfterLoad($tableName, $columnName)
    {
        $items = $this->getColumnValues($columnName);
        if (!empty($items)) {
            $connection = $this->getConnection();
        }
    }

    /**
     * Add field filter to collection
     *
     * @param array|string          $field
     * @param string|int|array|null $condition
     *
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'store_id') {
            return $this->addStoreFilter($condition, false);
        }

        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Get total count sql
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(\Magento\Framework\DB\Select::GROUP);

        return $countSelect;
    }
}
