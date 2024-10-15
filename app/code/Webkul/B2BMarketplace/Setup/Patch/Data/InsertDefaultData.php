<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_B2BMarketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\B2BMarketplace\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Customer\Model\GroupFactory;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

class InsertDefaultData implements DataPatchInterface
{
    /**
     * EAV setup factory.
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var GroupFactory
     */
    private $groupFactory;

    /**
     * @var CollectionFactory
     */
    private $groupCollectionFactory;

    /**
     * Init.
     *
     * @param EavSetupFactory $eavSetupFactory
     * @param GroupFactory          $groupFactory
     * @param CollectionFactory     $groupCollectionFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        GroupFactory $groupFactory,
        CollectionFactory $groupCollectionFactory,
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->groupFactory = $groupFactory;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $this->createSupplierAttributes($this->moduleDataSetup);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'is_featured_product',
            [
                'type' => 'int',
                'group' => '',
                'backend' => '',
                'frontend' => '',
                'label' => 'Is Featured',
                'input' => 'boolean',
                'class' => '',
                'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'default' => 0,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false
            ]
        );
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'allowed_min_qty_to_order',
            [
                'type' => 'varchar',
                'group' => '',
                'backend' => '',
                'frontend' => '',
                'label' => 'Minimum Order Qty',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to'     => 'simple,configurable,bundle',
                'frontend_class'=>'validate-zero-or-greater',
                'note' => 'Not applicable on downloadable and virtual product.'
            ]
        );

        $groupCollection = $this->groupCollectionFactory->create()
            ->addFieldToFilter('customer_group_code', 'B2B Suppliers');
        if (!$groupCollection->getSize()) {
            $group = $this->groupFactory->create();
            $group->setCode('B2B Suppliers')
                  ->setTaxClassId(3)
                  ->save();
        }
    }

    public function createSupplierAttributes($setup)
    {
        $attributes = [];
        $attributes["wk_supplier_company_name"] = "Company Name";
        $attributes["wk_supplier_company_tagline"] = "Company Tagline";
        $attributes["wk_supplier_registered_in"] = "Company Registered In";
        $attributes["wk_supplier_team_size"] = "Company Team Size";
        $attributes["wk_supplier_certification"] = "Company Certification";
        $attributes["wk_supplier_role"] = "Supplier Role";
        $attributes["wk_is_verified_supplier"] = "Verified Supplier";
        $attributes["wk_is_premium_supplier"] = "Premium Supplier";

        $attributes["wk_supplier_response_time"] = "Response Time";
        $attributes["wk_supplier_banner_content"] = "Banner Content";
        $attributes["wk_supplier_response_time_unit"] = "Response Time Unit";

        foreach ($attributes as $attributeCode => $attributeLabel) {
            $this->createAttribute($attributeCode, $attributeLabel, $setup);
        }
    }

    public function createAttribute($attributeCode, $attributeLabel, $setup)
    {
        try {
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
            $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
            $attributeSetId = $customerEntity->getDefaultAttributeSetId();
            $attributeSet = $this->attributeSetFactory->create();
            $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
            $customerSetup->addAttribute(Customer::ENTITY, $attributeCode, [
                'type' => 'varchar',
                'label' => $attributeLabel,
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => true,
                'position' => 999,
                'system' => 0,
            ]);

            $attribute = $customerSetup->getEavConfig()
                                    ->getAttribute(Customer::ENTITY, $attributeCode)
                                    ->addData([
                                        'attribute_set_id' => $attributeSetId,
                                        'attribute_group_id' => $attributeGroupId,
                                        'used_in_forms' => ['adminhtml_customer'],
                                    ]);
            $attribute->save();
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }
    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

   /**
    * {@inheritdoc}
    */
    public function getAliases()
    {
        return [];
    }
}
