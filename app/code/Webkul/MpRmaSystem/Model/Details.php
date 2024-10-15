<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpRmaSystem
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpRmaSystem\Model;

use Webkul\MpRmaSystem\Api\Data\DetailsInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * MpRmaSystem Details Model.
 *
 * @method \Webkul\MpRmaSystem\Model\ResourceModel\Details _getResource()
 * @method \Webkul\MpRmaSystem\Model\ResourceModel\Details getResource()
 */
class Details extends AbstractModel implements DetailsInterface, IdentityInterface
{
    /**
     * No route page id.
     */
    public const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * MpRmaSystem Details cache tag.
     */
    public const CACHE_TAG = 'mprmasystem_details';

    /**
     * @var string
     */
    protected $_cacheTag = 'mprmasystem_details';

    /**
     * @var string
     */
    protected $_eventPrefix = 'mprmasystem_details';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpRmaSystem\Model\ResourceModel\Details::class);
    }

    /**
     * Load object data.
     *
     * @param int|null $id
     * @param string   $field
     *
     * @return $this
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteDetails();
        }

        return parent::load($id, $field);
    }

    /**
     * Load No-Route Details.
     *
     * @return \Webkul\MpRmaSystem\Model\Details
     */
    public function noRouteDetails()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Get identities.
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }

    /**
     * Get ID.
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ENTITY_ID);
    }

    /**
     * Set ID.
     *
     * @param int $id
     *
     * @return \Webkul\MpRmaSystem\Api\Data\DetailsInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Get ORDER_ID.
     *
     * @return int
     */
    public function getOrderId()
    {
        return parent::getData(self::ORDER_ID);
    }

    /**
     * Set ORDER_ID.
     *
     * @param int $orderId
     *
     * @return \Webkul\MpRmaSystem\Api\Data\DetailsInterface
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * Get SELLER_ID.
     *
     * @return int|null
     */
    public function getSellerId()
    {
        return parent::getData(self::SELLER_ID);
    }

    /**
     * Set SELLER_ID.
     *
     * @param int $sellerId
     *
     * @return \Webkul\MpRmaSystem\Api\Data\DetailsInterface
     */
    public function setSellerId($sellerId)
    {
        return $this->setData(self::SELLER_ID, $sellerId);
    }

    /**
     * Get CUSTOMER_ID.
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return parent::getData(self::CUSTOMER_ID);
    }

    /**
     * Set CUSTOMER_ID.
     *
     * @param int $customerId
     *
     * @return \Webkul\MpRmaSystem\Api\Data\DetailsInterface
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Get CUSTOMER_EMAIL.
     *
     * @return string|null
     */
    public function getCustomerEmail()
    {
        return parent::getData(self::CUSTOMER_EMAIL);
    }

    /**
     * Set CUSTOMER_EMAIL.
     *
     * @param string $customerEmail
     *
     * @return \Webkul\MpRmaSystem\Api\Data\DetailsInterface
     */
    public function setCustomerEmail($customerEmail)
    {
        return $this->setData(self::CUSTOMER_EMAIL, $customerEmail);
    }

    /**
     * Get STATUS.
     *
     * @return int|null
     */
    public function getStatus()
    {
        return parent::getData(self::STATUS);
    }

    /**
     * Set STATUS.
     *
     * @param int $status
     *
     * @return \Webkul\MpRmaSystem\Api\Data\DetailsInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get ORDER_STATUS.
     *
     * @return int|null
     */
    public function getOrderStatus()
    {
        return parent::getData(self::ORDER_STATUS);
    }

    /**
     * Set ORDER_STATUS.
     *
     * @param int $orderStatus
     *
     * @return \Webkul\MpRmaSystem\Api\Data\DetailsInterface
     */
    public function setOrderStatus($orderStatus)
    {
        return $this->setData(self::ORDER_STATUS, $orderStatus);
    }

    /**
     * Get SELLER_STATUS.
     *
     * @return int|null
     */
    public function getSellerStatus()
    {
        return parent::getData(self::SELLER_STATUS);
    }

    /**
     * Set ORDER_STATUS.
     *
     * @param int $sellerStatus
     *
     * @return \Webkul\MpRmaSystem\Api\Data\DetailsInterface
     */
    public function setSellerStatus($sellerStatus)
    {
        return $this->setData(self::SELLER_STATUS, $sellerStatus);
    }

    /**
     * Get FINAL_STATUS.
     *
     * @return int|null
     */
    public function getFinalStatus()
    {
        return parent::getData(self::FINAL_STATUS);
    }

    /**
     * Set FINAL_STATUS.
     *
     * @param int $finalStatus
     *
     * @return \Webkul\MpRmaSystem\Api\Data\DetailsInterface
     */
    public function setFinalStatus($finalStatus)
    {
        return $this->setData(self::FINAL_STATUS, $finalStatus);
    }

    /**
     * Get PRODUCT_ID.
     *
     * @return int|null
     */
    public function getProductId()
    {
        return parent::getData(self::PRODUCT_ID);
    }

    /**
     * Set PRODUCT_ID.
     *
     * @param int $productId
     *
     * @return \Webkul\MpRmaSystem\Api\Data\DetailsInterface
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * Get RESOLUTION_TYPE.
     *
     * @return int|null
     */
    public function getResolutionType()
    {
        return parent::getData(self::RESOLUTION_TYPE);
    }

    /**
     * Set RESOLUTION_TYPE.
     *
     * @param int $resolutionType
     *
     * @return \Webkul\MpRmaSystem\Api\Data\DetailsInterface
     */
    public function setResolutionType($resolutionType)
    {
        return $this->setData(self::RESOLUTION_TYPE, $resolutionType);
    }

    /**
     * Get NUMBER.
     *
     * @return string|null
     */
    public function getNumber()
    {
        return parent::getData(self::NUMBER);
    }

    /**
     * Set NUMBER.
     *
     * @param string $number
     *
     * @return \Webkul\MpRmaSystem\Api\Data\DetailsInterface
     */
    public function setNumber($number)
    {
        return $this->setData(self::NUMBER, $number);
    }

    /**
     * Get ORDER_REF.
     *
     * @return string|null
     */
    public function getOrderRef()
    {
        return parent::getData(self::ORDER_REF);
    }

    /**
     * Set ORDER_REF.
     *
     * @param string $orderRef
     *
     * @return \Webkul\MpRmaSystem\Api\Data\DetailsInterface
     */
    public function setOrderRef($orderRef)
    {
        return $this->setData(self::ORDER_REF, $orderRef);
    }

    /**
     * Get ADDITIONAL_INFO.
     *
     * @return string|null
     */
    public function getAdditionalInfo()
    {
        return parent::getData(self::ADDITIONAL_INFO);
    }

    /**
     * Set ADDITIONAL_INFO.
     *
     * @param string $additionalInfo
     *
     * @return \Webkul\MpRmaSystem\Api\Data\DetailsInterface
     */
    public function setAdditionalInfo($additionalInfo)
    {
        return $this->setData(self::ADDITIONAL_INFO, $additionalInfo);
    }

    /**
     * Get CREATED_DATE.
     *
     * @return string|null
     */
    public function getCreatedDate()
    {
        return parent::getData(self::CREATED_DATE);
    }

    /**
     * Set CREATED_DATE.
     *
     * @param string $createdDate
     *
     * @return \Webkul\MpRmaSystem\Api\Data\DetailsInterface
     */
    public function setCreatedDate($createdDate)
    {
        return $this->setData(self::CREATED_DATE, $createdDate);
    }

    /**
     * Get REFUNDED_AMOUNT.
     *
     * @return int|float
     */
    public function getRefundedAmount()
    {
        return parent::getData(self::REFUNDED_AMOUNT);
    }

    /**
     * Set REFUNDED_AMOUNT.
     *
     * @param int|float $refundedAmount
     *
     * @return \Webkul\MpRmaSystem\Api\Data\DetailsInterface
     */
    public function setRefundedAmount($refundedAmount)
    {
        return $this->setData(self::REFUNDED_AMOUNT, $refundedAmount);
    }

    /**
     * Get MEMO_ID.
     *
     * @return int|null
     */
    public function getMemoId()
    {
        return parent::getData(self::MEMO_ID);
    }

    /**
     * Set MEMO_ID.
     *
     * @param int $memoId
     *
     * @return \Webkul\MpRmaSystem\Api\Data\DetailsInterface
     */
    public function setMemoId($memoId)
    {
        return $this->setData(self::MEMO_ID, $memoId);
    }

    /**
     * Get CUSTOMER_NAME.
     *
     * @return string|null
     */
    public function getCustomerName()
    {
        return parent::getData(self::CUSTOMER_NAME);
    }

    /**
     * Set CUSTOMER_NAME.
     *
     * @param string $customerName
     *
     * @return \Webkul\MpRmaSystem\Api\Data\DetailsInterface
     */
    public function setCustomerName($customerName)
    {
        return $this->setData(self::CUSTOMER_NAME, $customerName);
    }

    /**
     * Get PRODUCT_SELLER.
     *
     * @return string|null
     */
    public function getProductSeller()
    {
        return parent::getData(self::PRODUCT_SELLER);
    }

    /**
     * Set PRODUCT_SELLER.
     *
     * @param string $productSeller
     *
     * @return \Webkul\MpRmaSystem\Api\Data\DetailsInterface
     */
    public function setProductSeller($productSeller)
    {
        return $this->setData(self::PRODUCT_SELLER, $productSeller);
    }
}
