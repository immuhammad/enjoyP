<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mpquotesystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mpquotesystem\ViewModel;

class Catalog implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @param \Webkul\Mpquotesystem\Helper\Data $helper
     * @param \Magento\Wishlist\Helper\Data $wishlistHelper
     */
    public function __construct(
        \Webkul\Mpquotesystem\Helper\Data $helper,
        \Magento\Wishlist\Helper\Data $wishlistHelper
    ) {
        $this->helper = $helper;
        $this->wishlistHelper = $wishlistHelper;
    }

    /**
     * Get object of helper class
     *
     * @return void
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * Get object of Wishlist helper class
     *
     * @return void
     */
    public function getWishlistHelper()
    {
        return $this->wishlistHelper;
    }
}
