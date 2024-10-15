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
namespace Webkul\Mpquotesystem\Plugin\Checkout\Model;

use Magento\Framework\Exception\LocalizedException;

class Cart
{
    /**
     * @var \Webkul\Mpquotesystem\Helper\Data
     */
    protected $quoteHelper;
    
    /**
     * @param \Webkul\Mpquotesystem\Helper\Data $quoteHelper
     */
    public function __construct(
        \Webkul\Mpquotesystem\Helper\Data $quoteHelper
    ) {
        $this->quoteHelper = $quoteHelper;
    }

    /**
     * Cart Add product before process plugin.
     *
     * @param array     $subject
     * @param array     $productInfo
     * @param array     $requestInfo
     * @return array
     */
    public function beforeAddProduct($subject, $productInfo, $requestInfo = null)
    {
        try {
            if ($this->quoteHelper->getModuleStatus()) {
                $quoteStatus = $productInfo->getQuoteStatus();
                $allowAddToCart = (int)$this->quoteHelper->getConfigAddToCart();
                if (($quoteStatus == 1) && !$allowAddToCart) {
                    if (array_key_exists('quote_id', $requestInfo)) {
                        return [$productInfo, $requestInfo];
                    }
                    throw new LocalizedException(__('Add to cart for this product is not allowed'));
                }
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
        return [$productInfo, $requestInfo];
    }
}
