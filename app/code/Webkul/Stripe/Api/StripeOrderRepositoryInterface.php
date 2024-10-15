<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_Stripe
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */


namespace Webkul\Stripe\Api;

/**
 * Stripe StripeOrderRepository Interface
 */
interface StripeOrderRepositoryInterface
{

    /**
     * Get by id
     *
     * @param int $id
     * @return \Webkul\Stripe\Model\StripeOrder
     */
    public function getById($id);
    /**
     * Save
     *
     * @param \Webkul\Stripe\Model\StripeOrder $subject
     * @return \Webkul\Stripe\Model\StripeOrder
     */
    public function save(\Webkul\Stripe\Model\StripeOrder $subject);
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
     * @param \Webkul\Stripe\Model\StripeOrder $subject
     * @return boolean
     */
    public function delete(\Webkul\Stripe\Model\StripeOrder $subject);
    /**
     * Delete by id
     *
     * @param int $id
     * @return boolean
     */
    public function deleteById($id);
}
