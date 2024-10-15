<?php
/**
 * Affiliate User Interface.
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Api\Data;

interface UserInterface
{
    /**
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case.
     */
    const ENTITY_ID = 'entity_id';
    const CUSTOMER_ID = 'customer_id';
    const ENABLE = 'enable';
    const PAYPAL_ID = 'paypal_id';
    const GENERAL_COMMISSION = 'general_commission';
    const PAY_PER_CLICK = 'pay_per_click';
    const PAY_PER_UNIQUE_CLICK = 'pay_per_unique_click';
    const COMMISSION_TYPE = 'commission_type';
    const COMMISSION = 'commission';
    const BLOG_URL = 'blog_url';
    const CREATED_AT = 'created_at';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getEntityId();

    /**
     * set ID
     *
     * @return $this
     */
    public function setEntityId($entityId);

    /**
     * Get CustomerId
     * @return string
     */
    public function getCustomerId();

    /**
     * set CustomerId
     * @return $this
     */
    public function setCustomerId($customerId);

    /**
     * Get Enable
     * @return string
     */
    public function getEnable();

    /**
     * set Enable
     * @return $this
     */
    public function setEnable($enable);

    /**
     * Get PaypalId
     * @return string
     */
    public function getPaypalId();

    /**
     * set PaypalId
     * @return $this
     */
    public function setPaypalId($paypalId);

    /**
     * Get GeneralCommission
     * @return decimal
     */
    public function getGeneralCommission();

    /**
     * set GeneralCommission
     * @return $this
     */
    public function setGeneralCommission($generalCommission);

    /**
     * Get PayPerClick
     * @return decimal
     */
    public function getPayPerClick();

    /**
     * set PayPerClick
     * @return $this
     */
    public function setPayPerClick($payPerClick);

    /**
     * Get PayPerUniqueClick
     * @return int
     */
    public function getPayPerUniqueClick();

    /**
     * set PayPerUniqueClick
     * @return $this
     */
    public function setPayPerUniqueClick($payPerUniqueClick);

    /**
     * Get CommissionType
     * @return varchar
     */
    public function getCommissionType();

    /**
     * set CommissionType
     * @return $this
     */
    public function setCommissionType($commissionType);

    /**
     * Get Commission
     * @return varchar
     */
    public function getCommission();

    /**
     * set Commission
     * @return $this
     */
    public function setCommission($commission);

    /**
     * Get BlogUrl
     * @return varchar
     */
    public function getBlogUrl();

    /**
     * set BlogUrl
     * @return $this
     */
    public function setBlogUrl($blogUrl);

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
