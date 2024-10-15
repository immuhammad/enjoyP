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
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\Message\ManagerInterface;
use Webkul\Mpmembership\Model\Config\Source\Feeapplied;

/**
 * Webkul Mpmembership MpDeleteProductObserver Observer.
 */
class MpDeleteProductObserver implements ObserverInterface
{
    /**
     * @var \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory
     */
    protected $collection;

    /**
     * @var \Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory
     */
    protected $salesListCollection;

    /**
     * @var \Webkul\Mpmembership\Model\ResourceModel\Transaction\CollectionFactory
     */
    protected $transactionCollection;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Webkul\Mpmembership\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @param \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory $collection
     * @param \Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory $salesListCollection
     * @param \Webkul\Mpmembership\Model\ResourceModel\Transaction\CollectionFactory $transactionCollection
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Webkul\Mpmembership\Helper\Data $helper
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory $collection,
        \Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory $salesListCollection,
        \Webkul\Mpmembership\Model\ResourceModel\Transaction\CollectionFactory $transactionCollection,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\Mpmembership\Helper\Data $helper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        ManagerInterface $messageManager
    ) {
        $this->collection = $collection;
        $this->salesListCollection = $salesListCollection;
        $this->transactionCollection = $transactionCollection;
        $this->mpHelper = $mpHelper;
        $this->date = $date;
        $this->helper = $helper;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * Product delete after event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            if ($this->helper->isModuleEnabled()) {
                $wholedata = $observer->getEvent()->getData();
                $wholedata = $wholedata[0];

                if (array_key_exists("product_mass_delete", $wholedata) && !empty($wholedata['product_mass_delete'])) {
                    $deleteProductIds = $wholedata['product_mass_delete'];
                } elseif (array_key_exists("id", $wholedata) && !empty($wholedata['id'])) {
                    $deleteProductIds = [$wholedata['id']];
                }

                $feeAppliedFor = $this->helper->getConfigFeeAppliedFor();
                if ($feeAppliedFor == Feeapplied::PER_VENDOR
                    && isset($deleteProductIds)
                    && count($deleteProductIds) > 0
                ) {
                    $productCollection = $this->collection->create()
                        ->addFieldToFilter(
                            'mageproduct_id',
                            ['in' => $deleteProductIds]
                        )->addFieldToFilter(
                            'status',
                            ['eq' => 2]
                        );

                    $salesList = $this->salesListCollection->create()
                        ->addFieldToFilter(
                            'mageproduct_id',
                            ['in' => $deleteProductIds]
                        )->addFieldToSelect('mageproduct_id');

                    $salesListProducts = array_unique($salesList->getColumnValues('mageproduct_id'));
                    $salesListProducts = array_diff($deleteProductIds, $salesListProducts);

                    $salesList = $this->salesListCollection->create()
                        ->addFieldToFilter(
                            'mageproduct_id',
                            ['in' => $salesListProducts]
                        );

                    $mageProducts = $this->productCollectionFactory->create()
                        ->addFieldToFilter(
                            'entity_id',
                            ['in' => $salesListProducts]
                        )->addFieldToFilter(
                            'status',
                            ['eq' => Status::STATUS_DISABLED]
                        )->getAllIds();
                    $mageProductIds = array_unique($mageProducts);
                    $totalNoOfProductsToAdd = count($mageProductIds);

                    if ($productCollection->getSize()
                        && $this->mpHelper->getIsProductApproval()
                        && $salesList->getSize()<=0
                        && $salesListProducts==$mageProductIds
                        && $totalNoOfProductsToAdd > 0
                    ) {
                        $transactionId = $this->updateRemainingProducts($totalNoOfProductsToAdd);
                        if ($transactionId && !empty($transactionId)) {
                            $this->messageManager->addSuccess(
                                __('you can add more product(s) for "%1" transaction(s)', implode(",", $transactionId))
                            );
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "MpDeleteProductObserver_execute Exception : ".$e->getMessage()
            );
        }
    }

    /**
     * [updateRemainingProducts used to set updated value of remaining products,
     * according to condition]
     *
     * @return void
     */
    private function updateRemainingProducts($counter)
    {
        $transactionId = [];
        try {
            $customerId = $this->helper->getSellerId();
            $timeAndProducts = \Webkul\Mpmembership\Model\Transaction::TIME_AND_PRODUCTS;
            $time = \Webkul\Mpmembership\Model\Transaction::TIME;
            $products = \Webkul\Mpmembership\Model\Transaction::PRODUCTS;

            $sellerTransactions = $this->transactionCollection->create()
                ->addFieldToFilter(
                    'seller_id',
                    ['eq' => $customerId]
                )->addFieldToFilter(
                    'transaction_id',
                    ['neq' => '']
                )->addFieldToFilter(
                    'check_type',
                    ['in' => [$timeAndProducts, $products]]
                )->addFieldToFilter(
                    'remaining_products',
                    ['gt' => 0]
                )->setOrder('transaction_date', 'ASC');

            if ($sellerTransactions->getSize()) {
                foreach ($sellerTransactions as $partner) {
                    if ((($partner->getCheckType() == $timeAndProducts
                        && $partner->getExpiryDate() > date('Y-m-d h:i:s'))
                        || $partner->getCheckType() == $products)
                        && $partner->getRemainingProducts() <= $partner->getNoOfProducts()
                        && $counter > 0
                    ) {
                        $temp = false;
                        if ($counter > $partner->getRemainingProducts()) {
                            $counter = $counter - $partner->getRemainingProducts();
                            $temp = true;
                            $partner->setRemainingProducts(0);
                        } elseif ($counter < $partner->getRemainingProducts()) {
                            $temp = true;
                            $partner->setRemainingProducts(
                                $partner->getRemainingProducts()-$counter
                            );
                            $counter = 0;
                        } elseif ($counter == $partner->getRemainingProducts()) {
                            $counter = 0;
                            $temp = true;
                            $partner->setRemainingProducts(0);
                        }

                        if ($temp) {
                            $transactionId[] = $partner->getTransactionId();
                            $partner->setUpdatedAt($this->date->gmtDate());
                            $partner->save();
                        }

                        if ($counter == 0) {
                            break;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "MpDeleteProductObserver_updateRemainingProducts Exception : ".$e->getMessage()
            );
        }
        return $transactionId;
    }
}
