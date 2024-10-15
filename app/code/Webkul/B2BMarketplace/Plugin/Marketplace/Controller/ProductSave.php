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
namespace Webkul\B2BMarketplace\Plugin\Marketplace\Controller;

class ProductSave
{
    public function __construct(
        \Magento\Framework\Controller\ResultFactory $resultRedirect,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->resultRedirect = $resultRedirect;
        $this->messageManager = $messageManager;
    }

    public function aroundExecute(
        \Webkul\Marketplace\Controller\Product\Save $subject,
        \Closure $proceed
    ) {
        $wholedata = $subject->getRequest()->getParams();
        $productData = $wholedata["product"];
        if (array_key_exists("mp_product_cart_limit", $productData) &&
        array_key_exists("allowed_min_qty_to_order", $productData)) {
            $cartLimit = $productData["mp_product_cart_limit"];
            $allowMinQty = $productData["allowed_min_qty_to_order"];
            if ($allowMinQty > $cartLimit) {
                $this->messageManager->addError(__("Minimum quantity should not be more than cart limit"));
                if (!array_key_exists("id", $wholedata)) {
                    return $this->resultRedirect
                    ->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)->setPath(
                        '*/*/add',
                        [
                            'set' => $wholedata['set'],
                            'type' => $wholedata['type'],
                            '_secure' => $subject->getRequest()->isSecure()
                        ]
                    );
                } else {
                    return $this->resultRedirect
                    ->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)->setPath(
                        '*/*/edit',
                        [
                            'id' => $wholedata["id"],
                            '_secure' => $subject->getRequest()->isSecure(),
                        ]
                    );
                }
            }
        }
        return $proceed();
    }
}
