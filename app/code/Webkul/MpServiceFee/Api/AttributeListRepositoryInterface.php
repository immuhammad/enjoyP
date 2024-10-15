<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_MpServiceFee
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\MpServiceFee\Api;

/**
 * AttributeListRepository crud interface.
 */
interface AttributeListRepositoryInterface
{
    /**
     * Get by id
     *
     * @param int $id
     * @return \Webkul\MpServiceFee\Model\AttributesList
     */
    public function getById($id);

    /**
     * Save
     *
     * @param \Webkul\MpServiceFee\Model\AttributesList  $subject
     * @return \Webkul\MpServiceFee\Model\AttributesList
     */
    public function save(\Webkul\MpServiceFee\Model\AttributesList  $subject);

    /**
     * Get list
     *
     * @param Magento\Framework\Api\SearchCriteriaInterface $creteria
     * @return Magento\Framework\Api\SearchResults
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $creteria);

    /**
     * Delete
     *
     * @param \Webkul\MpServiceFee\Model\AttributesList  $subject
     * @return boolean
     */
    public function delete(\Webkul\MpServiceFee\Model\AttributesList  $subject);
    
    /**
     * Delete by id
     *
     * @param int $id
     * @return boolean
     */
    public function deleteById($id);
}
