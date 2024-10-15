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

namespace Webkul\Mpquotesystem\Block\Adminhtml\Managequotes;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry           $registry
     * @param array                                 $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_coreRegistry = $registry;
    }

    /**
     * Initialize edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'entity_id';
        $this->_blockGroup = 'Webkul_Mpquotesystem';
        $this->_controller = 'adminhtml_managequotes';
        parent::_construct();
        $quoteId = $this->getRequest()->getParam('entity_id');
        $flag = 0;
        if ($this->_isAllowedAction('Webkul_Mpquotesystem::mpquotes')) {
            $this->buttonList->update('save', 'label', __('Update Quote'));
        } else {
            $this->buttonList->remove('save');
        }
        if ($this->_isAllowedAction('Webkul_Mpquotesystem::mpquotes')) {
            $this->buttonList->update(
                'delete',
                'label',
                __('Delete Quote')
            );
        } else {
            $this->buttonList->remove('delete');
        }
    }

    /**
     * Retrieve text for header element depending on loaded post
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        $codRegistry = $this->_coreRegistry->registry('quote_data');
        $quoteData = $this->escapeHtml($codRegistry);
        if ($quoteData->getEntityId()) {
            return __("Edit quote '%1'", $quoteData->getEntityId());
        } else {
            return __('New Quote');
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
