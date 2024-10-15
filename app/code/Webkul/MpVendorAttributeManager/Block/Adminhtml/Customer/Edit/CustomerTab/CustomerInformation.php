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
namespace Webkul\MpVendorAttributeManager\Block\Adminhtml\Customer\Edit\CustomerTab;

class CustomerInformation extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Webkul\MpVendorAttributeManager\Model\VendorAttributeFactory
     */
    protected $vendorAttributeFactory;

    public const JS_TEMPLATE = 'customfields/customer/customer.phtml';
    
    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Webkul\MpVendorAttributeManager\Model\VendorAttributeFactory $vendorAttributeFactory
     * @param Array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Webkul\MpVendorAttributeManager\Model\VendorAttributeFactory $vendorAttributeFactory,
        array $data = []
    ) {
        $this->vendorAttributeFactory = $vendorAttributeFactory;
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
     * Get Customer Assigned Attributes
     *
     * @return Collection $customerAttributes
     */
    public function getCustomerAttributes()
    {
        $attributeUsedForCustomer = [0,1];
        $customerAttributes = $this->vendorAttributeFactory->create()->getCollection()
                                   ->addFieldToFilter("attribute_used_for", ["in" => $attributeUsedForCustomer])
                                   ->addFieldToFilter("wk_attribute_status", "1");

        return $customerAttributes;
    }
}
