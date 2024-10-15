<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpquotesystem
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpquotesystem\Block\Adminhtml\Managequotes\Edit\Tab;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('edit_form');
        $this->setTitle(__('Marketplace Quotes'));
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('quote_data');
        $form = $this->_formFactory->create(
            ['data' =>
                ['id' => 'edit_form',
                'action' => $this->getData('action'),
                'method' => 'post']
            ]
        );
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('General Information'), 'class' => 'fieldset-wide']
        );
        if ($model->getEntityId()) {
            $fieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        }
        $fieldset->addField(
            'quote_price',
            'text',
            ['name' => 'quote_price',
            'label' => __('Quote Price'),
            'required' => true,
            'placeholder'=>__('Quote Price')
            ]
        );
        $fieldset->addField(
            'quote_qty',
            'text',
            ['name' => 'quote_qty',
                'label' => __('Quote Quantity'),
                'required' => true,
                'placeholder'=>__('Quote Quantity')
            ]
        );
        $fieldset->addField(
            'product_id',
            'text',
            ['name' => 'product_id',
                'label' => __('Product Id'),
                'required' => true,
                'placeholder'=>__('Product Id')
            ]
        );
        $fieldset->addField(
            'product_name',
            'text',
            ['name' => 'product_name',
                'label' => __('Product Name'),
                'required' => true,
                'placeholder'=>'Product Name'
            ]
        );
        $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'status',
                'required' => true,
                'options' => [
                    1 => __('Unapproved'),
                    2 => __('Approved'),
                    3=> __('Declined')
                ]
            ]
        );
        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
