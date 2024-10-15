<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpmembership
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpmembership\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\Mpmembership\Model\ResourceModel\Transaction\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory as SellerProductCollectionFactory;
use Magento\Catalog\Model\Product\Visibility;
use Webkul\Mpmembership\Model\Config\Source\Feeapplied;
use Webkul\Mpmembership\Model\Transaction;
use Webkul\Mpmembership\Model\Product as ProductTransaction;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Webkul Mpmembership Observer MpSaveProductAfterObserver
 */
class MpSaveProductAfterObserver implements ObserverInterface
{
    /**
     * @var \Webkul\Mpmembership\Helper\Data
     */
    protected $helper;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $requestInterface;

    /**
     * @var \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory
     */
    protected $mpProductCollectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;

    /**
     * @param \Webkul\Mpmembership\Helper\Data $helper
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Framework\App\RequestInterface $requestInterface
     * @param SellerProductCollectionFactory $mpProductCollectionFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Magento\Catalog\Model\ResourceModel\Product\Action $productAction
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Webkul\Mpmembership\Helper\Data $helper,
        CollectionFactory $collectionFactory,
        \Magento\Framework\App\RequestInterface $requestInterface,
        SellerProductCollectionFactory $mpProductCollectionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Catalog\Model\ResourceModel\Product\Action $productAction,
        ProductRepositoryInterface $productRepository
    ) {
        $this->helper = $helper;
        $this->collectionFactory = $collectionFactory;
        $this->requestInterface = $requestInterface;
        $this->mpProductCollectionFactory = $mpProductCollectionFactory;
        $this->date = $date;
        $this->mpHelper = $mpHelper;
        $this->productAction = $productAction;
        $this->productRepository = $productRepository;
    }

    /**
     * Marketplace Product save after observer
     * that checks the remaining products and then update the value.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $wholeData = $observer->getEvent()->getData();
            $params = $this->requestInterface->getParams();
            $feeAppliedFor = $this->helper->getConfigFeeAppliedFor();

            if (isset($wholeData[0])) {
                $data = $wholeData[0];
            }

            if ($this->helper->isModuleEnabled() && !empty($data['id'])) {
                if (empty($params['id'])) {
                    if ($feeAppliedFor == Feeapplied::PER_VENDOR) {
                        $count = $this->getAddedProductCount($data['id']);
                        $flag = $this->checkValidation($count);
                        if ($flag) {
                            $this->setMpProductPaymentStatus($data['id']);
                        }
                    } elseif ($feeAppliedFor == Feeapplied::PER_PRODUCT
                        || !$this->mpHelper->getIsProductApproval()
                    ) {
                        $this->disableProduct($data['id']);
                    }
                } elseif (!empty($params['id'])) {
                    if ($feeAppliedFor == Feeapplied::PER_PRODUCT) {
                        $this->updateProduct($data['id']);
                    } elseif ($feeAppliedFor == Feeapplied::PER_VENDOR
                        && !empty($data['back'])
                        && $data['back'] == 'duplicate'
                        && !empty($data['product_id'])
                        && $data['product_id'] !== $data['id']
                        && $data['product_id'] == $params['id']
                    ) {
                        $count = $this->getAddedProductCount($data['id']);
                        $flag = $this->checkValidation($count);
                        if ($flag) {
                            $this->setMpProductPaymentStatus($data['id']);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "MpSaveProductAfterObserver_execute Exception : ".$e->getMessage()
            );
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * disableProduct disable product status
     *
     * @param int $ids [contains product id]
     * @return void
     */
    private function disableProduct($id)
    {
        try {
            $collection = $this->mpProductCollectionFactory->create()
                ->addFieldToFilter(
                    'mageproduct_id',
                    ['eq' => $id]
                );

            if ($collection->getSize() > 0) {
                foreach ($collection as $product) {
                    $product->setStatus(2);
                    $product->save();

                    $this->setProductStatus($product->getMageproductId());
                }
            } else {
                $this->setProductStatus($id);
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "MpSaveProductAfterObserver_disableProduct Exception : ".$e->getMessage()
            );
        }
    }

