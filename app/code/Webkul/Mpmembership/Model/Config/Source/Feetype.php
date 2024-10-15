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
 * Used in creating options for getting type of fees paid by seller
 */
class Feetype
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $data =  [
            ['value' => '0', 'label' => __('Percent')],
            ['value' => '1', 'label' => __('Fixed')]
        ];
        return $data;
    }
}
