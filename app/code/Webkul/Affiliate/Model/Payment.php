<?php

/**
 * Affiliate Payment Model.
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Model;

use Webkul\Affiliate\Api\Data\PaymentInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Payment extends \Magento\Framework\Model\AbstractModel implements PaymentInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'wk_affiliate_payment';

    /**
     * @var string
     */
    protected $_cacheTag = 'wk_affiliate_payment';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'wk_affiliate_payment';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Affiliate\Model\ResourceModel\Payment::class);
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
     * Get TransactionId.
     *
     * @return varchar
     */
    public function getTransactionId()
    {
        return $this->getData(self::TRANSACTION_ID);
    }

    /**
     * Set TransactionId.
     */
    public function setTransactionId($transactionId)
    {
        return $this->setData(self::TRANSACTION_ID, $transactionId);
    }

    /**
     * Get TransactionEmail.
     * @return varchar
     */
    public function getTransactionEmail()
    {
        return $this->getData(self::TRANSACTION_EMAIL);
    }

    /**
     * Set TransactionEmail.
     */
    public function setTransactionEmail($transactionEmail)
    {
        return $this->setData(self::TRANSACTION_EMAIL, $transactionEmail);
    }

    /**
     * Get IpnTransactionId.
     * @return varchar
     */
    public function getIpnTransactionId()
    {
        return $this->getData(self::IPN_TRANSACTION_ID);
    }

    /**
     * Set IpnTransactionId.
     */
    public function setIpnTransactionId($ipnTransactionId)
    {
        return $this->setData(self::IPN_TRANSACTION_ID, $ipnTransactionId);
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
     * Get TransactionAmount.
     *
     * @return int
     */
    public function getTransactionAmount()
    {
        return $this->getData(self::TRANSACTION_AMOUNT);
    }

    /**
     * Set TransactionAmount.
     */
    public function setTransactionAmount($transactionAmount)
    {
        return $this->setData(self::TRANSACTION_AMOUNT, $transactionAmount);
    }

    /**
     * Get PaymentMethod.
     *
     * @return varchar
     */
    public function getPaymentMethod()
    {
        return $this->getData(self::PAYMENT_METHOD);
    }

    /**
     * Set PaymentMethod.
     */
    public function setPaymentMethod($paymentMethod)
    {
        return $this->setData(self::PAYMENT_METHOD, $paymentMethod);
    }

    /**
     * Get TransactionStatus.
     *
     * @return varchar
     */
    public function getTransactionStatus()
    {
        return $this->getData(self::TRANSACTION_STATUS);
    }

    /**
     * Set TransactionStatus.
     */
    public function setTransactionStatus($transactionStatus)
    {
        return $this->setData(self::TRANSACTION_STATUS, $transactionStatus);
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
