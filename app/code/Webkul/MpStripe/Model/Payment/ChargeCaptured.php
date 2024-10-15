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

use Webkul\MpStripe\Model\Source\PaymentAction;

class ChargeCaptured
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
     * Manage payment charge captured
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
        $paymentAction = $this->helper->getConfigValue('stripe_payment_action');

        if ($collection && $collection->getSize() > 0) {
            foreach ($collection as $orderdata) {
                $coll = $orderdata;
            }
            $colls = $coll['order_ids'];
            $ordersid = explode(",", $colls) ;
            foreach ($ordersid as $orderId) {
                $order = $this->orderFactory->create()->load($orderId);
                if ($order->canInvoice() &&
                    $paymentAction == PaymentAction::STRIPE_ACTION_AUTHORIZE
                ) {
                    $finalCart = $this->helper->getFinalCart($order);
                    $paymentDetails = $this->helper->getCheckoutFinalData($finalCart, $order);
                    $paymentIntent = $data['data']['object']['payment_intent'];
                    $this->mpStripeOrderManager->addPaymentIntentToOrder($orderId, $paymentIntent);
                    $this->helper->setUpDefaultDetails();
                    $transfers = \Stripe\Transfer::all(['transfer_group' => $orderId]);

                    $this->mpStripeOrderManager->manageChargeSuccess(
                        $paymentDetails,
                        $order,
                        $orderId,
                        $transfers,
                        $data
                    );
                    
                }
            }
        } else {

            $orderId = $data['data']['object']['transfer_group'];
            $order = $this->orderFactory->create()->load($orderId);
            $paymentIntent = $data['data']['object']['payment_intent'];
            $this->mpStripeOrderManager->addPaymentIntentToOrder($orderId, $paymentIntent);
            if ($order->canInvoice() && $paymentAction == PaymentAction::STRIPE_ACTION_AUTHORIZE) {
                $finalCart = $this->helper->getFinalCart($order);
                $paymentDetails = $this->helper->getCheckoutFinalData($finalCart, $order);
                $this->helper->setUpDefaultDetails();
                $transfers = \Stripe\Transfer::all(['transfer_group' => $orderId]);
                $this->mpStripeOrderManager->manageChargeSuccess(
                    $paymentDetails,
                    $order,
                    $orderId,
                    $transfers,
                    $data
                );
            }
        }
    }
}
