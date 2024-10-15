<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SellerSubDomain
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\SellerSubDomain\Model;

use Magento\Framework\Model\AbstractModel;
use Webkul\SellerSubDomain\Api\Data\DomainInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * SellerSubDomain Domain Model.
 *
 * @method \Webkul\SellerSubDomain\Model\ResourceModel\Domain _getResource()
 * @method \Webkul\SellerSubDomain\Model\ResourceModel\Domain getResource()
 */
class Domain extends AbstractModel implements DomainInterface, IdentityInterface
{
    /**
     * No route page id.
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * SellerSubDomain Domain cache tag.
     */
    const CACHE_TAG = 'vendor_domain_list';

    /**
     * @var string
     */
    protected $_cacheTag = 'vendor_domain_list';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'vendor_domain_list';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Webkul\SellerSubDomain\Model\ResourceModel\Domain::class);
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
            return $this->noRouteControllers();
        }

        return parent::load($id, $field);
    }

    /**
     * Load No-Route Controllers.
     *
     * @return \Webkul\SellerSubDomain\Model\Domain
     */
    public function noRouteControllers()
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
     * @return \Webkul\SellerSubDomain\Api\Data\DomainInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Get Seller Id
     *
     * @return int|null
     */
    public function getSellerId()
    {
        return parent::getData(self::SELLER_ID);
    }

    /**
     * Set Seller Id
     *
     * @param int $sellerId
     * @return \Webkul\SellerSubDomain\Api\Data\DomainInterface
     */
    public function setSellerId($sellerId)
    {
        return $this->setData(self::SELLER_ID, $sellerId);
    }

    /**
     * Get Store Id
     *
     * @return int|null
     */
    public function getVendorStoreId()
    {
        return parent::getData(self::VENDOR_STORE_ID);
    }

    /**
     * Set Store Id
     *
     * @param int $store Id
     * @return \Webkul\SellerSubDomain\Api\Data\DomainInterface
     */
    public function setVendorStoreId($vendorStoreId)
    {
        return $this->setData(self::VENDOR_STORE_ID, $vendorStoreId);
    }

    /**
     * Get Website Id
     *
     * @return int|null
     */
    public function getVendorWebsiteId()
    {
        return parent::getData(self::VENDOR_WEBSITE_ID);
    }

    /**
     * Set Website Id
     *
     * @param int $websiteId
     * @return \Webkul\SellerSubDomain\Api\Data\DomainInterface
     */
    public function setVendorWebsiteId($vendorWebsiteId)
    {
        return $this->setData(self::VENDOR_WEBSITE_ID, $vendorWebsiteId);
    }

    /**
     * Get Vendor Url
     *
     * @return int|null
     */
    public function getVendorUrl()
    {
        return parent::getData(self::VENDOR_URL);
    }

    /**
     * Set vendor Url
     *
     * @param int $vendorUrl
     * @return \Webkul\SellerSubDomain\Api\Data\DomainInterface
     */
    public function setVendorUrl($vendorUrl)
    {
        return $this->setData(self::VENDOR_URL, $vendorUrl);
    }

    /**
     * Get UpdCreatedated Time
     *
     * @return int|null
     */
    public function getCreatedAt()
    {
        return parent::getData(self::CREATED_AT);
    }

    /**
     * Set Created Time
     *
     * @param int $modulename
     * @return \Webkul\SellerSubDomain\Api\Data\DomainInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get Updated Time
     *
     * @return int|null
     */
    public function getUpdatedAt()
    {
        return parent::getData(self::UPDATED_AT);
    }

    /**
     * Set Updated Time
     *
     * @param int $updatedAt
     * @return \Webkul\SellerSubDomain\Api\Data\DomainInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
