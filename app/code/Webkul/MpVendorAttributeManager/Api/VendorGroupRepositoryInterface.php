<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_MpVendorAttributeManager
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\MpVendorAttributeManager\Api;

/**
 * VendorGroup CRUD interface.
 */
interface VendorGroupRepositoryInterface
{
    /**
     * Function get by id
     *
     * @param int $id
     * @return \Webkul\MpVendorAttributeManager\Model\VendorGroup
     */
    public function getById($id);

    /**
     * Function save
     *
     * @param \Webkul\MpVendorAttributeManager\Model\VendorGroup $subject
     * @return \Webkul\MpVendorAttributeManager\Model\VendorGroup
     */
    public function save(\Webkul\MpVendorAttributeManager\Model\VendorGroup $subject);

    /**
     * Function get list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Webkul\MpVendorAttributeManager\Api\Data\VendorGroupSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria);

    /**
     * Function delete
     *
     * @param \Webkul\MpVendorAttributeManager\Model\VendorGroup $subject
     * @return boolean
     */
    public function delete(\Webkul\MpVendorAttributeManager\Model\VendorGroup $subject);

    /**
     * Function delete by id
     *
     * @param int $id
     * @return boolean
     */
    public function deleteById($id);
}
