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
 * MpRmaSystem Items interface.
 *
 * @api
 */
interface ItemsInterface
{
    /**
     * Constants for keys of data array.
     */
    public const ENTITY_ID = 'id';
    public const RMA_ID = 'rma_id';
    public const ITEM_ID = 'item_id';
    public const REASON_ID = 'reason_id';
    public const PRODUCT_ID = 'product_id';
    public const QTY = 'qty';
    public const PRICE = 'price';
    public const IS_QTY_RETURNED = 'is_qty_returned';

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
     * @return \Webkul\MpRmaSystem\Api\Data\ItemsInterface
     */
    public function setId($id);

    /**
     * Get RMA_ID.
     *
     * @return int|null
     */
    public function getRmaId();

    /**
     * Set RMA_ID.
     *
     * @param int $rmaId
     *
     * @return \Webkul\MpRmaSystem\Api\Data\ItemsInterface
     */
    public function setRmaId($rmaId);

    /**
     * Get ITEM_ID.
     *
     * @return int|null
     */
    public function getItemId();

    /**
     * Set ITEM_ID.
     *
     * @param int $itemId
     *
     * @return \Webkul\MpRmaSystem\Api\Data\ItemsInterface
     */
    public function setItemId($itemId);

    /**
     * Get REASON_ID.
     *
     * @return int|null
     */
    public function getReasonId();

    /**
     * Set REASON_ID.
     *
     * @param int $reasonId
     *
     * @return \Webkul\MpRmaSystem\Api\Data\ItemsInterface
     */
    public function setReasonId($reasonId);

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
     * @return \Webkul\MpRmaSystem\Api\Data\ItemsInterface
     */
    public function setProductId($productId);

    /**
     * Get QTY.
     *
     * @return int|null
     */
    public function getQty();

    /**
     * Set QTY.
     *
     * @param int $qty
     *
     * @return \Webkul\MpRmaSystem\Api\Data\ItemsInterface
     */
    public function setQty($qty);

    /**
     * Get PRICE.
     *
     * @return int|float
     */
    public function getPrice();

    /**
     * Set PRICE.
     *
     * @param int|float $price
     *
     * @return \Webkul\MpRmaSystem\Api\Data\ItemsInterface
     */
    public function setPrice($price);

    /**
     * Get IS_QTY_RETURNED.
     *
     * @return int|null
     */
    public function getIsQtyReturned();

    /**
     * Set IS_QTY_RETURNED.
     *
     * @param int $isQtyReturned
     *
     * @return \Webkul\MpRmaSystem\Api\Data\ItemsInterface
     */
    public function setIsQtyReturned($isQtyReturned);
}
