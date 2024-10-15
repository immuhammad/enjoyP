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

interface ClicksInterface
{
    /**
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case.
     */
    const ID = 'entity_id';
    const HIT_TYPE = 'hit_type';
    const HIT_ID = 'hit_id';
    const AFF_CUSTOMER_ID = 'aff_customer_id';
    const COMMISSION = 'commission';
    const CUSTOMER_IP = 'customer_ip';
    const CUSTOMER_DOMAIN = 'customer_domain';
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
     * Get HitType
     * @return string
     */
    public function getHitType();

    /**
     * set HitType
     * @return $this
     */
    public function setHitType($hitType);

    /**
     * Get HitId
     * @return string
     */
    public function getHitId();

    /**
     * set HitId
     * @return $this
     */
    public function setHitId($hitType);

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
     * Get CustomerIp
     * @return varchar
     */
    public function getCustomerIp();

    /**
     * set CustomerIp
     * @return $this
     */
    public function setCustomerIp($customerIp);

    /**
     * Get CustomerDomain
     * @return varchar
     */
    public function getCustomerDomain();

    /**
     * set CustomerDomain
     * @return $this
     */
    public function setCustomerDomain($customerDomain);

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
