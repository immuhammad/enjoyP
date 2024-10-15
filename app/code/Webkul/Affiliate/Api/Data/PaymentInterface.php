<?php
/**
 * Affiliate Payment Interface.
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Api\Data;

interface PaymentInterface
{
    /**
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case.
     */
    const ID = 'entity_id';
    const TRANSACTION_ID = 'transaction_id';
    const TRANSACTION_EMAIL = 'transaction_email';
    const IPN_TRANSACTION_ID = 'ipn_transaction_id';
    const AFF_CUSTOMER_ID = 'aff_customer_id';
    const TRANSACTION_AMOUNT = 'transaction_amount';
    const PAYMENT_METHOD = 'payment_method';
    const TRANSACTION_STATUS = 'transaction_status';
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
     * Get TransactionId
     * @return string
     */
    public function getTransactionId();

    /**
     * set TransactionId
     * @return $this
     */
    public function setTransactionId($transactionId);

    /**
     * Get TransactionEmail
     * @return string
     */
    public function getTransactionEmail();

    /**
     * set TransactionEmail
     * @return $this
     */
    public function setTransactionEmail($transactionEmail);

    /**
     * Get IpnTransactionId
     * @return string
     */
    public function getIpnTransactionId();

    /**
     * set IpnTransactionId
     * @return $this
     */
    public function setIpnTransactionId($ipnTransactionId);

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
     * Get TransactionAmount
     * @return decimal
     */
    public function getTransactionAmount();

    /**
     * set TransactionAmount
     * @return $this
     */
    public function setTransactionAmount($transactionAmount);

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
     * Get TransactionStatus
     * @return int
     */
    public function getTransactionStatus();

    /**
     * set TransactionStatus
     * @return $this
     */
    public function setTransactionStatus($transactionStatus);

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
