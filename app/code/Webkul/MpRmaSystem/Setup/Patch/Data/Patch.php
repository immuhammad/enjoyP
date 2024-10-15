<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpRmaSystem
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpRmaSystem\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Webkul\Marketplace\Model\ControllersRepository;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @var ControllersRepository
     */
    private $controllersRepository;

    /**
     * Initialize Depenedencies
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param ControllersRepository $controllersRepository
     * @return void
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ControllersRepository $controllersRepository
    ) {
        $this->moduleDataSetup       = $moduleDataSetup;
        $this->controllersRepository = $controllersRepository;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $data = [];
        $connection = $this->moduleDataSetup->getConnection();
        if (!count($this->controllersRepository->getByPath('mprmasystem/seller/allrma'))) {
            $data[] = [
                'module_name' => 'Webkul_MpRmaSystem',
                'controller_path' => 'mprmasystem/seller/allrma',
                'label' => 'All Rma',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }

        $connection->insertMultiple($this->moduleDataSetup->getTable('marketplace_controller_list'), $data);
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * Function for getting Aliases
     *
     * @return array
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Function for getting Dependencies
     *
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }
}
