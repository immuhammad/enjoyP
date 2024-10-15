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

namespace Webkul\SellerSubDomain\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Webkul\SellerSubDomain\Model\DomainFactory;

/**
 * Webkul Marketplace AdminhtmlCustomerSaveAfterObserver Observer.
 */
class AdminhtmlCustomerSaveAfterObserver implements ObserverInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * Store manager.
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $_messageManager;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    protected $_jsonDecoder;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * @var \Magento\Customer\Model\Customer\Mapper
     */
    protected $_customerMapper;

    /**
     * @var CustomerInterfaceFactory
     */
    protected $_customerDataFactory;

    /**
     * @var \Webkul\SellerSubDomain\Helper\Data
     */
    protected $_helper;

    /**
     * @param \Magento\Framework\ObjectManagerInterface        $objectManager,
     * @param \Magento\Framework\Message\ManagerInterface      $messageManager,
     * @param \Magento\Store\Model\StoreManagerInterface       $storeManager,
     * @param CollectionFactory                                $collectionFactory,
     * @param ProductCollection                                $sellerProduct
     * @param \Magento\Framework\Json\DecoderInterface         $jsonDecoder
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CollectionFactory $collectionFactory,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        \Magento\Customer\Model\Customer\Mapper $customerMapper,
        CustomerRepositoryInterface $customerRepository,
        CustomerInterfaceFactory $customerDataFactory,
        DataObjectHelper $dataObjectHelper,
        DomainFactory $domainFactory,
        \Webkul\SellerSubDomain\Helper\Data $helper
    ) {
        $this->_helper = $helper;
        $this->_objectManager = $objectManager;
        $this->_messageManager = $messageManager;
        $this->_collectionFactory = $collectionFactory;
        $this->_storeManager = $storeManager;
        $this->_jsonDecoder = $jsonDecoder;
        $this->_customerRepository = $customerRepository;
        $this->_customerMapper = $customerMapper;
        $this->_customerDataFactory = $customerDataFactory;
        $this->_dataObjectHelper = $dataObjectHelper;
        $this->_domainFactory = $domainFactory;
    }

    /**
     * admin customer save after event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customerid = $observer->getCustomer()->getId();
        $postData = $observer->getRequest()->getPostValue();
        $helper = $this->_helper;
        if (isset($postData['vendor_domain']) && $this->isSeller($customerid) &&
        $helper->isModuleEnable() && $helper->isLocalDomainSettingEnable()) {
            try {
                $error = 0;
                if ($postData['vendor_domain'] && !filter_var($postData['vendor_domain'], FILTER_VALIDATE_URL)) {
                    $this->_messageManager->addError("The domain ".$postData['vendor_domain']." is not valid");
                    // $this->_messageManager->addError('%1 is not an valid email', $postData['vendor_domain']);
                    return $this;
                } elseif ($postData['vendor_domain']) {
                    $url = explode('/', trim($postData['vendor_domain']));
                    $postData['vendor_domain'] = $url[0].'//'.$url[2].'/';
                }
                $collection = $this->_domainFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('seller_id', $customerid)
                    ->addFieldToFilter('vendor_store_id', $postData['store_switcher'])
                    ->addFieldToFilter('vendor_website_id', $postData['website_switcher']);
                if ($postData['vendor_domain']) {
                    $savedUrl = '';
                    $urlExists = $this->_domainFactory->create()->getCollection()
                        ->addFieldToFilter('vendor_url', $postData['vendor_domain'])
                        ->addFieldToFilter('seller_id', ['nin'=>[$customerid]])
                        ->getSize();
                    if ($collection->getSize()) {
                        $savedUrl = $savedVendorUrl = $collection
                            ->setPageSize(1)
                            ->getFirstItem()
                            ->getVendorUrl();
                    }
                    if ($urlExists && $savedUrl != $postData['vendor_domain']) {
                        $error = 1;
                    }
                }
                if (!$error) {
                    if ($postData['store_switcher'] < 1) {
                        $this->saveForAllViews($postData, $customerid);
                    } else {
                        $domainData['seller_id'] = $customerid;
                        $domainData['vendor_store_id'] = $postData['store_switcher'];
                        $domainData['vendor_website_id'] = $postData['website_switcher'];
                        $domainData['vendor_url'] = $postData['vendor_domain'];
                        $domainData['created_at'] = date('Y-m-d H:i:s');
                        $domainData['updated_at'] = date('Y-m-d H:i:s');
                        if ($postData['vendor_domain'] != $helper->getAdminBaseUrl() &&
                            $postData['vendor_domain'] != $helper->getAdminBaseUrl()."/") {
                            $this->saveDomainData($collection, $domainData);
                        } else {
                            $this->_messageManager->addError(
                                __('Please enter domain other than the base URL')
                            );
                        }
                    }
                } else {
                    $this->_messageManager->addError(
                        __('%1 Vendor domain already assigned.', $postData['vendor_domain'])
                    );
                }
            } catch (\Exception $e) {
                $this->_messageManager->addError($e->getMessage());
            }
        }
        return $this;
    }

    private function saveForAllViews($postData, $customerId)
    {
        $groups = $this->_storeManager->getWebsite()->getGroups();
        $storeViewName = [];
        foreach ($groups as $key => $group) {
            $storeName[] = $group->getName();
            $stores = $group->getStores();
            foreach ($stores as $store) {
                $storeViewIds[] = $store->getStoreId();
            }
        }

        foreach ($storeViewIds as $storeId) {
            if ($postData['vendor_domain'] != $this->_helper->getAdminBaseUrl() &&
                $postData['vendor_domain'] != $this->_helper->getAdminBaseUrl()."/") {
                    $sellerData = $this->_domainFactory->create()->getCollection()
                    ->addFieldToFilter("seller_id", ["eq"=>$customerId])
                    ->addFieldToFilter("vendor_store_id", ["eq"=>$storeId]);
                if ($sellerData->getSize()>0) {
                    $entityId = $sellerData->getFirstItem()->getEntityId();
                    $this->_domainFactory
                        ->create()
                        ->load($entityId)
                        ->setSellerId($customerId)
                        ->setVendorStoreId($storeId)
                        ->setVendorWebsiteId($postData['website_switcher'])
                        ->setVendorUrl($postData['vendor_domain'])
                        ->setCreatedAt(date('Y-m-d H:i:s'))
                        ->setUpdatesAt(date('Y-m-d H:i:s'))
                        ->save();
                } else {
                    $domainData['seller_id'] = $customerId;
                    $domainData['vendor_store_id'] = $storeId;
                    $domainData['vendor_website_id'] = $postData['website_switcher'];
                    $domainData['vendor_url'] = $postData['vendor_domain'];
                    $domainData['created_at'] = date('Y-m-d H:i:s');
                    $domainData['updated_at'] = date('Y-m-d H:i:s');
                    $this->_domainFactory
                    ->create()->setData($domainData)->save();
                }
            } else {
                $this->_messageManager->addError(
                    __('Please enter domain other than the base URL')
                );
            }
        }
    }

    private function saveDomainData($collection, $domainData)
    {
        if ($collection->getSize()) {
            foreach ($collection as $vendorDomain) {
                $vendorDomain->setVendorUrl($domainData['vendor_url'])
                    ->setUpdatedAt($domainData['updated_at'])
                    ->setEntityId($vendorDomain->getEntityId());
                $this->saveObject($vendorDomain);
            }
        } else {
            $this->_domainFactory->create()
            ->setData($domainData)
            ->save();
        }
    }

    private function saveObject($object)
    {
        $object->save();
    }

    private function isSeller($customerid)
    {
        $sellerStatus = 0;
        $model = $this->_collectionFactory->create()
        ->addFieldToFilter('seller_id', $customerid);
        foreach ($model as $value) {
            $sellerStatus = $value->getIsSeller();
        }

        return $sellerStatus;
    }
    private function getAllSellerIds()
    {
        return $this->_collectionFactory->create()
        ->addFieldToFilter('is_seller', 1)
        ->getColumnValues('entity_id');
    }
}
