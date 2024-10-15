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

/**
 * Class SellerStatus on grid
 */
class SellerStatus implements OptionSourceInterface
{
    /**
     * Get options.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = [
                                __('Pending'),
                                __('Processing'),
                                __('Solved'),
                                __('Declined'),
                                __('Authorized')
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
