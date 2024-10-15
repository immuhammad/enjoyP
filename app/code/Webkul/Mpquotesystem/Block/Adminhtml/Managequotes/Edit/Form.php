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

namespace Webkul\Mpquotesystem\Block\Adminhtml\Managequotes\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

    /**
     * Init form.
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('edit_form');
        $this->setTitle(__('Manage quotes'));
    }

    /**
     * Prepare form.
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            [
                'data' => [
                'id' => 'edit_form',
                'action' => $this->getData('action'),
                'method' => 'post',
                'enctype' => 'multipart/form-data',
                ],
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
