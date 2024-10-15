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
namespace Webkul\MpVendorAttributeManager\Block\Adminhtml\Group\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('vendor_group_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Attribute Group Information'));
    }

    /**
     * Prepare Layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $block = \Webkul\MpVendorAttributeManager\Block\Adminhtml\Group\Edit\Tab\Main::class;
        $attrBlock = \Webkul\MpVendorAttributeManager\Block\Adminhtml\Group\Edit\Tab\Attributes::class;
        $this->addTab(
            'main',
            [
                'label' => __('Group Details'),
                'content' => $this->getLayout()->createBlock($block, 'main')->toHtml(),
            ]
        );
        $this->addTab(
            'attributes',
            [
                'label' => __('Assign Attribtues'),
                'content' => $this->getLayout()
                            ->createBlock($attrBlock, 'group.attribute.grid')->toHtml()
            ]
        );
        return parent::_prepareLayout();
    }
}
