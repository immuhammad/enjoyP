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
use Magento\Framework\Json\EncoderInterface;
use Magento\Backend\Model\Auth\Session;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Construct function to set ID
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('managequotes_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Manage Quotes Information'));
    }
    
    /**
     * AddStoreFilter
     *
     * @param object  $store
     * @param boolean $withAdmin
     * @return void
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            "form_section",
            [
                "label"     =>  __("Marketplace Quote Manager"),
                "alt"       =>  __("marketplace Quote Manager"),
                "content"   =>  $this->getLayout()
                    ->createBlock(\Webkul\Mpquotesystem\Block\Adminhtml\EditQuotes::class)
                    ->setTemplate("Webkul_Mpquotesystem::form.phtml")->toHtml()
            ]
        );
        $this->addTab(
            'conversation',
            [
                'label' => __('Quote Conversation'),
                'url' => $this->getUrl('mpquotesystem/*/grid', ['_current' => true]),
                'class' => 'ajax',
            ]
        );
        return parent::_beforeToHtml();
    }
}
