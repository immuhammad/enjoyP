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
use Magento\Framework\Setup\ModuleContextInterface;
use Webkul\Marketplace\Model\ControllersRepository;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Setup\EavSetup;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class ControllersDataSave implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param ControllersRepository $controllersRepository
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ControllersRepository $controllersRepository,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->controllersRepository = $controllersRepository;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        // setup default
        $this->moduleDataSetup->getConnection()->startSetup();
        $connection = $this->moduleDataSetup->getConnection();

        $data = [];

        if (!count($this->controllersRepository->getByPath('mpquotesystem/sellerquote/managequote'))) {
            $data[] = [
                'module_name' => 'Webkul_Mpquotesystem',
                'controller_path' => 'mpquotesystem/sellerquote/managequote',
                'label' => 'Manage Quotes',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }
        if (!count($this->controllersRepository->getByPath('mpquotesystem/sellerquote/index'))) {
            $data[] = [
                'module_name' => 'Webkul_Mpquotesystem',
                'controller_path' => 'mpquotesystem/sellerquote/index',
                'label' => 'Quotes Setting',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }

        if (count($data)) {
            $connection->insertMultiple($this->moduleDataSetup->getTable('marketplace_controller_list'), $data);
        }
        $this->moduleDataSetup->endSetup();
    }

    /**
     * Get alisases
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
