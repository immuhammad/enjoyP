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
 * Webkul B2BMarketplace BecomesellerPostAfterObserver Observer.
 */
class BecomesellerPostAfterObserver extends CustomerRegisterSuccessObserver
{
    /**
     * BecomesellerPost event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            /** @var $controllerAction \Magento\Framework\App\RequestInterface */
            $requestData = $observer->getEvent()->getRequest()->getParams();
            $sellerId = $this->helper->getCustomerId();
            if (isset($requestData['profileurl']) && isset($requestData['is_seller'])) {
                if (!empty($requestData['profileurl']) && $requestData['is_seller']) {
                    $shopUrl = $requestData['profileurl'];
                    $sellerModel = $this->helper->getSellerCollectionObj($sellerId);
                    if ($sellerModel->getSize()) {
                        // Get suppier group id
                        $supplierGroupId = $this->b2bHelper->getSupplierCustomerGroupId();
                        $customer = $this->b2bHelper->getCustomer($sellerId);
                        // Check if customer is already a supplier
                        if ($customer->getGroupId() != $supplierGroupId) {
                            // Assign suppier group id to current customer
                            $customer->setGroupId($supplierGroupId)->save();
                        }
                        // Create supplier public urls for microsite
                        $this->createSellerPublicUrls($shopUrl);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }

        return $this;
    }
}
