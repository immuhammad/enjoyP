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

use Webkul\MpAdvancedBookingSystem\Model\Product\Type\Hotelbooking;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\ConfigurableProduct\Pricing\Price\PriceResolverInterface;
use Magento\ConfigurableProduct\Pricing\Price\LowestPriceOptionsProviderInterface;

class ConfigurablePriceResolver implements PriceResolverInterface
{
    /**
     * @var PriceResolverInterface
     */
    protected $priceResolver;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var Hotelbooking
     */
    protected $configurable;

    /**
     * @var LowestPriceOptionsProviderInterface
     */
    private $lowestPriceOptionsProvider;

    /**
     * Constructor
     *
     * @param PriceResolverInterface               $priceResolver
     * @param Hotelbooking                         $configurable
     * @param PriceCurrencyInterface               $priceCurrency
     * @param LowestPriceOptionsProviderInterface  $lowestPriceOptionsProvider
     */
    public function __construct(
        PriceResolverInterface $priceResolver,
        Hotelbooking $configurable,
        PriceCurrencyInterface $priceCurrency,
        LowestPriceOptionsProviderInterface $lowestPriceOptionsProvider = null
    ) {
        $this->priceResolver = $priceResolver;
        $this->configurable = $configurable;
        $this->priceCurrency = $priceCurrency;
        $this->lowestPriceOptionsProvider = $lowestPriceOptionsProvider ?:
            ObjectManager::getInstance()->get(LowestPriceOptionsProviderInterface::class);
    }

    /**
     * ResolvePrice
     *
     * @param \Magento\Framework\Pricing\SaleableInterface|\Magento\Catalog\Model\Product $product
     * @return float
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function resolvePrice(\Magento\Framework\Pricing\SaleableInterface $product)
    {
        $price = null;

        foreach ($this->lowestPriceOptionsProvider->getProducts($product) as $subProduct) {
            $productPrice = $this->priceResolver->resolvePrice($subProduct);
            $price = $price ? min($price, $productPrice) : $productPrice;
        }

        return (float)$price;
    }
}
