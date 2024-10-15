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

namespace Webkul\MpRmaSystem\Api;

interface DetailsRepositoryInterface
{
    /**
     * Get by id
     *
     * @param int $id
     * @return \Webkul\MpRmaSystem\Model\Details
     */
    public function getById($id);
    
    /**
     * Save
     *
     * @param \Webkul\MpRmaSystem\Model\Details $subject
     * @return void
     */
    public function save(\Webkul\MpRmaSystem\Model\Details $subject);

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
     * @param \Webkul\MpRmaSystem\Model\Details $subject
     * @return boolean
     */
    public function delete(\Webkul\MpRmaSystem\Model\Details $subject);
    
    /**
     * Delete by id
     *
     * @param int $id
     * @return boolean
     */
    public function deleteById($id);
}
