<?php
/**
 * Webkul Service Fee Type Source Model.
 * @category  Webkul
 * @package   Webkul_MpServiceFee
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpServiceFee\Model\Source\Config;

class Options extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * Option values
     */
    public const VALUE_FIXED = 0;

    public const VALUE_PERCENTAGE = 1;
    
    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['label' => __('Fixed'), 'value' => self::VALUE_FIXED],
                ['label' => __('Percentage'), 'value' => self::VALUE_PERCENTAGE],
            ];
        }
        return $this->_options;
    }
}
