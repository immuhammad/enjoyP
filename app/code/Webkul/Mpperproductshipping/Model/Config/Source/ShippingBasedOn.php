<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Mpperproductshipping
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\Mpperproductshipping\Model\Config\Source;

class ShippingBasedOn implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 0, 'label' => __('Main Product')], ['value' => 1, 'label' => __('Associated Product')]];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [0 => __('Main Product'), 1 => __('Associated Product')];
    }
}
