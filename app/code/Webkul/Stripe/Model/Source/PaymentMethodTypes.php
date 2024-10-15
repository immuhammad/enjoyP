<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package   Webkul_Stripe
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Stripe\Model\Source;

class PaymentMethodTypes implements \Magento\Framework\Option\ArrayInterface
{
    public const ALIPAY = 'alipay';
    public const CARD = 'card';
    public const IDEAL = 'ideal';
    public const FPX = 'fpx';
    public const BACS_DEBIT = 'bacs_debit';
    public const BANCONECT = 'bancontact';
    public const GIROPAY = 'giropay';
    public const P24 = 'p24';
    public const EPS = 'eps';
    public const SOFORT = 'sofort';
    public const SEPA_DEBIT = 'sepa_debit';

    /**
     * Possible environment types.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::ALIPAY,
                'label' => __('ALIPAY'),
            ],
            [
                'value' => self::CARD,
                'label' => __('CARD'),
            ],
            [
                'value' => self::IDEAL,
                'label' => __('IDEAL'),
            ],
            [
                'value' => self::FPX,
                'label' => __('FPX'),
            ],
            [
                'value' => self::BACS_DEBIT,
                'label' => __('BACS DEBIT'),
            ],
            [
                'value' => self::GIROPAY,
                'label' => __('GIROPAY'),
            ],
            [
                'value' => self::P24,
                'label' => __('P24'),
            ],
            [
                'value' => self::EPS,
                'label' => __('EPS'),
            ],
            [
                'value' => self::SOFORT,
                'label' => __('SOFORT'),
            ],
            [
                'value' => self::SEPA_DEBIT,
                'label' => __('SEPA DEBIT'),
            ],
        ];
    }
}
