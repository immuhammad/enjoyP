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
namespace Webkul\MpAdvancedBookingSystem\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Return status array
 */
class CancelStatus implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => __('Pending'),
                'value' => 0
            ],
            [
                'label' => __('Approved'),
                'value' => 1
            ],
            [
                'label' => __('Not Approved'),
                'value' => 2
            ]
        ];
    }
}
