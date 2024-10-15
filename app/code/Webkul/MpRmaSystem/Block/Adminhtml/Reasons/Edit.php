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
namespace Webkul\MpRmaSystem\Block\Adminhtml\Reasons;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * Initialize Dependencies
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @return void
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Construct
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'reason_id';
        $this->_blockGroup = 'Webkul_MpRmaSystem';
        $this->_controller = 'adminhtml_reasons';
        parent::_construct();
        if ($this->_isAllowedAction('Webkul_MpRmaSystem::reasons')) {
            $this->buttonList->update('save', 'label', __('Save Reason'));
        } else {
            $this->buttonList->remove('save');
        }

        $this->buttonList->remove('delete');
    }

    /**
     * Retrieve text for header element depending on loaded image.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->coreRegistry->registry('mprmasystem_reasons')->getId()) {
            $title = $this->coreRegistry
                        ->registry('mprmasystem_reasons')->getReason();
            $title = $this->escapeHtml($title);
            return __("Edit Reason '%'", $title);
        } else {
            return __('New Reason');
        }
    }

    /**
     * Check permission for passed action.
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
