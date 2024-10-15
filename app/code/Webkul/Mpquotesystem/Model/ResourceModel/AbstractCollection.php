<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpquotesystem
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpquotesystem\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Collection;
use \Magento\Framework\Data\Collection\EntityFactoryInterface;
use \Psr\Log\LoggerInterface;
use \Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use \Magento\Framework\Event\ManagerInterface;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\DB\Adapter\AdapterInterface;
use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

abstract class AbstractCollection extends Collection\AbstractCollection
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface        $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface       $eventManager
     * @param StoreManagerInterface  $storeManager
     * @param AdapterInterface|null  $connection
     * @param AbstractDb|null        $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
        $this->_storeManager = $storeManager;
    }

    /**
     * PerformAfterLoad method for performing operations after collection load
     *
     * @param string $tableName
     * @param string $columnName
     * @return void
     */
    protected function performAfterLoad($tableName, $columnName)
    {
        $items = $this->getColumnValues($columnName);
        if (isset($items) && !empty($items)) {
            $connection = $this->getConnection();
        }
    }

    /**
     * Add field filter to collection
     *
     * @param array|string          $field
     * @param string|int|array|null $condition
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
