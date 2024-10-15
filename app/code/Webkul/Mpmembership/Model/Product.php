<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpmembership
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpmembership\Model;

use Webkul\Mpmembership\Api\Data\ProductInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Product Model.
 *
 * @method \Webkul\Mpmembership\Model\ResourceModel\Product _getResource()
 * @method \Webkul\Mpmembership\Model\ResourceModel\Product getResource()
 */
class Product extends \Magento\Catalog\Model\AbstractModel implements
    IdentityInterface,
    ProductInterface
{
    /**
     * No route page id.
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * Membership Product cache tag
     */
    const CACHE_TAG = 'membership_product';

    /**
     * Payment Statuses
     */
    const FEE_PENDING = 0;

    const FEE_PAID = 1;

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * @var string
     */
    protected $_eventPrefix = 'membership_product';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Mpmembership\Model\ResourceModel\Product::class);
    }

    /**
     * Load object data
     *
     * @param  int|null $id
     * @param  string   $field
     * @return $this
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteProduct();
        }
        return parent::load($id, $field);
    }

    /**
     * Load No-Route Product
     *
     * @return \Webkul\Mpmembership\Model\Product
     */
    public function noRouteProduct()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
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
     * @return \Webkul\Mpmembership\Api\Data\ProductInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
}
