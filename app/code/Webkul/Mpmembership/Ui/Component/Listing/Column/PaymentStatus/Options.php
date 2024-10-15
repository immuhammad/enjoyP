<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpmembership
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpmembership\Ui\Component\Listing\Column\PaymentStatus;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class to get the Payment Ststus Options
 */
class Options implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'label' => __('Pending'),
                'value' => 0
            ],
            [
                'label' => __('Paid'),
                'value' => 1
            ]
        ];
        return $options;
    }
}
