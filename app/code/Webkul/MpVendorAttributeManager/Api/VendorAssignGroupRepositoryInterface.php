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
 * VendorAssignGroup CRUD interface.
 */
interface VendorAssignGroupRepositoryInterface
{
    /**
     * Function get by id
     *
     * @param int $id
     * @return \Webkul\MpVendorAttributeManager\Model\VendorAssignGroup
     */
    public function getById($id);

    /**
     * Function save
     *
     * @param \Webkul\MpVendorAttributeManager\Model\VendorAssignGroup $subject
     * @return \Webkul\MpVendorAttributeManager\Model\VendorAssignGroup
     */
    public function save(\Webkul\MpVendorAttributeManager\Model\VendorAssignGroup $subject);

    /**
     * Function get list
     *
     * @param Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return Magento\Framework\Api\SearchResults
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria);

    /**
     * Function delete
     *
     * @param \Webkul\MpVendorAttributeManager\Model\VendorAssignGroup $subject
     * @return boolean
     */
    public function delete(\Webkul\MpVendorAttributeManager\Model\VendorAssignGroup $subject);

    /**
     * Function delete by id
     *
     * @param int $id
     * @return boolean
     */
    public function deleteById($id);
}
