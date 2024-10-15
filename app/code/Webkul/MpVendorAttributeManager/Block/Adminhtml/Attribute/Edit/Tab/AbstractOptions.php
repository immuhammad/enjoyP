<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpVendorAttributeManager
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpVendorAttributeManager\Block\Adminhtml\Attribute\Edit\Tab;

abstract class AbstractOptions extends \Magento\Framework\View\Element\AbstractBlock
{
    /**
     * Preparing layout, adding buttons
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->addChild('options', \Webkul\MpVendorAttributeManager\Block\Adminhtml\Attribute\Edit\Options::class);
        return parent::_prepareLayout();
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    protected function _toHtml()
    {
        return $this->getChildHtml();
    }
}
