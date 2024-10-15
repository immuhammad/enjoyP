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

/**
 * Class DiscountConfigureProcess
 *
 * Removes discount block when wallet amount product is in cart.
 */
class DiscountConfigureProcess
{
    /**
     * @var \Webkul\Mpquotesystem\Helper\Data
     */
    private $quoteHelper;

    /**
     * @param \Webkul\Mpquotesystem\Helper\Data $quoteHelper
     */
    public function __construct(
        \Webkul\Mpquotesystem\Helper\Data $quoteHelper
    ) {
        $this->quoteHelper = $quoteHelper;
    }

    /**
     * Checkout LayoutProcessor before process plugin.
     *
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $LayoutProcessor
     * @param callable                                         $proceed
     * @param array                                            $jsLayout
     *
     * @return array
     */
    public function aroundProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $LayoutProcessor,
        callable $proceed,
        $jsLayout
    ) {
        try {
            $jsLayout = $proceed($jsLayout);
            if (!$this->quoteHelper->getDiscountEnable()
            && $this->quoteHelper->checkQuoteProductIsInCart()
            ) {
                unset(
                    $jsLayout['components']['checkout']['children']
                    ['steps']['children']['billing-step']['children']['payment']
                    ['children']['afterMethods']['children']['discount']
                );
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
        return $jsLayout;
    }
}
