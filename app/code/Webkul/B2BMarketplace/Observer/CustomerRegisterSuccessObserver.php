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

namespace Webkul\B2BMarketplace\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Webkul B2BMarketplace CustomerRegisterSuccessObserver Observer.
 */
class CustomerRegisterSuccessObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $helper;

    /**
     * @var \Webkul\B2BMarketplace\Helper\Data
     */
    protected $b2bHelper;

    /**
     * @var \Magento\UrlRewrite\Model\UrlRewrite
     */
    protected $urlRewrite;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface   $objectManager
     * @param \Webkul\Marketplace\Helper\Data             $helper
     * @param \Webkul\B2BMarketplace\Helper\Data          $b2bHelper
     * @param \Magento\UrlRewrite\Model\UrlRewrite        $urlRewrite
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Webkul\Marketplace\Helper\Data $helper,
        \Webkul\B2BMarketplace\Helper\Data $b2bHelper,
        \Magento\UrlRewrite\Model\UrlRewrite $urlRewrite,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_objectManager = $objectManager;
        $this->helper = $helper;
        $this->b2bHelper = $b2bHelper;
        $this->urlRewrite = $urlRewrite;
        $this->_messageManager = $messageManager;
    }

    /**
     * customer register event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $data = $observer['account_controller'];
        $helper = $this->helper;
        try {
            $paramData = $data->getRequest()->getParams();
            if (!empty($paramData['is_seller']) && !empty($paramData['profileurl']) && $paramData['is_seller'] == 1) {
                $customerId = $observer->getCustomer()->getId();
                $supplierModel = $helper->getSellerCollectionObj($customerId);
                if ($supplierModel->getSize()) {
                    $shopTitle = $data->getRequest()->getParam("shop_title");
                    foreach ($supplierModel as $value) {
                        $value->setToken($data->getRequest()->getParam("token"))
                            ->setDefaultAddressId($data->getRequest()->getParam("default_address_id"))
                            ->setShopTitle($shopTitle)
                            ->setContactNumber($paramData['telephone'])
                            ->setId($value->getId())
                            ->save();
                    }
                    $this->b2bHelper->saveCompanyInfo(['wk_supplier_company_name' => $shopTitle], $customerId);
                    $this->createSellerPublicUrls($paramData['profileurl']);
                }
            }
        } catch (\Exception $e) {
            // $this->_messageManager->addError($e->getMessage());
        }
    }

    public function createSellerPublicUrls($profileurl = '')
    {
        if ($profileurl) {
            $getAllStores = $this->helper->getAllStores();

            foreach ($getAllStores as $store) {
                $getCurrentStoreId = $store->getId();
                /*
                * Set Seller Profile Url
                */
                $sourceProfileUrl = 'marketplace/seller/profile/shop/'.$profileurl;
                $requestProfileUrl = $profileurl;
                /*
                * Check if already rexist in url rewrite model
                */
                $urlId = '';
                $profileRequestUrl = '';
                $urlCollectionData = $this->urlRewrite
                ->getCollection()
                ->addFieldToFilter('target_path', $sourceProfileUrl)
                ->addFieldToFilter('store_id', $getCurrentStoreId);
                foreach ($urlCollectionData as $value) {
                    $urlId = $value->getId();
                    $profileRequestUrl = $value->getRequestPath();
                }
                if ($profileRequestUrl != $requestProfileUrl) {
                    $idPath = rand(1, 100000);
                    $this->_objectManager->create(\Magento\UrlRewrite\Model\UrlRewrite::class)
                    ->load($urlId)
                    ->setStoreId($getCurrentStoreId)
                    ->setIsSystem(0)
                    ->setIdPath($idPath)
                    ->setTargetPath($sourceProfileUrl)
                    ->setRequestPath($requestProfileUrl)
                    ->save();
                }

                /*
                * Set Seller Collection Url
                */
                $sourceCollectionUrl = 'marketplace/seller/collection/shop/'.$profileurl;
                $requestCollectionUrl = $profileurl.'/collection';
                /*
                * Check if already rexist in url rewrite model
                */
                $urlId = '';
                $collectionRequestUrl = '';
                $urlCollectionData = $this->urlRewrite
                ->getCollection()
                ->addFieldToFilter('target_path', $sourceCollectionUrl)
                ->addFieldToFilter('store_id', $getCurrentStoreId);
                foreach ($urlCollectionData as $value) {
                    $urlId = $value->getId();
                    $collectionRequestUrl = $value->getRequestPath();
                }
                if ($collectionRequestUrl != $requestCollectionUrl) {
                    $idPath = rand(1, 100000);
                    $this->_objectManager->create(\Magento\UrlRewrite\Model\UrlRewrite::class)
                    ->load($urlId)
                    ->setStoreId($getCurrentStoreId)
                    ->setIsSystem(0)
                    ->setIdPath($idPath)
                    ->setTargetPath($sourceCollectionUrl)
                    ->setRequestPath($requestCollectionUrl)
                    ->save();
                }

                /*
                * Set Seller Feedback Url
                */
                $sourceFeedbackUrl = 'marketplace/seller/feedback/shop/'.$profileurl;
                $requestFeedbackUrl = $profileurl.'/feedback';
                /*
                * Check if already rexist in url rewrite model
                */
                $urlId = '';
                $feedbackRequestUrl = '';
                $urlFeedbackData = $this->urlRewrite
                ->getCollection()
                ->addFieldToFilter('target_path', $sourceFeedbackUrl)
                ->addFieldToFilter('store_id', $getCurrentStoreId);
                foreach ($urlFeedbackData as $value) {
                    $urlId = $value->getId();
                    $feedbackRequestUrl = $value->getRequestPath();
                }
                if ($feedbackRequestUrl != $requestFeedbackUrl) {
                    $idPath = rand(1, 100000);
                    $this->_objectManager->create(\Magento\UrlRewrite\Model\UrlRewrite::class)
                    ->load($urlId)
                    ->setStoreId($getCurrentStoreId)
                    ->setIsSystem(0)
                    ->setIdPath($idPath)
                    ->setTargetPath($sourceFeedbackUrl)
                    ->setRequestPath($requestFeedbackUrl)
                    ->save();
                }

                /*
                * Set Seller Location Url
                */
                $sourceLocationUrl = 'marketplace/seller/location/shop/'.$profileurl;
                $requestLocationUrl = $profileurl.'/location';
                /*
                * Check if already rexist in url rewrite model
                */
                $urlId = '';
                $locationRequestUrl = '';
                $urlLocationData = $this->urlRewrite->getCollection()
                ->addFieldToFilter('target_path', $sourceLocationUrl)
                ->addFieldToFilter('store_id', $getCurrentStoreId);
                foreach ($urlLocationData as $value) {
                    $urlId = $value->getId();
                    $locationRequestUrl = $value->getRequestPath();
                }
                if ($locationRequestUrl != $requestLocationUrl) {
                    $idPath = rand(1, 100000);
                    $this->_objectManager->create(\Magento\UrlRewrite\Model\UrlRewrite::class)
                    ->load($urlId)
                    ->setStoreId($getCurrentStoreId)
                    ->setIsSystem(0)
                    ->setIdPath($idPath)
                    ->setTargetPath($sourceLocationUrl)
                    ->setRequestPath($requestLocationUrl)
                    ->save();
                }

                /*
                * Set Seller quickorder Url
                */
                $sourceQuickOrderUrl = 'b2bmarketplace/supplier_profile/quickOrder/shop/'.$profileurl;
                $requestQuickOrderUrl = $profileurl.'/quickOrder';
                /*
                * Check if already rexist in url rewrite model
                */
                $urlId = '';
                $quickOrderRequestUrl = '';
                $urlQuickOrderData = $this->urlRewrite->getCollection()
                ->addFieldToFilter('target_path', $sourceQuickOrderUrl)
                ->addFieldToFilter('store_id', $getCurrentStoreId);
                foreach ($urlQuickOrderData as $value) {
                    $urlId = $value->getId();
                    $quickOrderRequestUrl = $value->getRequestPath();
                }
                if ($quickOrderRequestUrl != $requestQuickOrderUrl) {
                    $idPath = rand(1, 100000);
                    $this->_objectManager->create(\Magento\UrlRewrite\Model\UrlRewrite::class)
                    ->load($urlId)
                    ->setStoreId($getCurrentStoreId)
                    ->setIsSystem(0)
                    ->setIdPath($idPath)
                    ->setTargetPath($sourceQuickOrderUrl)
                    ->setRequestPath($requestQuickOrderUrl)
                    ->save();
                }

                /*
                * Set Seller requestQuote Url
                */
                $sourceRequestQuoteUrl = 'b2bmarketplace/supplier_profile/requestQuote/shop/'.$profileurl;
                $requestRequestQuoteUrl = $profileurl.'/requestQuote';
                /*
                * Check if already rexist in url rewrite model
                */
                $urlId = '';
                $requestQuoteRequestUrl = '';
                $urlRequestQuoteData = $this->urlRewrite->getCollection()
                ->addFieldToFilter('target_path', $sourceRequestQuoteUrl)
                ->addFieldToFilter('store_id', $getCurrentStoreId);
                foreach ($urlRequestQuoteData as $value) {
                    $urlId = $value->getId();
                    $requestQuoteRequestUrl = $value->getRequestPath();
                }
                if ($requestQuoteRequestUrl != $requestRequestQuoteUrl) {
                    $idPath = rand(1, 100000);
                    $this->_objectManager->create(\Magento\UrlRewrite\Model\UrlRewrite::class)
                    ->load($urlId)
                    ->setStoreId($getCurrentStoreId)
                    ->setIsSystem(0)
                    ->setIdPath($idPath)
                    ->setTargetPath($sourceRequestQuoteUrl)
                    ->setRequestPath($requestRequestQuoteUrl)
                    ->save();
                }
            }
        }
    }
}
