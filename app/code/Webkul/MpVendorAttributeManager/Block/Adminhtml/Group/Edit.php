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
namespace Webkul\MpVendorAttributeManager\Block\Adminhtml\Group;

use Magento\Backend\Block\Widget\Form\Container;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

class Edit extends Container
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
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry           $registry
     * @param array                                 $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'attribute_id';
        $this->_blockGroup = 'Webkul_MpVendorAttributeManager';
        $this->_controller = 'adminhtml_group';

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
        $this->buttonList->update('save', 'label', __('Save Group'));
        $this->buttonList->update('save', 'class', 'save primary');
    }

    /**
     * Fetch edit model from registery
     *
     * @return \Webkul\MpVendorAttributeManager\Model\VendorGroup
     */
    public function getModel()
    {
        return $this->_coreRegistry->registry('vendor_group');
    }
    
    /**
     * Retrieve header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        if ($this->getModel()->getId()) {
            $frontendLabel = $this->_coreRegistry->registry('entity_attribute')->getFrontendLabel();
            if (is_array($frontendLabel)) {
                $frontendLabel = $frontendLabel[0];
            }
            return __('Edit Vendor Attribute "%1"', $this->escapeHtml($frontendLabel));
        }
        return __('New Vendor Attribute');
    }

     /**
      * Retrieve URL for "Save and Continue" button
      *
      * @return string
      */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl(
            'vendorattribute/*/save',
            ['_current' => true, 'back' => 'edit']
        );
    }

    /**
     * Retrieve URL for "Save" button
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl(
            'vendorattribute/*/save',
            ['_current' => true, 'back' => null]
        );
    }
}
