<?php
/**
 * Affiliate UserBalance Model.
 *
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Model;

use Webkul\Affiliate\Api\Data\UserBalanceInterface;
use Magento\Framework\DataObject\IdentityInterface;

class UserBalance extends \Magento\Framework\Model\AbstractModel implements UserBalanceInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'wk_affiliate_user_balance';

    /**
     * @var string
     */
    protected $_cacheTag = 'wk_affiliate_user_balance';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'wk_affiliate_user_balance';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Affiliate\Model\ResourceModel\UserBalance::class);
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
     * Get AffCustomerId
     * @return varchar
     */
    public function getAffCustomerId()
    {
        return $this->getData(self::AFF_CUSTOMER_ID);
    }

    /**
     * Set AffCustomerId
     */
    public function setAffCustomerId($affCustomerId)
    {
        return $this->setData(self::AFF_CUSTOMER_ID, $affCustomerId);
    }

    /**
     * Get AffName
     * @return varchar
     */
    public function getAffName()
    {
        return $this->getData(self::AFF_NAME);
    }

    /**
     * Set AffName
     */
    public function setAffName($affName)
    {
        return $this->setData(self::AFF_NAME, $affName);
    }

    /**
     * Get Clicks
     * @return int
     */
    public function getClicks()
    {
        return $this->getData(self::CLICKS);
    }

    /**
     * Set Clicks.
     */
    public function setClicks($clicks)
    {
        return $this->setData(self::CLICKS, $clicks);
    }

    /**
     * Get UniqueClicks.
     *
     * @return int
     */
    public function getUniqueClicks()
    {
        return $this->getData(self::UNIQUE_CLICKS);
    }

    /**
     * Set UniqueClicks.
     */
    public function setUniqueClicks($uniqueClicks)
    {
        return $this->setData(self::UNIQUE_CLICKS, $uniqueClicks);
    }

    /**
     * Get PaymentMethod
     * @return decimal
     */
    public function getPaymentMethod()
    {
        return $this->getData(self::PAYMENT_METHOD);
    }

    /**
     * set PaymentMethod
     * @return $this
     */
    public function setPaymentMethod($paymentMethod)
    {
        return $this->setData(self::PAYMENT_METHOD, $paymentMethod);
    }

    /**
     * Get BalanceAmount.
     *
     * @return varchar
     */
    public function getBalanceAmount()
    {
        return $this->getData(self::BALANCE_AMOUNT);
    }

    /**
     * Set BalanceAmount.
     */
    public function setBalanceAmount($balanceAmount)
    {
        return $this->setData(self::BALANCE_AMOUNT, $balanceAmount);
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
