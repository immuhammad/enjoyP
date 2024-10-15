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
namespace Webkul\MpVendorAttributeManager\Plugin\Customer;

use Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAttribute\CollectionFactory as VendorAttrCollection;

class Save
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAttribute\Collection
     */
    protected $vendorAttributeCollection;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory
     */
    protected $attributeCollection;

    /**
     * Customer model
     *
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Eav\Model\Entity $eavEntity
     * @param VendorAttrCollection $vendorAttrCollection
     * @param \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Eav\Model\Entity $eavEntity,
        VendorAttrCollection $vendorAttrCollection,
        \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->_request = $request;
        $this->_eavEntity = $eavEntity;
        $this->customerFactory = $customerFactory;
        $this->vendorAttributeCollection = $vendorAttrCollection;
        $this->_attributeCollection = $attributeCollection;
    }

    /**
     * Function beforeExecute
     *
     * @param \Magento\Customer\Controller\Adminhtml\Index\Save $subject
     * @return void
     */
    public function beforeExecute(\Magento\Customer\Controller\Adminhtml\Index\Save $subject)
    {
        $customerData = $this->_request->getPostValue();
        if (isset($customerData['customer']['entity_id'])) {
            $customerId = $customerData['customer']['entity_id'];
            $customerDataKeys = array_keys($customerData['customer']);
            $customer = $this->customerFactory->create()->load($customerId);

            $customAttributes = $this->getAttributeCollection();
            if (!empty($customAttributes)) {
                foreach ($customAttributes as $attribute) {
                    if (!in_array($attribute->getAttributeCode(), $customerDataKeys)) {
                        $customerData['customer'][$attribute->getAttributeCode()] =
                            $attribute->getFrontend()->getValue($customer);
                    }
                }
            }
        }

        $this->_request->setPostValue($customerData);
    }

    /**
     * Function getAttributeCollection
     *
     * @return object
     */
    private function getAttributeCollection()
    {
        try {
            $vendorCollection = $this->vendorAttributeCollection->create();
            $vendorAttribute = $vendorCollection->getTable('marketplace_vendor_attribute');

            $typeId = $this->_eavEntity->setType('customer')->getTypeId();
            $collection = $this->_attributeCollection->create()
               ->setEntityTypeFilter($typeId)
               ->setOrder('sort_order', 'ASC');

            $collection->getSelect()
            ->join(
                ["vendor_attr" => $vendorAttribute],
                "vendor_attr.attribute_id = main_table.attribute_id"
            );

            return $collection;
        } catch (\Exception $ex) {
            return false;
        }

        return false;
    }
}
