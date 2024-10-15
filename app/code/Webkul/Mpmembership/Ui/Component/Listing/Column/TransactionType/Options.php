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

namespace Webkul\Mpmembership\Ui\Component\Listing\Column\TransactionType;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class to get the transaction type Options
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
                'label' => __('Expired'),
                'value' => 1
            ],
            [
                'label' => __('Valid'),
                'value' => 2
            ],
            [
                'label' => __('Time Expired'),
                'value' => 3
            ],
            [
                'label' => __('Product Limit Completed'),
                'value' => 4
            ]
        ];
        return $options;
    }
}
