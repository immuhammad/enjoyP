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
namespace Webkul\MpVendorAttributeManager\Block\Adminhtml\Attribute;

/**
 * Product attribute edit page
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Block group name
     *
     * @var string
     */
    protected $_blockGroup = 'Webkul_MpVendorAttributeManager';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context, $data);
    }

    /**
     * Function _construct
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'attribute_id';
        $this->_controller = 'adminhtml_attribute';

        parent::_construct();

        $this->addButton(
            'save_and_edit_button',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                    ],
                ]
            ]
        );

        $this->buttonList->update('save', 'label', __('Save Attribute'));
        $this->buttonList->update('save', 'class', 'save primary');

        $entityAttribute = $this->_coreRegistry->registry('entity_attribute');
        if (!$entityAttribute || !$entityAttribute->getIsUserDefined()) {
            $this->buttonList->remove('delete');
        } else {
            $this->buttonList->update('delete', 'label', __('Delete Attribute'));
        }
    }

    /**
     * Prepare form Html.
     *
     * @return String
     */
    public function getFormHtml()
    {
        $html = parent::getFormHtml();
        $html .= $this->setTemplate('Webkul_MpVendorAttributeManager::customfields/dependable.phtml')->toHtml();
        return $html;
    }

    /**
     * Get Model from Current Registry.
     *
     * @return Object
     */
    public function getModel()
    {
        return $this->_coreRegistry->registry('entity_attribute');
    }

    /**
     * Get Dependent Fields from Current Registry.
     *
     * @return Object
     */
    public function getDependableModel()
    {
        return $this->_coreRegistry->registry('vendor_dependfields');
    }
    
    /**
     * Prepare the layout.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {

        $this->_formScripts[] = "
            require([
                'jquery',
                'mage/mage',
                'knockout'
            ], function ($){
                $('#customfields_attribute_code').on('keyup',function(){
                   $(this).val($(this).val().replace(/\s+/g, '_'));
                });
                $('body').on('keyup','#customfields_dependable_inputname',function(){
                   $(this).val($(this).val().replace(/\s+/g, '_'));
                });
            });
               
        ";
        return parent::_prepareLayout();
    }

    /**
     * Retrieve the header text
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('entity_attribute')->getId()) {
            $frontendLabel = $this->_coreRegistry->registry('entity_attribute')->getFrontendLabel();
            if (is_array($frontendLabel)) {
                $frontendLabel = $frontendLabel[0];
            }
            return __('Edit Vendor Attribute "%1"', $this->escapeHtml($frontendLabel));
        }
        return __('New Vendor Attribute');
    }

    /**
     * Retrieve the save and continue edit Url.
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl(
            'vendorattribute/*/save',
            ['_current' => true, 'back' => 'edit', 'active_tab' => '{{tab_id}}']
        );
    }

    /**
     * Retrieve the save Url.
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl(
            'vendorattribute/*/save',
            ['_current' => true, 'back' => null, 'active_tab' => '{{tab_id}}']
        );
    }

    /**
     * Retrieve customer validation Url.
     *
     * @return string
     */
    public function getValidationUrl()
    {
        return $this->getUrl('vendorattribute/*/validate', ['_current' => true]);
    }

    /**
     * Function get JsonHelper
     *
     * @return object
     */
    public function getJsonHelper()
    {
        return $this->jsonHelper;
    }
}
