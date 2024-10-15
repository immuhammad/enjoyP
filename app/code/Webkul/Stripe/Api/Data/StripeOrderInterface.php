<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_Stripe
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */


namespace Webkul\Stripe\Api\Data;

/**
 * StripeOrder Data Interface
 */
interface StripeOrderInterface
{

    public const ENTITY_ID = 'entity_id';

    public const QUOTE_ID = 'quote_id';

    public const PAYMENT_INTENT = 'payment_intent';

    public const STATUS = 'status';
    /**
     * Set EntityId
     *
     * @param int $entityId
     * @return Webkul\Stripe\Api\Data\StripeOrderInterface
     */
    public function setEntityId($entityId);
    /**
     * Get EntityId
     *
     * @return int
     */
    public function getEntityId();
    /**
     * Set QuoteId
     *
     * @param int $quoteId
     * @return Webkul\Stripe\Api\Data\StripeOrderInterface
     */
    public function setQuoteId($quoteId);
    /**
     * Get QuoteId
     *
     * @return int
     */
    public function getQuoteId();
    /**
     * Set PaymentIntent
     *
     * @param string $paymentIntent
     * @return Webkul\Stripe\Api\Data\StripeOrderInterface
     */
    public function setPaymentIntent($paymentIntent);
    /**
     * Get PaymentIntent
     *
     * @return string
     */
    public function getPaymentIntent();
    /**
     * Set Status
     *
     * @param string $status
     * @return Webkul\Stripe\Api\Data\StripeOrderInterface
     */
    public function setStatus($status);
    /**
     * Get Status
     *
     * @return string
     */
    public function getStatus();
}
