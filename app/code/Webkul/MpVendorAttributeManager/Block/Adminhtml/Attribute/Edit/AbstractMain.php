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
namespace Webkul\MpVendorAttributeManager\Block\Adminhtml\Attribute\Edit;

abstract class AbstractMain extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Attribute instance
     *
     * @var Attribute
     */
    protected $_attribute = null;

    /**
     * @var \Magento\Config\Model\Config\Source\YesnoFactory
     */
    protected $_yesnoFactory;

    /**
     * @var \Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory
     */
    protected $_inputTypeFactory;

    /**
     * @var \Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker
     */
    protected $propertyLocker;

    /**
     * @var \Magento\Eav\Helper\Data
     */
    protected $_eavData = null;

    /**
     * @var \Webkul\MpVendorAttributeManager\Model\VendorGroupFactory
     */
    protected $_vendorGroups;

    /**
     * @var \Webkul\MpVendorAttributeManager\Model\VendorAttribute\Source\AttributeUsedFor
     */
    protected $usedFor;

    /**
     * @var \Webkul\MpVendorAttributeManager\Model\VendorAttribute\Source\Status
     */
    protected $attributeStatus;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Eav\Helper\Data $eavData
     * @param \Magento\Config\Model\Config\Source\YesnoFactory $yesnoFactory
     * @param \Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory $inputTypeFactory
     * @param \Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker $propertyLocker
     * @param \Webkul\MpVendorAttributeManager\Model\VendorAttribute\Source\VendorGroups $vendorGroups
     * @param \Webkul\MpVendorAttributeManager\Model\VendorAttribute\Source\AttributeUsedFor $usedFor
     * @param \Webkul\MpVendorAttributeManager\Model\VendorAttribute\Source\Status $attributeStatus
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Eav\Helper\Data $eavData,
        \Magento\Config\Model\Config\Source\YesnoFactory $yesnoFactory,
        \Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory $inputTypeFactory,
        \Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker $propertyLocker,
        \Webkul\MpVendorAttributeManager\Model\VendorAttribute\Source\VendorGroups $vendorGroups,
        \Webkul\MpVendorAttributeManager\Model\VendorAttribute\Source\AttributeUsedFor $usedFor,
        \Webkul\MpVendorAttributeManager\Model\VendorAttribute\Source\Status $attributeStatus,
        array $data = []
    ) {
        $this->propertyLocker = $propertyLocker;
        $this->_eavData = $eavData;
        $this->_yesnoFactory = $yesnoFactory;
        $this->_inputTypeFactory = $inputTypeFactory;
        $this->_vendorGroups = $vendorGroups;
        $this->usedFor = $usedFor;
        $this->attributeStatus = $attributeStatus;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Return attribute object from Registry
     *
     * @return Attribute
     */
    public function getattributeObject()
    {
        if (null === $this->_attribute) {
            return $this->_coreRegistry->registry('entity_attribute');
        }
        return $this->_attribute;
    }

    /**
     * Set attribute object
     *
     * @param Attribute $attribute
     * @return $this
     */
    public function setattributeObject($attribute)
    {
        $this->_attribute = $attribute;
        return $this;
    }

    /**
     * Preparing default form elements for editing attribute
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $attributeObj = $this->getattributeObject();
        $usedInForms = $attributeObj->getUsedInForms();

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('customfields_');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Attribute Properties'), 'collapsable' => true]
        );

        if ($attributeObj->getAttributeId()) {
            $fieldset->addField('attribute_id', 'hidden', ['name' => 'attribute_id']);
        }

        $this->_addElementTypes($fieldset);

        $yesno = $this->_yesnoFactory->create()->toOptionArray();

        $attributeTypes = $this->_inputTypeFactory->create()->toOptionArray();
        $notAllowed = ['texteditor', 'datetime', 'pagebuilder'];
        foreach ($attributeTypes as $attributeKey => $attributeData) {
            if (in_array($attributeData['value'], $notAllowed)) {
                unset($attributeTypes[$attributeKey]);
            }
        }

        $validationClass = sprintf(
            'validate-code validate-length maximum-length-25 validate-no-html-tags'
        );

        $label = $attributeObj->getFrontendLabel();
        $fieldset->addField(
            'attribute_label',
            'text',
            [
                'name' => 'frontend_label[0]',
                'label' => __('Default Label'),
                'title' => __('Default Label'),
                'required' => true,
                'value' => is_array($label) ? $label[0] : $label,
                'class' => "validate-no-html-tags"
            ]
        );

        $fieldset->addField(
            'attribute_code',
            'text',
            [
                'name' => 'attribute_code',
                'label' => __('Attribute Code'),
                'title' => __('Attribute Code'),
                'note' => __(
                    'Make sure you don\'t use spaces or more than 25 characters.'
                ),
                'class' => $validationClass,
                'required' => true
            ]
        );

        $fieldset->addField(
            'frontend_input',
            'select',
            [
                'name' => 'frontend_input',
                'label' => __('Frontend Input Type'),
                'title' => __('Frontend Input Type'),
                'value' => 'text',
                'values' => $attributeTypes
            ]
        );

        $fieldset->addField(
            'is_required',
            'select',
            [
                'name' => 'is_required',
                'label' => __('Values Required'),
                'title' => __('Values Required'),
                'values' => $yesno
            ]
        );

        if ($attributeObj->getFrontendInput() != 'date') {
            $fieldset->addField(
                'frontend_class',
                'select',
                [
                    'name' => 'frontend_class',
                    'label' => __('Input Validation'),
                    'title' => __('Input Validation'),
                    'values' => $this->_eavData->getFrontendClasses($attributeObj->getEntityType()->getEntityTypeCode())
                ]
            );
        }

        $fieldset->addField(
            'sort_order',
            'text',
            [
                'name' => 'sort_order',
                'label' => __('Input Field Sort Order'),
                'title' => __('Input Field Sort Order'),
                'required' => true,
                'class' => "validate-number"
            ]
        );

        $fieldset->addField(
            'assign_group',
            'select',
            [
                'name' => 'assign_group',
                'label' => __('Assign Group'),
                'title' => __('Assign Group'),
                'value' => $this->_coreRegistry->registry('attribute_group'),
                'values' => $this->_vendorGroups->toOptionArray()
            ]
        );

        $fieldset->addField(
            'attribute_used_for',
            'select',
            [
                'name' => 'attribute_used_for',
                'label' => __('Attribute Used For'),
                'title' => __('Attribute Used For'),
                'required' => true,
                'value' => $this->_coreRegistry->registry('attribute_used_for'),
                'values' => $this->usedFor->toOptionArray()
            ]
        );

        $fieldset->addField(
            'wk_attribute_status',
            'select',
            [
                'name' => 'wk_attribute_status',
                'label' => __('Attribute Status'),
                'title' => __('Attribute Status'),
                'required' => true,
                'value' => $this->_coreRegistry->registry('wk_attribute_status'),
                'values' => ['1' => __('Enable'), '0' => __('Disable')]
            ]
        );

        $this->propertyLocker->lock($form);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Initialize form fileds values
     *
     * @return $this
     */
    protected function _initFormValues()
    {
        $this->_eventManager->dispatch(
            'adminhtml_block_eav_attribute_edit_form_init',
            ['form' => $this->getForm()]
        );
        $formData = $this->getattributeObject()->getData();
        $formData['frontend_class'] = $formData['frontend_class'] ?? '';
        $formData['frontend_class'] = trim(preg_replace('/required/', '', $formData['frontend_class']));

        $this->getForm()->addValues($formData);
        return parent::_initFormValues();
    }

    /**
     * Processing block html after rendering
     *
     * Adding js block to the end of this block
     *
     * @param   string $html
     * @return  string
     */
    protected function _afterToHtml($html)
    {
        $jsScripts = $this->getLayout()->createBlock(\Magento\Eav\Block\Adminhtml\Attribute\Edit\Js::class)->toHtml();
        return $html . $jsScripts;
    }
}
