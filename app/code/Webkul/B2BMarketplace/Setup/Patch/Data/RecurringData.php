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

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Webkul\Marketplace\Model\ControllersRepository;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class RecurringData implements DataPatchInterface
{
    /**
     * @var ControllersRepository
     */
    private $controllersRepository;

    /**
     * @param ControllersRepository $controllersRepository
     * @param EavSetupFactory       $eavSetupFactory
     */
    public function __construct(
        ControllersRepository $controllersRepository,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->controllersRepository = $controllersRepository;
        $this->moduleDataSetup = $moduleDataSetup;
    }
    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $connection = $this->moduleDataSetup->getConnection();

        /**
         * insert sellerstorepickup controller's data
         */
        $data = [];

        if (!$this->controllersRepository->getByPath('b2bmarketplace/supplier/quotes')->getSize()) {
            $data[] = [
                'module_name' => 'Webkul_B2BMarketplace',
                'controller_path' => 'b2bmarketplace/supplier/quotes',
                'label' => 'RFQ List',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }

        if (!$this->controllersRepository->getByPath('b2bmarketplace/supplier/quote')->getSize()) {
            $data[] = [
                'module_name' => 'Webkul_B2BMarketplace',
                'controller_path' => 'b2bmarketplace/supplier/quote',
                'label' => 'View RFQ',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }

        if (!$this->controllersRepository->getByPath('b2bmarketplace/supplier/quote_request')->getSize()) {
            $data[] = [
                'module_name' => 'Webkul_B2BMarketplace',
                'controller_path' => 'b2bmarketplace/supplier/quote_request',
                'label' => 'Send Quote Request',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }

        if (!$this->controllersRepository->getByPath('b2bmarketplace/supplier/message')->getSize()) {
            $data[] = [
                'module_name' => 'Webkul_B2BMarketplace',
                'controller_path' => 'b2bmarketplace/supplier/message',
                'label' => 'Manage Customer\'s Message',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }

        if (!$this->controllersRepository->getByPath('b2bmarketplace/supplier/buyingleads')->getSize()) {
            $data[] = [
                'module_name' => 'Webkul_B2BMarketplace',
                'controller_path' => 'b2bmarketplace/supplier/buyingleads',
                'label' => 'Manage Buying Leads',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }

        if (!$this->controllersRepository->getByPath('b2bmarketplace/supplier/verification')->getSize()) {
            $data[] = [
                'module_name' => 'Webkul_B2BMarketplace',
                'controller_path' => 'b2bmarketplace/supplier/verification',
                'label' => 'Verification',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }

        $connection->insertMultiple($this->moduleDataSetup->getTable('marketplace_controller_list'), $data);
        $this->moduleDataSetup->getConnection()->endSetup();
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
