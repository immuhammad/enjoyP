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
 * Class OrderStatus
 */
class ResolutionType implements OptionSourceInterface
{
    /**
     * Get options.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = [
                            Data::RESOLUTION_REFUND => __("Refund"),
                            Data::RESOLUTION_REPLACE => __("Replace"),
                            Data::RESOLUTION_CANCEL => __("Cancel Items"),
                                
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
