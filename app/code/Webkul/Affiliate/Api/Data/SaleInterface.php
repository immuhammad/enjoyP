<?php
/**
 * Affiliate Sale Interface.
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Api\Data;

interface SaleInterface
{
    /**
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case.
     */
    const ID = 'entity_id';
    const PRODUCT_ID = 'product_id';
    const AFF_CUSTOMER_ID = 'aff_customer_id';
    const ORDER_STATUS = 'order_status';
    const PRICE = 'price';
    const COMMISSION = 'commission';
    const AFFILIATE_STATUS = 'affiliate_status';
    const COME_FROM = 'come_from';
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
     * Get ProductId
     * @return string
     */
    public function getProductId();

    /**
     * set ProductId
     * @return $this
     */
    public function setProductId($productId);

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
     * Get OrderStatus
     * @return string
     */
    public function getOrderStatus();

    /**
     * set OrderStatus
     * @return $this
     */
    public function setOrderStatus($orderStatus);

    /**
     * Get Price
     * @return decimal
     */
    public function getPrice();

    /**
     * set Price
     * @return $this
     */
    public function setPrice($price);

    /**
     * Get Commission
     * @return decimal
     */
    public function getCommission();

    /**
     * set Commission
     * @return $this
     */
    public function setCommission($commission);

    /**
     * Get AffiliateStatus
     * @return int
     */
    public function getAffiliateStatus();

    /**
     * set AffiliateStatus
     * @return $this
     */
    public function setAffiliateStatus($affiliateStatus);

    /**
     * Get ComeFrom
     * @return varchar
     */
    public function getComeFrom();

    /**
     * set ComeFrom
     * @return $this
     */
    public function setComeFrom($comeFrom);

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
