<?php

/**
 * Affiliate Clicks Model.
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Model;

use Webkul\Affiliate\Api\Data\ClicksInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Clicks extends \Magento\Framework\Model\AbstractModel implements ClicksInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'wk_affiliate_clicks';

    /**
     * @var string
     */
    protected $_cacheTag = 'wk_affiliate_clicks';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'wk_affiliate_clicks';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Affiliate\Model\ResourceModel\Clicks::class);
    }
    /**
     * Get Id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Set Id.
     */
    public function setId($entityId)
    {
        return $this->setData(self::ID, $entityId);
    }

    /**
     * Get HitType.
     *
     * @return varchar
     */
    public function getHitType()
    {
        return $this->getData(self::HIT_TYPE);
    }

    /**
     * Set HitType.
     */
    public function setHitType($hitType)
    {
        return $this->setData(self::HIT_TYPE, $hitType);
    }

    /**
     * Get HitId.
     *
     * @return varchar
     */
    public function getHitId()
    {
        return $this->getData(self::HIT_ID);
    }

    /**
     * Set HitId.
     */
    public function setHitId($hitId)
    {
        return $this->setData(self::HIT_ID, $hitId);
    }

    /**
     * Get AffCustomerId.
     *
     * @return int
     */
    public function getAffCustomerId()
    {
        return $this->getData(self::AFF_CUSTOMER_ID);
    }

    /**
     * Set AffCustomerId.
     */
    public function setAffCustomerId($affCustomerId)
    {
        return $this->setData(self::AFF_CUSTOMER_ID, $affCustomerId);
    }

    /**
     * Get Commission.
     *
     * @return Decimal
     */
    public function getCommission()
    {
        return $this->getData(self::COMMISSION);
    }

    /**
     * Set Commission.
     */
    public function setCommission($commission)
    {
        return $this->setData(self::COMMISSION, $commission);
    }

    /**
     * Get CustomerIp.
     *
     * @return varchar
     */
    public function getCustomerIp()
    {
        return $this->getData(self::CUSTOMER_IP);
    }

    /**
     * Set CustomerIp.
     */
    public function setCustomerIp($customerIp)
    {
        return $this->setData(self::CUSTOMER_IP, $customerIp);
    }

    /**
     * Get CustomerDomain.
     *
     * @return varchar
     */
    public function getCustomerDomain()
    {
        return $this->getData(self::CUSTOMER_DOMAIN);
    }

    /**
     * Set CustomerDomain.
     */
    public function setCustomerDomain($customerDomain)
    {
        return $this->setData(self::CUSTOMER_DOMAIN, $customerDomain);
    }

    /**
     * Get ComeFrom.
     *
     * @return varchar
     */
    public function getComeFrom()
    {
        return $this->getData(self::COME_FROM);
    }

    /**
     * Set ComeFrom.
     */
    public function setComeFrom($comeFrom)
    {
        return $this->setData(self::COME_FROM, $comeFrom);
    }
   
    /**
     * Get CreatedAt.
     *
     * @return varchar
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set CreatedAt.
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
}
