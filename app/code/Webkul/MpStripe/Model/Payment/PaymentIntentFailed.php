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

use Magento\Sales\Model\Order;

class PaymentIntentFailed
{
    /**
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     */
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory
    ) {
        $this->orderFactory = $orderFactory;
    }

    /**
     * Manage payment intent failed
     *
     * @param array $data
     * @return void
     */
    public function process($data)
    {
        $paymentIntent = $data['data']['object']['id'];
        $order = $this->orderFactory->create()
            ->getCollection()
            ->addFieldToFilter('stripe_payment_intent', $paymentIntent)->getFirstItem();
        $orderState = Order::STATE_PENDING_PAYMENT;
        $order->setState($orderState)->setStatus(Order::STATE_PENDING_PAYMENT);
        $order->save();
    }
}
