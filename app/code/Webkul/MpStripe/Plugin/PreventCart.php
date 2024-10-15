<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpStripe
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpStripe\Plugin;

use Magento\Framework\Exception\LocalizedException;

/**
 * Prevent Add To Cart
 */
class PreventCart
{
    /**
     * @param \Webkul\MpStripe\Helper\Data $helper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Webkul\Marketplace\Helper\Data $marketplaceData
     * @param \Magento\Checkout\Model\Cart $cart
     */
    public function __construct(
        \Webkul\MpStripe\Helper\Data $helper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Webkul\Marketplace\Helper\Data $marketplaceData,
        \Magento\Checkout\Model\Cart $cart
    ) {
        $this->helper = $helper;
        $this->_messageManager = $messageManager;
        $this->marketplaceData = $marketplaceData;
        $this->cart = $cart;
    }

    /**
     * BeforeAddProduct function
     *
     * @param \Magento\Checkout\Model\Cart $subject
     * @param object $productInfo
     * @param array $requestInfo
     * @return array
     */
    public function beforeAddProduct(
        \Magento\Checkout\Model\Cart $subject,
        $productInfo,
        $requestInfo = null
    ) {
        $sellerList = [];
        if ($this->helper->getIsActive() && $this->helper->isDirectCharge()) {
            $items = $this->cart->getQuote()->getAllVisibleItems();
            foreach ($items as $item) {
                $sellerId = $this->marketplaceData->getSellerIdByProductId($item->getProductId());
                if (!in_array($sellerId, $sellerList)) {
                    $sellerList[] = $sellerId;
                }
            }

            $productId = $productInfo->getId();
            $currentSellerId = $this->marketplaceData->getSellerIdByProductId($productId);
            if (count($sellerList) > 1 || (!empty($sellerList) && !in_array($currentSellerId, $sellerList))) {
                $splitCartConfig = $this->marketplaceData->getConfigValue(
                    'marketplacesplitcart_settings',
                    'mpsplitcart_enable'
                );
                if (!(int)$splitCartConfig) {
                    throw new LocalizedException(__('You are not allowed to checkout with multi seller products.'));
                }
            }
        }
        return [$productInfo, $requestInfo];
    }
}
