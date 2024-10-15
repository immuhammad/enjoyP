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
namespace Webkul\MpRmaSystem\Api\Data;

/**
 * MpRmaSystem Details interface.
 *
 * @api
 */
interface DetailsInterface
{
    /**
     * Constants for keys of data array.
     */
    public const ENTITY_ID = 'id';
    public const ORDER_ID = 'order_id';
    public const SELLER_ID = 'seller_id';
    public const CUSTOMER_ID = 'customer_id';
    public const CUSTOMER_EMAIL = 'customer_email';
    public const STATUS = 'status';
    public const ORDER_STATUS = 'order_status';
    public const SELLER_STATUS = 'seller_status';
    public const FINAL_STATUS = 'final_status';
    public const PRODUCT_ID = 'product_id';
    public const RESOLUTION_TYPE = 'resolution_type';
    public const NUMBER = 'number';
    public const ORDER_REF = 'order_ref';
    public const ADDITIONAL_INFO = 'additional_info';
    public const CREATED_DATE = 'created_date';
    public const REFUNDED_AMOUNT = 'refunded_amount';
    public const MEMO_ID = 'memo_id';
    public const CUSTOMER_NAME = 'customer_name';
    public const PRODUCT_SELLER = 'product_seller';

    /**
     * Get ID.
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID.
     *
     * @param int $id
     *
     * @return \Webkul\MpRmaSystem\Api\Data\DetailsInterface
     */
    public function setId($id);

    /**
     * Get ORDER_ID.
     *
     * @return int|null
     */
    public function getOrderId();

    /**
     * Set ORDER_ID.
     *
     * @param int $orderId
     *
     * @return \Webkul\MpRmaSystem\Api\Data\DetailsInterface
     */
    public function setOrderId($orderId);

    /**
     * Get SELLER_ID.
     *
     * @return int|null
     */
    public function getSellerId();

    /**
     * Set SELLER_ID.
     *
     * @param int $sellerId
     *
     * @return \Webkul\MpRmaSystem\Api\Data\DetailsInterface
     */
    public function setSellerId($sellerId);

    /**
     * Get CUSTOMER_ID.
     *
     * @return int|null
     */
    public function getCustomerId();

    /**
     * Set CUSTOMER_ID.
     *
     * @param int $customerId
     *
     * @return \Webkul\MpRmaSystem\Api\Data\DetailsInterface
     */
    public function setCustomerId($customerId);

    /**
     * Get CUSTOMER_EMAIL.
     *
     * @return string|null
     */
    public function getCustomerEmail();

    /**
     * Set CUSTOMER_EMAIL.
     *
     * @param string $customerEmail
     *
     * @return \Webkul\MpRmaSystem\Api\Data\DetailsInterface
     */
    public function setCustomerEmail($customerEmail);

    /**
     * Get STATUS.
     *
     * @return int|null
     */
    public function getStatus();

    /**
     * Set STATUS.
     *
     * @param int $status
     *
     * @return \Webkul\MpRmaSystem\Api\Data\DetailsInterface
     */
    public function setStatus($status);

    /**
     * Get ORDER_STATUS.
     *
     * @return int|null
     */
    public function getOrderStatus();

    /**
     * Set ORDER_STATUS.
     *
     * @param int $orderStatus
     *
     * @return \Webkul\MpRmaSystem\Api\Data\DetailsInterface
     */
    public function setOrderStatus($orderStatus);

    /**
     * Get SELLER_STATUS.
     *
     * @return int|null
     */
    public function getSellerStatus();

    /**
     * Set ORDER_STATUS.
     *
     * @param int $sellerStatus
     *
     * @return \Webkul\MpRmaSystem\Api\Data\DetailsInterface
     */
    public function setSellerStatus($sellerStatus);

    /**
     * Get FINAL_STATUS.
     *
     * @return int|null
     */
    public function getFinalStatus();

    /**
     * Set FINAL_STATUS.
     *
     * @param int $finalStatus
     *
     * @return \Webkul\MpRmaSystem\Api\Data\DetailsInterface
     */
    public function setFinalStatus($finalStatus);

    /**
     * Get PRODUCT_ID.
     *
     * @return int|null
     */
    public function getProductId();

    /**
     * Set PRODUCT_ID.
     *
     * @param int $productId
     *
     * @return \Webkul\MpRmaSystem\Api\Data\DetailsInterface
     */
    public function setProductId($productId);

    /**
     * Get RESOLUTION_TYPE.
     *
     * @return int|null
     */
    public function getResolutionType();

    /**
     * Set RESOLUTION_TYPE.
     *
     * @param int $resolutionType
     *
     * @return \Webkul\MpRmaSystem\Api\Data\DetailsInterface
     */
    public function setResolutionType($resolutionType);

    /**
     * Get NUMBER.
     *
     * @return string|null
     */
    public function getNumber();

    /**
     * Set NUMBER.
     *
     * @param string $number
     *
     * @return \Webkul\MpRmaSystem\Api\Data\DetailsInterface
     */
    public function setNumber($number);

    /**
     * Get ORDER_REF.
     *
     * @return string|null
     */
    public function getOrderRef();

    /**
     * Set ORDER_REF.
     *
     * @param string $orderRef
     *
     * @return \Webkul\MpRmaSystem\Api\Data\DetailsInterface
     */
    public function setOrderRef($orderRef);

    /**
     * Get ADDITIONAL_INFO.
     *
     * @return string|null
     */
    public function getAdditionalInfo();

    /**
     * Set ADDITIONAL_INFO.
     *
     * @param string $additionalInfo
     *
     * @return \Webkul\MpRmaSystem\Api\Data\DetailsInterface
     */
    public function setAdditionalInfo($additionalInfo);

    /**
     * Get CREATED_DATE.
     *
     * @return string|null
     */
    public function getCreatedDate();

    /**
     * Set CREATED_DATE.
     *
     * @param string $createdDate
     *
     * @return \Webkul\MpRmaSystem\Api\Data\DetailsInterface
     */
    public function setCreatedDate($createdDate);

    /**
     * Get REFUNDED_AMOUNT.
     *
     * @return int|float
     */
    public function getRefundedAmount();

    /**
     * Set REFUNDED_AMOUNT.
     *
     * @param int|float $refundedAmount
     *
     * @return \Webkul\MpRmaSystem\Api\Data\DetailsInterface
     */
    public function setRefundedAmount($refundedAmount);

    /**
     * Get MEMO_ID.
     *
     * @return int|null
     */
    public function getMemoId();

    /**
     * Set MEMO_ID.
     *
     * @param int $memoId
     *
     * @return \Webkul\MpRmaSystem\Api\Data\DetailsInterface
     */
    public function setMemoId($memoId);

    /**
     * Get CUSTOMER_NAME.
     *
     * @return string|null
     */
    public function getCustomerName();

    /**
     * Set CUSTOMER_NAME.
     *
     * @param string $customerName
     *
     * @return \Webkul\MpRmaSystem\Api\Data\DetailsInterface
     */
    public function setCustomerName($customerName);

    /**
     * Get PRODUCT_SELLER.
     *
     * @return string|null
     */
    public function getProductSeller();

    /**
     * Set PRODUCT_SELLER.
     *
     * @param string $productSeller
     *
     * @return \Webkul\MpRmaSystem\Api\Data\DetailsInterface
     */
    public function setProductSeller($productSeller);
}
