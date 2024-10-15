<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpStripe
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpStripe\Model\Payment;

class PaymentIntentSucceeded
{
    /**
     * @param \Webkul\MpStripe\Api\MpStripeOrderManagementInterface $mpStripeOrderManager
     * @param \Webkul\Marketplace\Helper\Data $marketplaceData
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Webkul\MpStripe\Logger\StripeLogger $logger
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     */
    public function __construct(
        \Webkul\MpStripe\Api\MpStripeOrderManagementInterface $mpStripeOrderManager,
        \Webkul\Marketplace\Helper\Data $marketplaceData,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Webkul\MpStripe\Logger\StripeLogger $logger,
        \Magento\Sales\Model\OrderFactory $orderFactory
    ) {
        $this->mpStripeOrderManager = $mpStripeOrderManager;
        $this->marketplaceData = $marketplaceData;
        $this->objectManager = $objectManager;
        $this->logger = $logger;
        $this->orderFactory = $orderFactory;
    }

    /**
     * Manage payment intent succeeded
     *
     * @param array $data
     * @return void
     */
    public function process($data)
    {
        $collection = '';

        if ($this->marketplaceData->getConfigValue('mpsplitorder', 'mpsplitorder_enable')) {
            $mpsplitorderFactory = $this->objectManager->create(
                \Webkul\Mpsplitorder\Model\ResourceModel\Mpsplitorder\CollectionFactory::class
            );
            $orderIds = explode(',', $lastorderId);
            $collection = $mpsplitorderFactory->create()
                        ->addFieldToFilter("last_order_id", ["in" => $orderIds])
                        ->addFieldToSelect("order_ids");
        }
        if ($collection && $collection->getSize() > 0) {
            foreach ($collection as $orderdata) {
                $coll = $orderdata;
            }
            $colls = $coll['order_ids'];
            $orderIds = explode(",", $colls);
            foreach ($orderIds as $orderId) {
                $paymentIntent = $data['data']['object']['id'];
                $this->mpStripeOrderManager->addPaymentIntentToOrder($orderId, $paymentIntent);
            }
        } else {
            $orderId = $data['data']['object']['transfer_group'];
            $this->logger->critical('order id  '.$orderId);
            $paymentIntent = $data['data']['object']['id'];
            $this->mpStripeOrderManager->addPaymentIntentToOrder($orderId, $paymentIntent);
        }
    }
}
