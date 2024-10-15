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

use Webkul\MpRmaSystem\Api\Data\ItemsInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * MpRmaSystem Items Model.
 *
 * @method \Webkul\MpRmaSystem\Model\ResourceModel\Items _getResource()
 * @method \Webkul\MpRmaSystem\Model\ResourceModel\Items getResource()
 */
class Items extends AbstractModel implements ItemsInterface, IdentityInterface
{
    public const NOROUTE_ENTITY_ID = 'no-route';

    public const CACHE_TAG = 'mprmasystem_items';

    /**
     * @var string
     */
    protected $_cacheTag = 'mprmasystem_items';

    /**
     * @var string
     */
    protected $_eventPrefix = 'mprmasystem_items';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpRmaSystem\Model\ResourceModel\Items::class);
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
            return $this->noRouteItems();
        }

        return parent::load($id, $field);
    }

    /**
     * Load No-Route Items.
     *
     * @return \Webkul\MpRmaSystem\Model\Items
     */
    public function noRouteItems()
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
     * @return \Webkul\MpRmaSystem\Api\Data\ItemsInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Get RMA_ID.
     *
     * @return int|null
     */
    public function getRmaId()
    {
        return parent::getData(self::RMA_ID);
    }

    /**
     * Set RMA_ID.
     *
     * @param int $rmaId
     *
     * @return \Webkul\MpRmaSystem\Api\Data\ItemsInterface
     */
    public function setRmaId($rmaId)
    {
        return $this->setData(self::RMA_ID, $rmaId);
    }

    /**
     * Get ITEM_ID.
     *
     * @return int|null
     */
    public function getItemId()
    {
        return parent::getData(self::ITEM_ID);
    }

    /**
     * Set ITEM_ID.
     *
     * @param int $itemId
     *
     * @return \Webkul\MpRmaSystem\Api\Data\ItemsInterface
     */
    public function setItemId($itemId)
    {
        return $this->setData(self::ITEM_ID, $itemId);
    }

    /**
     * Get REASON_ID.
     *
     * @return int|null
     */
    public function getReasonId()
    {
        return parent::getData(self::REASON_ID);
    }

    /**
     * Set REASON_ID.
     *
     * @param int $reasonId
     *
     * @return \Webkul\MpRmaSystem\Api\Data\ItemsInterface
     */
    public function setReasonId($reasonId)
    {
        return $this->setData(self::REASON_ID, $reasonId);
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
     * @return \Webkul\MpRmaSystem\Api\Data\ItemsInterface
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * Get QTY.
     *
     * @return int|null
     */
    public function getQty()
    {
        return parent::getData(self::QTY);
    }

    /**
     * Set QTY.
     *
     * @param int $qty
     *
     * @return \Webkul\MpRmaSystem\Api\Data\ItemsInterface
     */
    public function setQty($qty)
    {
        return $this->setData(self::QTY, $qty);
    }

    /**
     * Get PRICE.
     *
     * @return int|float
     */
    public function getPrice()
    {
        return parent::getData(self::PRICE);
    }

    /**
     * Set PRICE.
     *
     * @param int|float $price
     *
     * @return \Webkul\MpRmaSystem\Api\Data\ItemsInterface
     */
    public function setPrice($price)
    {
        return $this->setData(self::PRICE, $price);
    }

    /**
     * Get IS_QTY_RETURNED.
     *
     * @return int|null
     */
    public function getIsQtyReturned()
    {
        return parent::getData(self::IS_QTY_RETURNED);
    }

    /**
     * Set IS_QTY_RETURNED.
     *
     * @param int $isQtyReturned
     *
     * @return \Webkul\MpRmaSystem\Api\Data\ItemsInterface
     */
    public function setIsQtyReturned($isQtyReturned)
    {
        return $this->setData(self::IS_QTY_RETURNED, $isQtyReturned);
    }
}
