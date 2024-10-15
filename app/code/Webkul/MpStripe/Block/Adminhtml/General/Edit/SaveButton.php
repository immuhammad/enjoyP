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
namespace Webkul\MpStripe\Block\Adminhtml\General\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class SaveButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * GetButtonData function
     *
     * @return array
     */
    public function getButtonData()
    {
        $sellerId = $this->context->getRequest()->getParam('seller_id');
        $stripeSeller = $this->stripeSeller->create()->getCollection()
        ->addFieldToFilter('seller_id', $sellerId)->getFirstItem();
        $label = $stripeSeller->getId()?false:__('Create Account');
        if ($label) {
            return [
                'label' => $label,
                'class' => 'save primary',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'save']],
                    'form-role' => 'save',
                ],
                'sort_order' => 90,
            ];
        }
    }
}
