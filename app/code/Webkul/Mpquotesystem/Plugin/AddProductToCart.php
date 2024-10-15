<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpquotesystem
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpquotesystem\Plugin;

use Magento\Checkout\Model\Cart;
use Magento\Framework\Exception\LocalizedException;
use Webkul\Mpquotesystem\Helper\Data;

/**
 * Class AddProductToCart
 *
 * Restrict from adding the same quoted product to cart
 */
class AddProductToCart extends Cart
{
    /**
     * @var \Webkul\Mpquotesystem\Helper\Data
     */
    protected $helper;

    /**
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->_helper = $helper;
    }

    /**
     * Plugin for \Magento\Checkout\Model\Cart
     *
     * @param \Magento\Checkout\Model\Cart $subject
     * @param object $proceed
     * @param array $productInfo
     * @param array $requestInfo
     * @return void
     */
    public function aroundAddProduct(
        \Magento\Checkout\Model\Cart $subject,
        $proceed,
        $productInfo,
        $requestInfo = null
    ) {
        try {
            $product = $subject->_getProduct($productInfo);
            $productId = $product->getId();
            if ($productId) {
                $cartitems = $subject->getItems();
                foreach ($cartitems as $item) {
                    $cartProductId = $item->getProductId();
                    $productInCart = $this->_helper->getProduct($cartProductId);
                    $defaultConfig = null;
                    if (($productInCart->getQuoteStatus() == 2)) {
                        $defaultConfig = $this->_helper->applyGlobalConfig($productInCart);
                    }
                    if ($productId === $cartProductId) {
                        if (($productInCart->getQuoteStatus() == 1) || ($defaultConfig)) {
                            throw new LocalizedException(__('Same Product is already added to cart.'));
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
            return $proceed($productInfo, $requestInfo);
    }
}
