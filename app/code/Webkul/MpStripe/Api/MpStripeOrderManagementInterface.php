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
namespace Webkul\MpStripe\Api;

/**
 * @api
 */
interface MpStripeOrderManagementInterface
{
    /**
     * ManageChargeSuccess function
     *
     * @param array $paymentDetails
     * @param Object $order
     * @param int $orderId
     * @param array $transfers
     * @param array $data
     * @return mixed
     */
    public function manageChargeSuccess($paymentDetails, $order, $orderId, $transfers, $data);

    /**
     * Add payment intent id to order
     *
     * @param string $orderId
     * @param string $paymentIntent
     * @return mixed
     */
    public function addPaymentIntentToOrder($orderId, $paymentIntent);
}
