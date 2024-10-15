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

/**
 * Generic source
 */
class Animation
{
    /**
     * Carrier code
     *
     * @var string
     */
    protected $code = '';
    /**
     * Returns array to be used in multiselect on back-end
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            ['value'=>'popup', 'label'=> __('Popup')],
            ['value'=>'slide', 'label'=> __('Slide')],
        ];
        
        return $options;
    }
}
