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
namespace Webkul\MpVendorAttributeManager\Block\Adminhtml\Group\Edit\Tab;

use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Widget\Form\Generic;

class Main extends Generic implements TabInterface
{
    /**
     * Adding product form elements for editing attribute
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('vendor_group');
        /** @var DataForm $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' =>$this->getData('action'), 'method' => 'post']]
        );
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Group Information'), 'class' => 'fieldset-wide']
        );
        if ($model->getId()) {
            $fieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        }
        $fieldset->addField('attr_ids', 'hidden', ['name' => 'attr_ids', 'id' => 'attr_ids']);
        $fieldset->addField(
            'group_name',
            'text',
            ['name' => 'group_name', 'label' => __('Group Name'), 'title' => __('Group Name'), 'required' => true]
        );
        $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'status',
                'required' => true,
                'options' => ['1' => __('Enable'), '0' => __('Disable')]
            ]
        );
        $data = $model->getData();
        $form->setValues($data);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Group Details');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Group Details');
    }

    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isHidden()
    {
        return false;
    }
}
