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
namespace Webkul\Mpquotesystem\Plugin\Catalog\Block\Product\Compare;

use Magento\Catalog\Block\Product\Compare\ListCompare as Compare;

class ListCompare
{
    /**
     * @var \Webkul\Mpquotesystem\Helper\Data
     */
    private $_quotesystemHelper;

    /**
     * Initialize dependencies.
     *
     * @param \Webkul\Quotesystem\Helper\Data $helper
     */
    public function __construct(
        \Webkul\Mpquotesystem\Helper\Data $helper
    ) {
        $this->_quotesystemHelper = $helper;
    }

    /**
     * Change redirect after login
     *
     * @param Compare $subject
     * @param string $result
     * @param \Magento\Catalog\Model\Product $product
     * @param int $idSuffix
     */
    public function afterGetProductPrice(
        Compare $subject,
        $result,
        \Magento\Catalog\Model\Product $product,
        $idSuffix = ""
    ) {
        try {
            $isQuoted = $product->getQuoteStatus();
            $showPrice = (int)$this->_quotesystemHelper->getConfigShowPrice();
            if ($isQuoted == 1 && !$showPrice) {
                return '<div class="price-box " ' .
                'data-role="priceBox" ' .
                'data-product-id="' . $product->getId() . '" ' .
                'data-price-box="product-id-' . $product->getId() . '"' .
                '></div>';
                ;
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
        return $result;
    }
}
