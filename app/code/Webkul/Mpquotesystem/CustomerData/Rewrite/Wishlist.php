<?php
/**
 * Webkul
 *
 * @category  Webkul
 * @package   Webkul_Mpquotesystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpquotesystem\CustomerData\Rewrite;

class Wishlist extends \Magento\Wishlist\CustomerData\Wishlist
{
    /**
     * @var \Magento\Wishlist\Helper\Data
     */
    protected $wishlistHelper;

    /**
     * @var \Magento\Wishlist\Block\Customer\Sidebar
     */
    protected $block;

    /**
     * @var \Webkul\Mpquotesystem\Helper\Data
     */
    protected $quoteHelper;

    /**
     * @param \Magento\Wishlist\Helper\Data            $wishlistHelper
     * @param \Magento\Wishlist\Block\Customer\Sidebar $block
     * @param \Magento\Catalog\Helper\ImageFactory     $imageHelperFactory
     * @param \Magento\Framework\App\ViewInterface     $view
     * @param \Webkul\Mpquotesystem\Helper\Data        $quoteHelper
     */
    public function __construct(
        \Magento\Wishlist\Helper\Data $wishlistHelper,
        \Magento\Wishlist\Block\Customer\Sidebar $block,
        \Magento\Catalog\Helper\ImageFactory $imageHelperFactory,
        \Magento\Framework\App\ViewInterface $view,
        \Webkul\Mpquotesystem\Helper\Data $quoteHelper
    ) {
        $this->wishlistHelper = $wishlistHelper;
        $this->block = $block;
        $this->quoteHelper = $quoteHelper;
        parent::__construct(
            $wishlistHelper,
            $block,
            $imageHelperFactory,
            $view
        );
    }

    /**
     * Retrieve wishlist item data
     *
     * @param  \Magento\Wishlist\Model\Item $wishlistItem
     * @return array
     */
    protected function getItemData(\Magento\Wishlist\Model\Item $wishlistItem)
    {
        $product = $wishlistItem->getProduct();
        $quotePrice = false;
        $quoteAddToCart = false;
        $product = $this->quoteHelper->getProductById($product->getId());
        $status = $product->getQuoteStatus();
        $showPrice = (int)$this->quoteHelper->getConfigShowPrice();
        $showAddToCart = (int)$this->quoteHelper->getConfigAddToCart();

        if (($status == 1) && !$showPrice) {
            $quotePrice = true;
        }
        if (($status == 1) && !$showAddToCart) {
            $quoteAddToCart = true;
        }

        return [
            'image' => $this->getImageData($product),
            'product_url' => $this->wishlistHelper->getProductUrl($wishlistItem),
            'product_name' => $product->getName(),
            'product_id' => $product->getEntityId(),
            'product_price' => $quotePrice ?
                '<div class="price-box" data-product-id="'.$product->getEntityId().'"></div>'
                : $this->block->getProductPriceHtml(
                    $product,
                    'wishlist_configured_price',
                    \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
                    ['item' => $wishlistItem]
                ),
            'product_is_saleable_and_visible' => $quoteAddToCart ? !$quoteAddToCart : $product->isSaleable() &&
            $product->isVisibleInSiteVisibility(),
            'product_has_required_options' => $product->getTypeInstance()->hasRequiredOptions($product),
            'add_to_cart_params' => $this->wishlistHelper->getAddToCartParams($wishlistItem, true),
            'delete_item_params' => $this->wishlistHelper->getRemoveParams($wishlistItem, true),
        ];
    }
}
