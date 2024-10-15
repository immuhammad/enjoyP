<?php
/**
 * Webkul Affiliate Model Config AffiliateAllowedPaymentMethodsList
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class AffiliateAllowedPaymentMethodsList extends AbstractSource
{
    /**
     * Return options array.
     * @return array
     */
    public function toOptionArray()
    {
        $mode = [
            ['value' => "checkmo",  'label' => __('Check / Money order')],
            ['value' => "banktransfer",  'label' =>__('Bank Transfer Payment')],
            ['value' => "paypal_standard",  'label' => __('PayPal Standard Payment')]
        ];

        return $mode;
    }

    /**
     * Get options in "key-value" format.
     *
     * @return array
     */
    public function toArray()
    {
        $optionList = $this->toOptionArray();
        $optionArray = [];
        foreach ($optionList as $option) {
            $optionArray[$option['value']] = $option['label'];
        }
        return $optionArray;
    }

    /**
     * Return options array.
     * @return array
     */
    public function getAllOptions()
    {
        return $this->toOptionArray();
    }

    /**
     * Get a text for option value.
     * @param string|int $value
     * @return string|bool
     */
    public function getOptionText($value)
    {
        foreach ($this->getAllOptions() as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }
}
