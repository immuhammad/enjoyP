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

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Function _construct
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('vendor_attribute_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Attribute Information'));
    }

    /**
     * Function _beforeToHtml
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'main',
            [
                'label' => __('Properties'),
                'title' => __('Properties'),
                'content' => $this->getChildHtml('main'),
                'active' => true
            ]
        );
        $this->addTab(
            'labels',
            [
                'label' => __('Manage Labels'),
                'title' => __('Manage Labels'),
                'content' => $this->getChildHtml('labels')
            ]
        );
        return parent::_beforeToHtml();
    }
}
