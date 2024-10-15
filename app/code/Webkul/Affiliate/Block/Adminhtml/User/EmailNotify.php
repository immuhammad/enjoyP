<?php
/**
 * Webkul Affiliate User EmailNotify
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Block\Adminhtml\User;

class EmailNotify extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Initialize Category Map Block.
     */
    public function _construct()
    {
        $this->_objectId = 'category_map_id';
        $this->_blockGroup = 'Webkul_Affiliate';
        $this->_controller = 'adminhtml_User';
        parent::_construct();
        $this->buttonList->remove('reset');
        $this->buttonList->update('save', 'label', __('Send mail'));
    }

    /**
     * Retrieve text for header element depending on loaded image.
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Send Mail To Affiliate');
    }

    /**
     * Check permission for passed action.
     * @param string $resourceId
     * @return bool
     */
    public function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Get form action URL.
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        if ($this->hasFormActionUrl()) {
            return $this->getData('form_action_url');
        }
        return $this->getUrl('*/*/emailnotify');
    }
}
