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
namespace Webkul\MpVendorAttributeManager\Block\Adminhtml\Attribute\Edit;

use Magento\Store\Model\ResourceModel\Store\Collection;

class Options extends \Magento\Eav\Block\Adminhtml\Attribute\Edit\Options\Options
{
    /**
     * @var string
     */
    protected $_template = 'Webkul_MpVendorAttributeManager::customfields/options.phtml';

    /**
     * Retrieve attribute object from registry
     *
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    protected function getAttributeObject()
    {
        return $this->_registry->registry('entity_attribute');
    }
}
