<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package  Webkul_Mpperproductshipping
 * @author   Webkul
 * @license  https://store.webkul.com/license.html
 */

namespace Webkul\Mpperproductshipping\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class MpShippingChargesAttribute implements
    DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'mp_shipping_charge',
            [
              'type' => 'varchar',
              'backend' => '',
              'frontend' => '',
              'label' => __('Shipping Charges'),
              'input' => 'text',
              'class' => '',
              'source' => '',
              'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
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
              'frontend_class'=>'validate-number validate-zero-or-greater',
              'note' => __('Not applicable on downloadable and virtual product.')
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }
}
