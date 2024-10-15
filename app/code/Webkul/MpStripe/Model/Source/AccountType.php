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

class AccountType implements \Magento\Framework\Option\ArrayInterface
{
    public const INDIVIDUAL = 'individual';
    public const COMPANY = 'company';

    /**
     * Possible environment types.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => '',
                'label' => __("Please Select"),
            ],
            [
                'value' => self::INDIVIDUAL,
                'label' => __('Individual'),
            ],
            [
                'value' => self::COMPANY,
                'label' => __('Company'),
            ],
        ];
    }
}
