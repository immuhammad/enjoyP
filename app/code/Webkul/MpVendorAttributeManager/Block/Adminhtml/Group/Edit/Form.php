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

use Magento\Backend\Block\Widget\Form\Generic;

class Form extends Generic
{

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('group_form');
        $this->setTitle(__('Vendor Group Information'));
    }

    /**
     * Prepare Form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('vendor_group');
        $form = $this->_formFactory->create([
            'data' => [
                'id' => 'edit_form',
                'action' =>$this->getData('action'),
                'method' => 'post'
            ]
        ]);
        $form->setUseContainer(true);
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
