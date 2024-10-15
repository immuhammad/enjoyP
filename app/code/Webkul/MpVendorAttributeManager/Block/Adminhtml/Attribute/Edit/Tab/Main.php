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
namespace Webkul\MpVendorAttributeManager\Block\Adminhtml\Attribute\Edit\Tab;

use Webkul\MpVendorAttributeManager\Block\Adminhtml\Attribute\Edit\AbstractMain;

/**
 * Block Class Attribute Tab Main
 */
class Main extends AbstractMain
{
    /**
     * Adding product form elements for editing attribute
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $attributeObject = $this->getAttributeObject();
        $form = $this->getForm();
        $fieldset = $form->getElement('base_fieldset');

        $frontendInputElement = $form->getElement('frontend_input');
        $additionalTypes = [
            ['value' => 'image', 'label' => __('Media Image')],
            ['value' => 'file', 'label' => __('File')],
        ];
        $frontendInputValues = array_merge($frontendInputElement->getValues(), $additionalTypes);
        $frontendInputElement->setValues($frontendInputValues);
        return $this;
    }

    /**
     * Retrieve additional element types for product attributes
     *
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        return ['apply' => \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Apply::class];
    }
}
