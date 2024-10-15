<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_B2BMarketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\B2BMarketplace\Observer;

/**
 * Webkul B2BMarketplace MassApproveAfterObserver Observer.
 */
class MassApproveAfterObserver extends CustomerRegisterSuccessObserver
{
    /**
     * MassApproveAfterObserver event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $supplier = $observer->getSeller();
            $shopUrl = $supplier->getShopUrl();
            $supplierId = $supplier->getId();
            // Get suppier group id
            $supplierGroupId = $this->b2bHelper->getSupplierCustomerGroupId();
            $customer = $this->b2bHelper->getCustomer($supplierId);
            // Check if customer is already a supplier
            if ($customer->getGroupId() != $supplierGroupId) {
                // Assign suppier group id to current customer
                $customer->setGroupId($supplierGroupId)->save();
            }
            // Create supplier public urls for microsite
            $this->createSellerPublicUrls($shopUrl);
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }

        return $this;
    }
}
