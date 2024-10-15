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
namespace Webkul\SellerSubDomain\Api\Data;

/**
 * SellerSubDomain Domain interface.
 * @api
 */
interface DomainInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ENTITY_ID   = 'entity_id';
    /**#@-*/

    const SELLER_ID   = 'seller_id';

    const VENDOR_STORE_ID    = 'vendor_store_id';

    const VENDOR_WEBSITE_ID  = 'vendor_website_id';
    
    const VENDOR_URL  = 'vendor_url';
    
    const CREATED_AT  = 'created_at';
    
    const UPDATED_AT  = 'updated_at';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @param int $id
     * @return \Webkul\SellerSubDomain\Api\Data\DomainInterface
     */
    public function setId($id);

    /**
     * Get Seller Id
     *
     * @return int|null
     */
    public function getSellerId();

    /**
     * Set Seller Id
     *
     * @param int $sellerId
     * @return \Webkul\SellerSubDomain\Api\Data\DomainInterface
     */
    public function setSellerId($sellerId);

    /**
     * Get Store Id
     *
     * @return int|null
     */
    public function getVendorStoreId();

    /**
     * Set Store Id
     *
     * @param int $store Id
     * @return \Webkul\SellerSubDomain\Api\Data\DomainInterface
     */
    public function setVendorStoreId($vendorStoreId);

    /**
     * Get Website Id
     *
     * @return int|null
     */
    public function getVendorWebsiteId();

    /**
     * Set Website Id
     *
     * @param int $websiteId
     * @return \Webkul\SellerSubDomain\Api\Data\DomainInterface
     */
    public function setVendorWebsiteId($vendorWebsiteId);

    /**
     * Get Vendor Url
     *
     * @return int|null
     */
    public function getVendorUrl();

    /**
     * Set vendor Url
     *
     * @param int $vendorUrl
     * @return \Webkul\SellerSubDomain\Api\Data\DomainInterface
     */
    public function setVendorUrl($vendorUrl);

    /**
     * Get Created Time
     *
     * @return int|null
     */
    public function getCreatedAt();

    /**
     * Set Created Time
     *
     * @param int $createdAt
     * @return \Webkul\SellerSubDomain\Api\Data\DomainInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get Updated Time
     *
     * @return int|null
     */
    public function getUpdatedAt();

    /**
     * Set Updated Time
     *
     * @param int $updatedAt
     * @return \Webkul\SellerSubDomain\Api\Data\DomainInterface
     */
    public function setUpdatedAt($updatedAt);
}
