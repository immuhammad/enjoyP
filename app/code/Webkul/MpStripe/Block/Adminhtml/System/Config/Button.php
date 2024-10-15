<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpStripe
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpStripe\Block\Adminhtml\System\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Button extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var $_template string
     */
    protected $_template = 'Webkul_MpStripe::system/config/button.phtml';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
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
     * Render function
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * _getElementHtml function
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * GetAjaxUrl function
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('mpstripe/system/webhook');
    }

    /**
     * GetButtonHtml function
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $webHookId = $this->scopeConfig->getValue(
            'payment/mpstripe/webhook_id',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $secretkey = $this->scopeConfig->getValue(
            'payment/mpstripe/api_key',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (!$webHookId && $secretkey) {
            $button = $this->getLayout()->createBlock(
                \Magento\Backend\Block\Widget\Button::class
            )->setData(
                [
                    'id' => 'webhooks_stripe',
                    'label' => __('Generate Webhooks'),
                ]
            );
    
            return $button->toHtml();
        }
        return '';
    }
}
