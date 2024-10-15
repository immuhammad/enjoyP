<?php
/**
 * Webkul Software.
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Affiliate\Block\System\Config\Form;

class Button extends \Magento\Config\Block\System\Config\Form\Field
{
    const BUTTON_TEMPLATE = 'system/config/button.phtml';

    /**
     * Set template to itself.
     *
     * @return $this
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::BUTTON_TEMPLATE);
        }
        return $this;
    }

    /**
     * Render button.
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        // Remove scope label
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return ajax url for button.
     * @return string
     */
    public function getAjaxCheckUrl()
    {
        return $this->getUrl('affiliate/user/paymentnotifyupdate');
    }

    /**
     * Get the button and scripts contents.
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->addData(
            [
                'id' => 'payment_notify_button',
                'button_label' => __('Update Payment Notify'),
                'onclick' => 'javascript:check(); return false;',
            ]
        );
        return $this->_toHtml();
    }
}
