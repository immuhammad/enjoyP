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
 * Strpe StripeCustomerRepository Interface
 */
interface StripeCustomerRepositoryInterface
{

    /**
     * Get by id
     *
     * @param int $id
     * @return \Webkul\MpStripe\Model\StripeCustomer
     */
    public function getById($id);
    /**
     * Save
     *
     * @param \Webkul\MpStripe\Model\StripeCustomer $subject
     * @return \Webkul\MpStripe\Model\StripeCustomer
     */
    public function save(\Webkul\MpStripe\Model\StripeCustomer $subject);
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
     * @param \Webkul\MpStripe\Model\StripeCustomer $subject
     * @return boolean
     */
    public function delete(\Webkul\MpStripe\Model\StripeCustomer $subject);
    /**
     * Delete by id
     *
     * @param int $id
     * @return boolean
     */
    public function deleteById($id);
}
