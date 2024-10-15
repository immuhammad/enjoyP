<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_MpStripe
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */


namespace Webkul\MpStripe\Api;

/**
 * Stripe StripeSellerRepository Interface
 */
interface StripeSellerRepositoryInterface
{

    /**
     * Get by id
     *
     * @param int $id
     * @return \Webkul\MpStripe\Model\StripeSeller
     */
    public function getById($id);
    /**
     * Save
     *
     * @param \Webkul\MpStripe\Model\StripeSeller $subject
     * @return \Webkul\MpStripe\Model\StripeSeller
     */
    public function save(\Webkul\MpStripe\Model\StripeSeller $subject);
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
     * @param \Webkul\MpStripe\Model\StripeSeller $subject
     * @return boolean
     */
    public function delete(\Webkul\MpStripe\Model\StripeSeller $subject);
    /**
     * Delete by id
     *
     * @param int $id
     * @return boolean
     */
    public function deleteById($id);
    /**
     * Get By Seller Id
     *
     * @param int $sellerId
     * @return boolean
     */
    public function getBySellerId($sellerId);
}
