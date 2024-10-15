<?php
/**
 * Affiliate UserBalance Interface.
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Api\Data;

interface UserBalanceInterface
{
    /**
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case.
     */
    const ID = 'entity_id';
    const AFF_CUSTOMER_ID = 'aff_customer_id';
    const AFF_NAME = 'aff_name';
    const CLICKS = 'clicks';
    const UNIQUE_CLICKS = 'unique_clicks';
    const PAYMENT_METHOD = 'payment_method';
    const BALANCE_AMOUNT = 'balance_amount';
    const CREATED_AT = 'created_at';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * set ID
     *
     * @return $this
     */
    public function setId($entityId);

    /**
     * Get AffCustomerId
     * @return string
     */
    public function getAffCustomerId();

    /**
     * set AffCustomerId
     * @return $this
     */
    public function setAffCustomerId($affCustomerId);

    /**
     * Get AffName
     * @return string
     */
    public function getAffName();

    /**
     * set AffName
     * @return $this
     */
    public function setAffName($affName);

    /**
     * Get Clicks
     * @return string
     */
    public function getClicks();

    /**
     * set Clicks
     * @return $this
     */
    public function setClicks($clicks);

    /**
     * Get UniqueClicks
     * @return string
     */
    public function getUniqueClicks();

    /**
     * set UniqueClicks
     * @return $this
     */
    public function setUniqueClicks($uniqueClicks);

    /**
     * Get PaymentMethod
     * @return decimal
     */
    public function getPaymentMethod();

    /**
     * set PaymentMethod
     * @return $this
     */
    public function setPaymentMethod($paymentMethod);

    /**
     * Get BalanceAmount
     * @return decimal
     */
    public function getBalanceAmount();

    /**
     * set BalanceAmount
     * @return $this
     */
    public function setBalanceAmount($balanceAmount);

    /**
     * Get CreatedAt.
     * @return string
     */
    public function getCreatedAt();

    /**
     * set CreatedAt.
     * @return $this
     */
    public function setCreatedAt($createdAt);
}
