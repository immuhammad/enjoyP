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
namespace Webkul\MpRmaSystem\Model\ResourceModel;

class Details extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
     /**
      * @var \Magento\Framework\App\ResourceConnection
      */
    protected $resourceConnection;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory
     */
    protected $orderItemCollection;
    
    /**
     * Prefix for resources that will be used in this resource model
     *
     * @var string
     */
    protected $connectionName = \Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION;
    
    /**
     * Initialize Dependencies
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollection
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollection,
        $connectionName = null
    ) {
        $this->resourceConnection       = $resourceConnection;
        $this->orderItemCollection      = $orderItemCollection;
        if ($connectionName !== null) {
            $this->connectionName       = $connectionName;
        }
        parent::__construct($context, $connectionName);
    }
    
    /**
     * Init
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('marketplace_rma_details', 'id');
    }
    
    /**
     * Load an object using 'identifier' field if there's no field specified and value is not numeric
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param mixed $value
     * @param string $field
     * @return $this
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        if (!is_numeric($value) && $field===null) {
            $field = 'identifier';
        }
        return parent::load($object, $value, $field);
    }
    /**
     * Get Product Details of Requested RMA
     *
     * @param int $rmaId
     *
     * @return collection object
     */
    public function getRmaProductDetails($rmaId)
    {
        $resource = $this->resourceConnection;
        $collection = $this->orderItemCollection->create();
        $tableName = $resource->getTableName('marketplace_rma_items');
        $sql = "rma_items.item_id = main_table.item_id ";
        $collection->getSelect()->join(['rma_items' => $tableName], $sql, ['*']);
        $tableName = $resource->getTableName('marketplace_rma_details');
        $sql = " rma_details.id = rma_items.rma_id ";
        $collection->getSelect()->join(['rma_details' => $tableName], $sql, ['order_id']);
        $collection->getSelect()->where("rma_details.id = ".$rmaId);
        $collection->addFilterToMap("qty", "rma_items.qty");
        $collection->addFilterToMap("product_id", "rma_items.product_id");
        $collection->addFilterToMap("price", "rma_items.price");
        return $collection;
    }
}
