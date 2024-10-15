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

namespace Webkul\SellerSubDomain\Plugin\Backend\Store;

use \Magento\Framework\App\Helper\Context;

class Save
{
    /**
     * @var \Webkul\SellerSubDomain\Helper\Data
     */
    protected $_helper;

    /**
     * @param Context                             $context
     * @param \Webkul\SellerSubDomain\Helper\Data $data
     */
    public function __construct(
        Context $context,
        \Webkul\SellerSubDomain\Helper\Data $data,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewrite
    ) {
        $this->_helper = $data;
        $this->_storeManager = $storeManager;
        $this->_urlRewrite = $urlRewrite;
    }

    /**
     * @param \Magento\Theme\Block\Html\Header\Logo $subject
     * @param $result
     * @return string
     */
    public function afterExecute(\Magento\Backend\Controller\Adminhtml\System\Store\Save $subject, $result)
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
            return $result;
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
}
