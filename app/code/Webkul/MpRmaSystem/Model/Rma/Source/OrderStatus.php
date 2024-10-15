<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpRmaSystem
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpRmaSystem\Model\Rma\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Webkul\MpRmaSystem\Helper\Data;

/**
 * Class OrderStatus on Grid
 */
class OrderStatus implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = [
                                Data::ORDER_NOT_DELIVERED => __("Not Delivered"),
                                Data::ORDER_DELIVERED => __("Delivered"),
                                Data::ORDER_NOT_APPLICABLE => __("Not Applicable"),
                                
                            ];
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }

        return $options;
    }
}
