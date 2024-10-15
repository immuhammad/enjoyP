<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpquotesystem
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpquotesystem\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetupFactory;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class CreateProductAttribute implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->_eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $eavSetup = $this->_eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'quote_status',
            [
                'label' => 'Quote Status',
                'type' => 'int',
                'input' => 'select',
                'group' => 'Product Details',
                'source' => \Webkul\Mpquotesystem\Model\Product\Attribute\Options ::class,
                'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'visible_on_front' => false,
                'is_configurable' => false,
                'searchable' => true,
                'default' => 0,
                'filterable' => true,
                'comparable' => true,
                'visible_in_advanced_search' => true,
                'note' => 'Quote enable on this product or not',
                'apply_to' => 'simple,downloadable,virtual,bundle,configurable',
            ]
        );
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'min_quote_qty',
            [
                'label' => 'Minimum Quote Quantity',
                'input' => 'text',
                'group' => 'Product Details',
                'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'backend' => \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend::class,
                'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'visible_on_front' => false,
                'is_configurable' => false,
                'searchable' => true,
                'default' => '',
                'filterable' => false,
                'comparable' => true,
                'sort_order' => 51,
                'visible_in_advanced_search' => true,
                'note' => 'Minimum Quote quantity for this product',
                'apply_to' => 'simple,downloadable,virtual,bundle,configurable',
            ]
        );
    }

    /**
     * Get aliases
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Get dependencies
     */
    public static function getDependencies()
    {
        return [

        ];
    }
}
