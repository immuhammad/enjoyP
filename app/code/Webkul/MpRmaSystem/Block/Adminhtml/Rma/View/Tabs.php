<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpRmaSystem
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpRmaSystem\Block\Adminhtml\Rma\View;

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
        $this->setId('mprmasystem_rma_view_tabs');
        $this->setDestElementId('mprmasystem_rma_view');
        $this->setTitle(__('RMA Details'));
    }

    /**
     * Prepare Layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $block = \Webkul\MpRmaSystem\Block\Adminhtml\Rma\View\Tab\Details::class;
        $url = $this->getUrl('*/*/conversation', ['_current' => true]);
        $this->addTab(
            'rma_info',
            [
                'label' => __('Information'),
                'content' => $this->getLayout()
                                ->createBlock($block, 'rma_info')
                                ->toHtml(),
            ]
        );
        $this->addTab(
            'rma_conversation',
            [
                'label' => __('Conversation'),
                'url' => $url,
                'class' => 'ajax'
            ]
        );
        $this->addTab(
            'rma_product',
            [
                'label' => __('Product Details'),
                'url' => $this->getUrl('*/*/product', ['_current' => true]),
                'class' => 'ajax'
            ]
        );
        return parent::_prepareLayout();
    }
}