    /**
     * setProductStatus sets catalog product status
     *
     * @param int $id [contains product id]
     */
    private function setProductStatus($id)
    {
        try {
            $updateAttributes['status'] = Status::STATUS_DISABLED;
            $this->productAction->updateAttributes(
                [$id],
                $updateAttributes,
                $this->mpHelper->getCurrentStoreId()
            );
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "MpSaveProductAfterObserver_setProductStatus Exception : ".$e->getMessage()
            );
        }
    }

    /**
     * checkValidation used to set updated value of remaining products,
     * according to condition]
     *
     * @param int $count
     * @return void
     */
    private function checkValidation($count)
    {
        $flag = false;
        try {
            $customerId = $this->helper->getSellerId();

            $transactionModel = $this->collectionFactory->create();
            $sellerTransactions = $transactionModel
                ->addFieldToFilter(
                    'seller_id',
                    ['eq' => $customerId]
                )->addFieldToFilter(
                    'transaction_id',
                    ['neq' => '']
                )->setOrder('transaction_date', 'ASC');

            $timeAndProducts = Transaction::TIME_AND_PRODUCTS;
            $time = Transaction::TIME;
            $products = Transaction::PRODUCTS;

            if ($sellerTransactions->getSize()) {
                foreach ($sellerTransactions as $partner) {
                    if ($partner->getCheckType() == $timeAndProducts) {
                        $remainingProductCount = $partner->getRemainingProducts() + $count;
                        if ($partner->getExpiryDate() > date('Y-m-d h:i:s')
                            && $remainingProductCount <= $partner->getNoOfProducts()
                        ) {
                            $partner->setRemainingProducts(
                                $remainingProductCount
                            );
                            $partner->setUpdatedAt($this->date->gmtDate());
                            $partner->save();
                            $this->updateTransactionType($partner);
                            $flag = true;
                            break;
                        }
                    } elseif ($partner->getCheckType() == $products) {
                        $remainingProductCount = $partner->getRemainingProducts() + $count;
                        if ($remainingProductCount <= $partner->getNoOfProducts()) {
                            $partner->setRemainingProducts(
                                $remainingProductCount
                            );
                            $partner->setUpdatedAt($this->date->gmtDate());
                            $partner->save();
                            $this->updateTransactionType($partner);
                            $flag = true;
                            break;
                        }
                    } elseif ($partner->getCheckType() == $time) {
                        if ($partner->getExpiryDate() > date('Y-m-d h:i:s')) {
                            $partner->setRemainingProducts(
                                $partner->getRemainingProducts() + $count
                            );
                            $partner->setUpdatedAt($this->date->gmtDate());
                            $partner->save();
                            $flag = true;
                            break;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "MpSaveProductAfterObserver_checkValidation Exception : ".$e->getMessage()
            );
        }
        return $flag;
    }

    /**
     * updateTransactionType
     *
     * @param \Webkul\Mpmembership\Model\Transaction $partner
     * @return void
     */
    private function updateTransactionType($partner)
    {
        if ($partner->getRemainingProducts() == $partner->getNoOfProducts()) {
            $this->helper->updateTransactionType(
                $partner->getId(),
                Transaction::PRODUCT_LIMIT_COMPLETED
            );
        }
    }

    /**
     * setVisibility
     *
     * @param int $ids [contains product id]
     * @return void
     */
    private function setVisibility($id)
    {
        try {
            $updateAttributes['visibility'] = Visibility::VISIBILITY_NOT_VISIBLE;
            $this->productAction->updateAttributes(
                [$id],
                $updateAttributes,
                $this->mpHelper->getCurrentStoreId()
            );
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "MpSaveProductAfterObserver_setVisibility Exception : ".$e->getMessage()
            );
        }
    }

    /**
     * updateProduct
     *
     * @param int $productId
     * @return void
     */
    private function updateProduct($productId)
    {
        $validProductIds = $this->helper->getValidProductIds(
            $this->helper->getSellerId()
        );
        $parentIds = $this->helper->getParentIdsByChildId($productId);
        if (!in_array($productId, $validProductIds) && empty($parentIds)) {
            $this->disableProduct($productId);
        } elseif (!empty($parentIds)) {
            $this->setVisibility($productId);
        }
    }

    /**
     * setMpProductPaymentStatus
     *
     * @param int $id
     * @return void
     */
    private function setMpProductPaymentStatus($id)
    {
        try {
            $collection = $this->mpProductCollectionFactory->create()
                ->addFieldToFilter(
                    'mageproduct_id',
                    ['eq' => $id]
                );

            if ($collection->getSize() > 0) {
                foreach ($collection as $product) {
                    $product->setPaymentStatus(ProductTransaction::FEE_PAID);
                    $product->save();

                    $this->checkChildProducts($product->getMageproductId());
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "MpSaveProductAfterObserver_setMpProductPaymentStatus Exception : ".$e->getMessage()
            );
        }
    }

    /**
     * checkChildProducts
     *
     * @param int $id [product id]
     * @return void
     */
    private function checkChildProducts($id)
    {
        try {
            $usedProductIds = [];
            $product = $this->productRepository->getById($id);
            if ($product->getTypeId()=="configurable") {
                $productTypeInstance = $product->getTypeInstance();
                $usedProductIds = $productTypeInstance->getUsedProductIds($product);
            }

            if (!empty($usedProductIds)) {
                foreach ($usedProductIds as $productId) {
                    $this->setMpProductPaymentStatus($productId);
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "MpSaveProductAfterObserver_checkChildProducts Exception : ".$e->getMessage()
            );
        }
    }

    /**
     * getAddedProductCount
     *
     * @param int $productId
     * @return int
     */
    private function getAddedProductCount($productId)
    {
        $count = 1;
        try {
            $usedProductIds = [];
            $product = $this->productRepository->getById($productId);
            if ($product->getTypeId() == "configurable") {
                $productTypeInstance = $product->getTypeInstance();
                $usedProductIds = $productTypeInstance->getUsedProductIds($product);
            }
            if (!empty($usedProductIds)) {
                $count += count($usedProductIds);
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "MpSaveProductAfterObserver_getAddedProductCount Exception : ".$e->getMessage()
            );
        }
        return $count;
    }
}
