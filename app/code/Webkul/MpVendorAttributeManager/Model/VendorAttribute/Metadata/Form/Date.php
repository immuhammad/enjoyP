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

namespace Webkul\MpVendorAttributeManager\Model\VendorAttribute\Metadata\Form;

class Date extends \Magento\Customer\Model\Metadata\Form\Date
{
    /**
     * @inheritdoc
     */
    public function extractValue(\Magento\Framework\App\RequestInterface $request)
    {
        $value = $this->_getRequestValue($request);
        if ($value) {
            $attributeCode = $this->getAttribute()->getAttributeCode();
            $explodedValue = explode('_', $attributeCode);
            if (is_array($explodedValue)) {
                if ('wkv' == $explodedValue[0]) {
                    $value = date('m/d/Y', strtotime($value));
                }
            }
        }
        return $this->_applyInputFilter($value);
    }
}
