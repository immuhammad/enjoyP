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

use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Form\Field;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Ui\DataProvider\EavValidationRules;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Entity\Type;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Eav\Model\Config;
use Magento\Customer\Api\Data\CustomerInterface;
use Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAttribute\CollectionFactory as VendorAttrCollection;

class DataProvider
{
    /**
     * Maximum file size allowed for file_uploader UI component
     */
    public const MAX_FILE_SIZE = 2097152;

    /**
     * @var \Magento\Eav\Model\Entity
     */
    protected $eavEntity;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory
     */
    protected $attributeCollection;

    /**
     * @var EavValidationRules
     */
    protected $eavValidationRules;

    /**
     * EAV attribute properties to fetch from meta storage
     * @var array
     */
    protected $metaProperties = [
        'dataType' => 'frontend_input',
        'visible' => 'is_visible',
        'required' => 'is_required',
        'label' => 'frontend_label',
        'sortOrder' => 'sort_order',
        'notice' => 'note',
        'default' => 'default_value',
        'size' => 'multiline_count',
    ];

    /**
     * Form element mapping
     *
     * @var array
     */
    protected $formElement = [
        'text' => 'input',
        'hidden' => 'input',
        'boolean' => 'checkbox',
    ];

    /**
     * File types allowed for file_uploader UI component
     *
     * @var array
     */
    private $fileUploaderTypes = [
        'image',
        'file',
    ];

    /**
     * @var \Magento\Customer\Model\Config\Share
     */
    private $shareConfig;

    /**
     * @var \Magento\Customer\Model\AttributeFactory
     */
    protected $_attributeFactory;

    /**
     * Vendor attribute collection object
     *
     * @var \Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAttribute\Collection
     */
    protected $vendorAttributeCollection;

    /**
     * Constructor
     *
     * @param \Magento\Eav\Model\Entity $eavEntity
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection
     * @param EavValidationRules $eavValidationRules
     * @param Config $eavConfig
     * @param \Magento\Customer\Model\AttributeFactory $attributeFactory
     * @param VendorAttrCollection $vendorAttrCollection
     */
    public function __construct(
        \Magento\Eav\Model\Entity $eavEntity,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection,
        EavValidationRules $eavValidationRules,
        Config $eavConfig,
        \Magento\Customer\Model\AttributeFactory $attributeFactory,
        VendorAttrCollection $vendorAttrCollection
    ) {
        $this->_eavEntity = $eavEntity;
        $this->_objectManager = $objectManager;
        $this->_attributeCollection = $attributeCollection;
        $this->eavValidationRules = $eavValidationRules;
        $this->eavConfig = $eavConfig;
        $this->_attributeFactory = $attributeFactory;
        $this->vendorAttributeCollection = $vendorAttrCollection;
    }

    /**
     * Function afterGetMeta
     *
     * @param \Magento\Customer\Model\Customer\DataProviderWithDefaultAddresses $subject
     * @param array $result
     * @return void
     */
    public function afterGetMeta(\Magento\Customer\Model\Customer\DataProviderWithDefaultAddresses $subject, $result)
    {
        $customAttributes = $this->getAttributeCollection();

        try {
            if (!empty($customAttributes)) {
                foreach ($customAttributes as $attribute) {
                    if (in_array($attribute->getAttributeCode(), array_keys($result['customer']['children']))) {
                        unset($result['customer']['children'][$attribute->getAttributeCode()]);
                    }
                }
            }

            if (in_array("is_vendor_group", array_keys($result['customer']['children']))) {
                unset($result['customer']['children']["is_vendor_group"]);
            }
        } catch (\Exception $ex) {
            return $result;
        }

        return $result;
    }

    /**
     * Return custom attributes collection
     *
     * @param boolean $status
     * @return \Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAttribute\Collection
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
