<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SellerSubDomain
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\SellerSubDomain\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InsertDefaultData implements DataPatchInterface
{
    /**
     * @var _urlRewrite
     */
    private $_urlRewrite;
    /**
     * @var storeManager
     */
    Private $storeManager;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewrite
    ) {
        $this->_storeManager = $storeManager;
        $this->_urlRewrite = $urlRewrite;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        try {
            $allStores = $this->_storeManager->getStores(true, false);
            foreach ($allStores as $store) {
                $sourceUrlList = [
                    'sellersubdomain/collection/index' => 'collection',
                    'sellersubdomain/location/index' => 'location',
                    'sellersubdomain/feedback/index' => 'feedback'
                ];
                foreach ($sourceUrlList as $key => $path) {
                    $urlId = 0;
                    $profileRequestUrl = '';
                    $urlCollectionData = $this->_urlRewrite
                        ->create()
                        ->getCollection()
                        ->addFieldToFilter('target_path', $key)
                        ->addFieldToFilter('store_id', $store->getId());
                    foreach ($urlCollectionData as $value) {
                        $urlId = $value->getId();
                        $profileRequestUrl = $value->getRequestPath();
                    }
                    if ($profileRequestUrl != $path) {
                        $idPath = rand(1, 100000);
                        $this->saveUrlRewriteData($urlId, $store->getId(), $idPath, $key, $path);
                    }
                }
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }
    public function saveUrlRewriteData($id, $storeId, $idPath, $key, $path)
    {
        $this->_urlRewrite->create()
            ->load($id)
            ->setStoreId($storeId)
            ->setIsSystem(0)
            ->setIdPath($idPath)
            ->setTargetPath($key)
            ->setRequestPath($path)
            ->save();
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
