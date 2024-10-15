<?php
/**
 * ImageOption.php
 *
 * @category  Webkul
 * @package   Webkul_Mpquotesystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpquotesystem\Model\Product\Attribute;

class Options extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $this->_options = [
            ['value' => 0, 'label' => __('Select')],
            ['value' => 1, 'label' => __('Yes')],
            ['value' => 2, 'label' => __('No')]
        ];
        return $this->_options;
    }
}
