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
namespace Webkul\MpVendorAttributeManager\Model\Config\Source;

class Content implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Agreements list getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $content = [
            [
                'value'=> 0,
                'label'=> __('Text')
            ],
            [
                'value'=> 1,
                'label'=> __('HTML')
            ]
        ];
        return $content;
    }
}
