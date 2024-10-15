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

class Enabledisable extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * Option values
     */
    public const VALUE_ENABLE = 1;

    public const VALUE_DISABLE = 0;
    
    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['label' => __('Enable'), 'value' => self::VALUE_ENABLE],
                ['label' => __('Disable'), 'value' => self::VALUE_DISABLE],
            ];
        }
        return $this->_options;
    }
}
