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
namespace Webkul\MpAdvancedBookingSystem\Helper\ConfigurableProduct;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class Data
{
    /**
     * Get allowed attributes
     *
     * @param \Magento\ConfigurableProduct\Helper\Data $subject
     * @param array $result
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function afterGetAllowAttributes(
        \Magento\ConfigurableProduct\Helper\Data $subject,
        $result,
        $product
    ) {
        return ($product->getTypeId() == Configurable::TYPE_CODE || $product->getTypeId() == 'hotelbooking')
            ? $product->getTypeInstance()->getConfigurableAttributes($product)
            : [];
    }
}
