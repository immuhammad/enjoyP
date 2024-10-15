<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_Stripe
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Stripe\Model\Source;

class Environment implements \Magento\Framework\Option\ArrayInterface
{
    public const ENVIRONMENT_PRODUCTION = 'production';
    public const ENVIRONMENT_SANDBOX = 'sandbox';

    /**
     * Possible environment types.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::ENVIRONMENT_SANDBOX,
                'label' => __('Sandbox'),
            ],
            [
                'value' => self::ENVIRONMENT_PRODUCTION,
                'label' => __('Production'),
            ],
        ];
    }
}
