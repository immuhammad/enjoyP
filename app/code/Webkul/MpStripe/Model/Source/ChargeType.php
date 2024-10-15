<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpStripe
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpStripe\Model\Source;

class ChargeType implements \Magento\Framework\Option\ArrayInterface
{
    public const DIRECT_CHARGE = 'direct';
    public const SEPARATE_CHARGE_AND_TRANSFER = 'separate';

    /**
     * Possible environment types.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::DIRECT_CHARGE,
                'label' => __('Direct Charge'),
            ],
            [
                'value' => self::SEPARATE_CHARGE_AND_TRANSFER,
                'label' => __('Separate Charge and Transfer'),
            ],
        ];
    }
}
