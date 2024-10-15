<?php
/**
 * Webkul Affiliate Order Status Options UI
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Ui\Component\Listing\AffiliateStatus;

use Magento\Framework\Data\OptionSourceInterface;

class Options implements OptionSourceInterface
{
    /**
     * @var array
     */
    private $options;

    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options = [
                                ['label' => __('Not Approved'), 'value' => 0],
                                ['label' => __('Rejected'), 'value' => 2],
                                ['label' => __('Approved'), 'value' => 1]
                            ];
        }
        return $this->options;
    }
}
