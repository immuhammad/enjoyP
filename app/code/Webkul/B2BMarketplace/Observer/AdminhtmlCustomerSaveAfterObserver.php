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
 * Webkul B2BMarketplace AdminhtmlCustomerSaveAfterObserver Observer.
 */
class AdminhtmlCustomerSaveAfterObserver extends CustomerRegisterSuccessObserver
{
    /**
     * admin customer save after event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $customer = $observer->getCustomer();
            $customerId = $customer->getId();
            $postData = $observer->getRequest()->getPostValue();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $helper = $objectManager->create('Webkul\B2BMarketplace\Helper\Data');
            $postData['shop_title'] = $postData['shop_title'] ?? "";
            $customerData = ["wk_supplier_company_name" => $postData['shop_title']];
            $helper->saveCustomerData($customer, $customerData);
            $sellerModel = $this->helper->getSellerCollectionObj($customerId);
            if ($sellerModel->getSize()) {
                $shopUrl = '';
                $sellerStatus = '';
                foreach ($sellerModel as $seller) {
                    $shopUrl = $seller->getShopUrl();
                    $sellerStatus = $seller->getIsSeller();
                }
                if (($shopUrl && $sellerStatus && !isset($postData['is_seller_remove'])) ||
                isset($postData['is_seller_add'])) {
                    // Get suppier group id
                    $supplierGroupId = $this->b2bHelper->getSupplierCustomerGroupId();
                    // Check if customer is already a supplier
                    if ($customer->getGroupId() != $supplierGroupId) {
                        // Assign suppier group id to current customer
                        $this->b2bHelper->getCustomer($customerId)
                        ->setGroupId($supplierGroupId)
                        ->save();
                    }
                    if ($shopUrl && isset($postData['is_seller_add'])) {
                        $this->createSellerPublicUrls($shopUrl);
                    }
                } else {
                    // Get customer group id
                    $customerGroupId = $this->b2bHelper->getCustomerGroupId();
                    // Check if user is already a customer
                    if ($customer->getGroupId() != $customerGroupId) {
                        // Assign customer group id to current user
                        $this->b2bHelper->getCustomer($customerId)
                        ->setGroupId($customerGroupId)
                        ->save();
                    }
                }
            } else {
                // Get customer group id
                $customerGroupId = $this->b2bHelper->getCustomerGroupId();
                // Check if user is already a customer
                if ($customer->getGroupId() != $customerGroupId) {
                    // Assign customer group id to current user
                        $this->b2bHelper->getCustomer($customerId)
                        ->setGroupId($customerGroupId)
                        ->save();
                }
            }
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
    }
}
