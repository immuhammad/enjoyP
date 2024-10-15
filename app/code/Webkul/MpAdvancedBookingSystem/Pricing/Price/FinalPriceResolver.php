<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvancedBookingSystem
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAdvancedBookingSystem\Pricing\Price;

use Magento\Catalog\Pricing\Price\FinalPrice as CatalogFinalPrice;
use Magento\ConfigurableProduct\Pricing\Price\PriceResolverInterface;

class FinalPriceResolver implements PriceResolverInterface
{
    /**
     * ResolvePrice
     *
     * @param \Magento\Framework\Pricing\SaleableInterface $product
     * @return float
     */
    public function resolvePrice(\Magento\Framework\Pricing\SaleableInterface $product)
    {
        return $product->getPriceInfo()->getPrice(CatalogFinalPrice::PRICE_CODE)->getValue();
    }
}
