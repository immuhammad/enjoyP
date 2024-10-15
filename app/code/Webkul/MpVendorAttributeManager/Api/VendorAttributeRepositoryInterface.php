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
 * VendorAttribute CRUD interface.
 */
interface VendorAttributeRepositoryInterface
{
    /**
     * Function get by id
     *
     * @param int $id
     * @return \Webkul\MpVendorAttributeManager\Model\VendorAttribute
     */
    public function getById($id);

    /**
     * Function save
     *
     * @param \Webkul\MpVendorAttributeManager\Model\VendorAttribute $subject
     * @return \Webkul\MpVendorAttributeManager\Model\VendorAttribute
     */
    public function save(\Webkul\MpVendorAttributeManager\Model\VendorAttribute $subject);

    /**
     * Function get list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Webkul\MpVendorAttributeManager\Api\Data\VendorAttributeSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria);

    /**
     * Function get list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Webkul\MpVendorAttributeManager\Api\Data\VendorAttributeSearchResultsInterface
     */
    public function getJoinedList(\Magento\Framework\Api\SearchCriteriaInterface $criteria);

    /**
     * Function delete
     *
     * @param \Webkul\MpVendorAttributeManager\Model\VendorAttribute $subject
     * @return boolean
     */
    public function delete(\Webkul\MpVendorAttributeManager\Model\VendorAttribute $subject);

    /**
     * Function delete by id
     *
     * @param int $id
     * @return boolean
     */
    public function deleteById($id);
}
