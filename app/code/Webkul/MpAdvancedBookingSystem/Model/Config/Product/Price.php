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

namespace Webkul\MpAdvancedBookingSystem\Model\Config\Product;

class Price implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Get Option Array
     *
     * @param mixed $type
     */
    public function toOptionArray($type = '')
    {
        if ($type) {
            return [
                ['value' => 'fixed', 'label' => __('Fixed')]
            ];
        }
        return [
            ['value' => 'fixed', 'label' => __('Fixed')],
            // ['value' => 'percent', 'label' => __('Percent')]
        ];
    }
}
