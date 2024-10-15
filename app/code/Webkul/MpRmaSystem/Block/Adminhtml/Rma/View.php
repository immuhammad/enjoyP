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
namespace Webkul\MpRmaSystem\Block\Adminhtml\Rma;

class View extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @var string
     */
    protected $_blockGroup = 'Webkul_MpRmaSystem';

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_rma';
        $this->_mode = 'view';
        parent::_construct();
        $this->buttonList->remove('delete');
        $this->buttonList->remove('reset');
        $this->buttonList->remove('save');
        $this->setId('mprmasystem_rma_view');
    }

    /**
     * Prepare layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $rmaId = $this->getRequest()->getParam('id');
        $message = __('Click Ok to Print the Rma');
        $addButtonProps = [
            'id' => 'wk_print_rma',
            'label' => __('Print Rma'),
            'class' => 'secondary',
            'onclick' => "confirmSetLocation('{$message}', '{$this->getPdfUrl($rmaId)}')",
        ];
        $this->buttonList->add('add_new', $addButtonProps);
        return parent::_prepareLayout();
    }

    /**
     * Get pdf url
     *
     * @param int $rmaId
     * @return string
     */
    public function getPdfUrl($rmaId)
    {
        return $this->getUrl(
            'mprmasystem/rma/printpdf/',
            [
                'rma_id'=>$rmaId,
                '_secure' => $this->getIsSecure()
            ]
        );
    }
}
