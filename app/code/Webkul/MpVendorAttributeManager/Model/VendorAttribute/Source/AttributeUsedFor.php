<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpVendorAttributeManager
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpVendorAttributeManager\Model\VendorAttribute\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class AttributeUsedFor
 *
 * Webkul\MpVendorAttributeManager\Model\VendorAttribute\Source
 * */
class AttributeUsedFor implements OptionSourceInterface
{
    /**
     * @var \Webkul\MpVendorAttributeManager\Model\VendorAttribute
     */
    protected $vendorGroup;

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            ['value' => '', 'label' => __('Select')],
            ['value' => '0', 'label' => __('Both')],
            ['value' => '1', 'label' => __('Customer')],
            ['value' => '2', 'label' => __('Seller')]
        ];
        return $options;
    }
}
