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

namespace Webkul\Mpmembership\Model\Config\Source;

/**
 * Used in creating options for getting type to add products
 */
class Checktype
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $data =  [
            [
                'value' => \Webkul\Mpmembership\Model\Transaction::TIME_AND_PRODUCTS,
                'label' => __('Time and Number Of Products')
            ],
            [
                'value' => \Webkul\Mpmembership\Model\Transaction::TIME,
                'label' => __('Only Time')
            ],
            [
                'value' => \Webkul\Mpmembership\Model\Transaction::PRODUCTS,
                'label' => __('Only Number Of Products')
            ]
        ];
        return $data;
    }
}
