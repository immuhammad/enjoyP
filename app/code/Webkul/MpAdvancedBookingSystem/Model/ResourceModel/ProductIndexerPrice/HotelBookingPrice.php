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
namespace Webkul\MpAdvancedBookingSystem\Model\ResourceModel\ProductIndexerPrice;

use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\BasePriceModifier;
use Magento\Framework\Indexer\DimensionalIndexerInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Query\BaseFinalPrice;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructureFactory;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructure;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Indexer\Price\OptionsIndexerInterface;
use Magento\Framework\App\ObjectManager;

class HotelBookingPrice implements DimensionalIndexerInterface
{

    /**
     * @var TableMaintainer
     */
    private $tableMaintainer;

    /**
     * @var IndexTableStructureFactory
     */
    private $indexTableStructureFactory;

    /**
     * @var BaseFinalPrice
     */
    private $baseFinalPrice;

    /**
     * @var BasePriceModifier
     */
    private $basePriceModifier;

    /**
     * @var OptionsIndexerInterface
     */
    private $optionsIndexer;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var string
     */
    private $connectionName;

    /**
     * @var bool
     */
    private $fullReindexAction;
    
    /**
     * @param BaseFinalPrice $baseFinalPrice
     * @param IndexTableStructureFactory $indexTableStructureFactory
     * @param TableMaintainer $tableMaintainer
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param BasePriceModifier $basePriceModifier
     * @param bool $fullReindexAction
     * @param string $connectionName
     * @param OptionsIndexerInterface|null $optionsIndexer
     */
    public function __construct(
        BaseFinalPrice $baseFinalPrice,
        IndexTableStructureFactory $indexTableStructureFactory,
        TableMaintainer $tableMaintainer,
        \Magento\Framework\App\ResourceConnection $resource,
        BasePriceModifier $basePriceModifier,
        $fullReindexAction = false,
        $connectionName = 'indexer',
        ?OptionsIndexerInterface $optionsIndexer = null
    ) {
        $this->tableMaintainer = $tableMaintainer;
        $this->indexTableStructureFactory = $indexTableStructureFactory;
        $this->baseFinalPrice = $baseFinalPrice;
        $this->basePriceModifier = $basePriceModifier;
        $this->resource = $resource;
        $this->connectionName = $connectionName;
        $this->fullReindexAction = $fullReindexAction;
        $this->optionsIndexer = $optionsIndexer
            ?: ObjectManager::getInstance()->get(OptionsIndexerInterface::class);
    }

    /**
     * @inheritdoc
     *
     * @throws \Exception
     */
    public function executeByDimensions(array $dimensions, \Traversable $entityIds)
    {
        $this->tableMaintainer->createMainTmpTable($dimensions);

        $tableStructure = [
            'tableName' => $this->tableMaintainer->getMainTmpTable($dimensions),
            'entityField' => 'entity_id',
            'customerGroupField' => 'customer_group_id',
            'websiteField' => 'website_id',
            'taxClassField' => 'tax_class_id',
            'originalPriceField' => 'price',
            'finalPriceField' => 'final_price',
            'minPriceField' => 'min_price',
            'maxPriceField' => 'max_price',
            'tierPriceField' => 'tier_price',
        ];
        $temporaryPriceTable = $this->indexTableStructureFactory->create($tableStructure);
        $select = $this->baseFinalPrice->getQuery(
            $dimensions,
            \Webkul\MpAdvancedBookingSystem\Model\Product\Type\Hotelbooking::TYPE_CODE,
            iterator_to_array($entityIds)
        );
        $this->tableMaintainer->insertFromSelect(
            $select,
            $temporaryPriceTable->getTableName(),
            [
                "entity_id",
                "customer_group_id",
                "website_id",
                "tax_class_id",
                "price",
                "final_price",
                "min_price",
                "max_price",
                "tier_price",
            ]
        );

        $this->basePriceModifier->modifyPrice($temporaryPriceTable, iterator_to_array($entityIds));
        $this->applyHotelBookingOption($temporaryPriceTable, $dimensions, iterator_to_array($entityIds));
    }

    /**
     * Apply configurable option
     *
     * @param IndexTableStructure $temporaryPriceTable
     * @param array $dimensions
     * @param array $entityIds
     *
     * @return $this
     * @throws \Exception
     */
    private function applyHotelBookingOption(
        IndexTableStructure $temporaryPriceTable,
        array $dimensions,
        array $entityIds
    ) {
        $temporaryOptionsTableName = 'catalog_product_index_price_cfg_opt_temp';
        $this->getConnection()->createTemporaryTableLike(
            $temporaryOptionsTableName,
            $this->getTable('catalog_product_index_price_cfg_opt_tmp'),
            true
        );

        $indexTableName = $this->getMainTable($dimensions);
        $this->optionsIndexer->execute($indexTableName, $temporaryOptionsTableName, $entityIds);
        $this->updateTemporaryTable($temporaryPriceTable->getTableName(), $temporaryOptionsTableName);

        $this->getConnection()->delete($temporaryOptionsTableName);

        return $this;
    }
    
    /**
     * Get connection
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @throws \DomainException
     */
    private function getConnection(): \Magento\Framework\DB\Adapter\AdapterInterface
    {
        if ($this->connection === null) {
            $this->connection = $this->resource->getConnection($this->connectionName);
        }

        return $this->connection;
    }

    /**
     * Get table
     *
     * @param string $tableName
     * @return string
     */
    private function getTable($tableName)
    {
        return $this->resource->getTableName($tableName, $this->connectionName);
    }

    /**
     * Get main table
     *
     * @param array $dimensions
     * @return string
     */
    private function getMainTable($dimensions)
    {
        if ($this->fullReindexAction) {
            return $this->tableMaintainer->getMainReplicaTable($dimensions);
        }
        return $this->tableMaintainer->getMainTableByDimensions($dimensions);
    }

    /**
     * Update data in the catalog product price indexer temp table
     *
     * @param string $temporaryPriceTableName
     * @param string $temporaryOptionsTableName
     *
     * @return void
     */
    private function updateTemporaryTable(string $temporaryPriceTableName, string $temporaryOptionsTableName)
    {
        $table = ['i' => $temporaryPriceTableName];
        $selectForCrossUpdate = $this->getConnection()->select()->join(
            ['io' => $temporaryOptionsTableName],
            'i.entity_id = io.entity_id AND i.customer_group_id = io.customer_group_id' .
            ' AND i.website_id = io.website_id',
            []
        );
        // adds price of custom option, that was applied in DefaultPrice::_applyCustomOption
        $selectForCrossUpdate->columns(
            [
                'min_price' => new \Zend_Db_Expr('i.min_price - i.price + io.min_price'),
                'max_price' => new \Zend_Db_Expr('i.max_price - i.price + io.max_price'),
                'tier_price' => 'io.tier_price',
            ]
        );

        $query = $selectForCrossUpdate->crossUpdateFromSelect($table);
        $this->getConnection()->query($query);
    }
}
