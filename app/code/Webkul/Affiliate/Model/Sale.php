<?php

/**
 * Affiliate Sale Model.
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Model;

use Webkul\Affiliate\Api\Data\SaleInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Sale extends \Magento\Framework\Model\AbstractModel implements SaleInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'wk_affiliate_sale';

    /**
     * @var string
     */
    protected $_cacheTag = 'wk_affiliate_sale';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'wk_affiliate_sale';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Affiliate\Model\ResourceModel\Sale::class);
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
     * Get ProductId.
     *
     * @return int
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * Set ProductId.
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * Get AffCustomerId.
     *
     * @return varchar
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
     * Get OrderStatus.
     *
     * @return varchar
     */
    public function getOrderStatus()
    {
        return $this->getData(self::ORDER_STATUS);
    }

    /**
     * Set OrderStatus.
     */
    public function setOrderStatus($orderStatus)
    {
        return $this->setData(self::ORDER_STATUS, $orderStatus);
    }

    /**
     * Get Price.
     *
     * @return decimal
     */
    public function getPrice()
    {
        return $this->getData(self::PRICE);
    }

    /**
     * Set Price.
     */
    public function setPrice($price)
    {
        return $this->setData(self::PRICE, $price);
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
     * Get AffiliateStatus.
     *
     * @return int
     */
    public function getAffiliateStatus()
    {
        return $this->getData(self::AFFILIATE_STATUS);
    }

    /**
     * Set AffiliateStatus.
     */
    public function setAffiliateStatus($affiliateStatus)
    {
        return $this->setData(self::AFFILIATE_STATUS, $affiliateStatus);
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
