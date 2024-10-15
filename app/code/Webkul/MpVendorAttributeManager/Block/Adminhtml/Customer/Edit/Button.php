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
namespace Webkul\MpVendorAttributeManager\Block\Adminhtml\Customer\Edit;

class Button extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Webkul\MpVendorAttributeManager\Model\VendorAttributeFactory
     */
    protected $_vendorAttribute;

    public const JS_TEMPLATE = 'customfields/customer/js.phtml';
    
    /**
     * Undocumented function
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Webkul\MpVendorAttributeManager\Model\VendorAttributeFactory $vendorAttribute
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Webkul\MpVendorAttributeManager\Model\VendorAttributeFactory $vendorAttribute,
        array $data = []
    ) {
        $this->_vendorAttribute = $vendorAttribute;
        parent::__construct($context, $data);
    }
     
    /**
     * Set template to itself
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::JS_TEMPLATE);
        }
        return $this;
    }

    /**
     * Get Vendor Attributes for Seller.
     *
     * @return Collection $vendorAttributes
     */
    public function getVendorAttributes()
    {
        $attIdsForSeller = [2];
        $vendorAttributes = $this->_vendorAttribute->create()->getCollection()
                            ->addFieldToFilter("attribute_used_for", ["in" => $attIdsForSeller])
                            ->addFieldToFilter("wk_attribute_status", "1");
        return $vendorAttributes;
    }
}
