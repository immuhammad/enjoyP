<?php

/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_QuickOrder
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\B2BMarketplace\Block\Product;

/**
 * Confugurable product view type
 *
 * @api
 * @api
 * @since 100.0.2
 */
class Configurable extends \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable
{
    /**
     * Get allowed attributes
     *
     * @return array
     */
    public function getAllowAttributes()
    {
        if ($this->getProduct()->getTypeId() =='configurable') {
            return $this->getProduct()->getTypeInstance()->getConfigurableAttributes($this->getProduct());
        }
        return [];
    }
}
