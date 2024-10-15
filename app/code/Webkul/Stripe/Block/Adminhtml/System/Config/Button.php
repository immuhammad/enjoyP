<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Stripe
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Stripe\Block\Adminhtml\System\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Button extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var return button template
     */
    protected $_template = 'Webkul_Stripe::system/config/button.phtml';

    /**
     * Constructor
     *
     * @param Template\Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Render method
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Get html element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Get ajax URL
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('stripe/system/webhook');
    }

    /**
     * Get button html code
     */
    public function getButtonHtml()
    {
        $webHookId = $this->scopeConfig
        ->getValue('payment/stripe/webhook_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $secretkey = $this->scopeConfig
        ->getValue('payment/stripe/api_secret_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (!$webHookId && $secretkey) {
            $button = $this->getLayout()->createBlock(
                \Magento\Backend\Block\Widget\Button::class
            )->setData(
                [
                    'id' => 'webhooks',
                    'label' => __('Generate Webhooks'),
                ]
            );
    
            return $button->toHtml();
        }
        return '';
    }
}
