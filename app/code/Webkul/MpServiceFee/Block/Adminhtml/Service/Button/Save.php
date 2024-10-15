<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpServiceFee
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpServiceFee\Block\Adminhtml\Service\Button;

class Save extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Generic
{
    /**
     * Get button data
     *
     * @return array
     */
    public function getButtonData()
    {
        $data = [
          'label' => __('Save'),
          'class' => 'save primary',
          'data_attribute' => [
              'mage-init' => ['button' => ['event' => 'save']],
              'form-role' => 'save',
          ],
          'sort_order' => 90,
        ];
        return $data;
    }
}
