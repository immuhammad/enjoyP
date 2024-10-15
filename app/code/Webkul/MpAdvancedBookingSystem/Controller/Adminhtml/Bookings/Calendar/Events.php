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
namespace Webkul\MpAdvancedBookingSystem\Controller\Adminhtml\Bookings\Calendar;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Webkul\MpAdvancedBookingSystem\Model\BookedFactory as BookedOrdersFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;

class Events extends Action
{
    /**
     * Registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;
    
    /**
     * @var BookedOrdersFactory
     */
    protected $_bookedOrdersFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $eavAttribute;

    /**
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param BookedOrdersFactory $bookedOrdersFactory
     * @param ResourceConnection $resource
     * @param Attribute $eavAttribute
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        BookedOrdersFactory $bookedOrdersFactory,
        ResourceConnection $resource,
        Attribute $eavAttribute
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_bookedOrdersFactory = $bookedOrdersFactory;
        $this->_resource = $resource;
        $this->eavAttribute = $eavAttribute;
        parent::__construct($context);
    }

    /**
     * Tree json action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $params = $this->getRequest()->getParams();
        try {
            $events = [];
            if (!empty($params['start']) && !empty($params['end'])) {
                $start =  date("Y-m-d", $params['start']);
                $end = date("Y-m-d", $params['end']);
                $proAttId = $this->eavAttribute->getIdByCode('catalog_product', 'name');
                $orderGridTable = $this->_resource->getTableName('sales_order_grid');
                $catalogProductEntityVarchar = $this->_resource->getTableName('catalog_product_entity_varchar');
                $sql = $catalogProductEntityVarchar.' as cpev';
                $cond = 'main_table.product_id = cpev.entity_id';
                $fields = ['product_name' => 'value'];
                $collection = $this->_bookedOrdersFactory->create()->getCollection()
                    ->addFieldToFilter('booking_from', ['gteq' => $start])
                    ->addFieldToFilter('booking_too', ['lteq' => $end]);

                $collection->getSelect()
                    ->join($sql, $cond, $fields)
                    ->where('cpev.store_id = 0 AND cpev.attribute_id = '.$proAttId);

                $collection->addFilterToMap('product_name', 'cpev.value');

                $sql = $orderGridTable.' as ogt';
                $cond = 'main_table.order_id = ogt.entity_id';
                $fields = [
                    'increment_id' => 'increment_id',
                    'customer_email' => 'main_table.customer_email',
                    'status' => 'ogt.status'
                ];

                $collection->getSelect()
                    ->join($sql, $cond, $fields);
                $collection->addFilterToMap('increment_id', 'ogt.increment_id');

                foreach ($collection as $order) {
                    $events[] = [
                        'title' => $order->getProductName(),
                        'start' => $order->getBookingFrom(),
                        'end' => $order->getBookingToo(),
                        'incrementId' => $order->getIncrementId(),
                        'orderId' => $order->getOrderId(),
                        'customerEmail' => $order->getCustomerEmail(),
                        'status' => ucfirst($order->getStatus())
                    ];
                }
            }

            $result = ['error' => false, 'events' => $events];
        } catch (\Exception $e) {
            $result = ['error' => true, 'events' => $events, 'message' => $e->getMessage()];
        }
        $resultJson->setData($result);

        return $resultJson;
    }
}
