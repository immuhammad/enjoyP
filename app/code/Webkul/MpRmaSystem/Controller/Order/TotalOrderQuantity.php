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
namespace Webkul\MpRmaSystem\Controller\Order;
 
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class TotalOrderQuantity extends Action
{
    /**
     * @var Magento\Framework\Controller\ResultFactory $resultFactory;
     */
    protected $resultFactory;

    /**
     * @var \Webkul\MpRmaSystem\Helper\Data $helper
     */
    protected $helper;

    /**
     * Initialize Dependencies
     *
     * @param Context $context
     * @param ResultFactory $resultFactory
     * @param \Webkul\MpRmaSystem\Helper\Data $helper
     * @return void
     */

    public function __construct(
        Context $context,
        ResultFactory $resultFactory,
        \Webkul\MpRmaSystem\Helper\Data $helper
    ) {
 
        $this->resultFactory = $resultFactory;
        $this->helper        = $helper;
        parent::__construct($context);
    }
    /**
     * Checking total order quantity
     *
     * @return \Magento\Framework\Controller\ResultFactory::TYPE_JSON
     */
    public function execute()
    {
        $data = [
            "status" => false,
            "message" => __("Invalid request.")
        ];
        $orderId = $this->getRequest()->getParams('order_id');
        if (!$orderId) {
            $data = [
                "status" => false,
                "message" => __("This order is no longer valid.")
            ];
        }
        $order = $this->helper->getOrder($orderId);
        $totalOrderQty = $order->getTotalQtyOrdered();
        if ($totalOrderQty) {
            $data = [
                "status" => true,
                "message" => __("Total Qty Ordered"),
                "totalOrderQty" => $totalOrderQty
            ];
        }
        $response = $this->resultFactory
        ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
        ->setData($data);
        return $response;
    }
}
