<?php

/**
 * Affiliate User Model.
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Model;

use Webkul\Affiliate\Api\Data\UserInterface;
use Magento\Framework\DataObject\IdentityInterface;

class User extends \Magento\Framework\Model\AbstractModel implements UserInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'wk_affiliate_user';

    /**
     * @var string
     */
    protected $_cacheTag = 'wk_affiliate_user';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'wk_affiliate_user';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Affiliate\Model\ResourceModel\User::class);
    }

    /**
     * Get Id.
     *
     * @return int
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Set Id.
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * Get CustomerId.
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * Set CustomerId.
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Get Enable.
     *
     * @return varchar
     */
    public function getEnable()
    {
        return $this->getData(self::ENABLE);
    }

    /**
     * Set Enable.
     */
    public function setEnable($enable)
    {
        return $this->setData(self::ENABLE, $enable);
    }

    /**
     * Get PaypalId.
     *
     * @return varchar
     */
    public function getPaypalId()
    {
        return $this->getData(self::PAYPAL_ID);
    }

    /**
     * Set PaypalId.
     */
    public function setPaypalId($paypalId)
    {
        return $this->setData(self::PAYPAL_ID, $paypalId);
    }

    /**
     * Get GeneralCommission.
     *
     * @return decimal
     */
    public function getGeneralCommission()
    {
        return $this->getData(self::GENERAL_COMMISSION);
    }

    /**
     * Set GeneralCommission.
     */
    public function setGeneralCommission($generalCommission)
    {
        return $this->setData(self::GENERAL_COMMISSION, $generalCommission);
    }

    /**
     * Get PayPerClick
     *
     * @return int
     */
    public function getPayPerClick()
    {
        return $this->getData(self::PAY_PER_CLICK);
    }

    /**
     * Set PayPerClick.
     */
    public function setPayPerClick($payPerClick)
    {
        return $this->setData(self::PAY_PER_CLICK, $payPerClick);
    }

    /**
     * Get PayPerUniqueClick.
     *
     * @return int
     */
    public function getPayPerUniqueClick()
    {
        return $this->getData(self::PAY_PER_UNIQUE_CLICK);
    }

    /**
     * Set PayPerUniqueClick.
     */
    public function setPayPerUniqueClick($payPerUniqueClick)
    {
        return $this->setData(self::PAY_PER_UNIQUE_CLICK, $payPerUniqueClick);
    }

    /**
     * Get CommissionType.
     *
     * @return varchar
     */
    public function getCommissionType()
    {
        return $this->getData(self::COMMISSION_TYPE);
    }

    /**
     * Set CommissionType.
     */
    public function setCommissionType($commissionType)
    {
        return $this->setData(self::COMMISSION_TYPE, $commissionType);
    }
    
    /**
     * Get Commission.
     * @return varchar
     */
    public function getCommission()
    {
        return $this->getData(self::COMMISSION);
    }

    /**
     * Set CommissionType.
     */
    public function setCommission($commission)
    {
        return $this->setData(self::COMMISSION, $commission);
    }

    /**
     * Get BlogUrl.
     * @return varchar
     */
    public function getBlogUrl()
    {
        return $this->getData(self::BLOG_URL);
    }

    /**
     * Set BlogUrl.
     */
    public function setBlogUrl($blogUrl)
    {
        return $this->setData(self::BLOG_URL, $blogUrl);
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
