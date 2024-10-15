<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManagerInterface as CoreSession;
use Webkul\Affiliate\Model\UserFactory;
use Webkul\Affiliate\Model\SaleFactory;
use Webkul\Affiliate\Helper\Email as HelperEmail;
use Webkul\Affiliate\Helper\Data as Data;
use Webkul\Affiliate\Logger\Logger;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;

/**
 * Webkul Affiliate CheckoutSubmitAllAfter Observer Model.
 */
class CheckoutSubmitAllAfter implements ObserverInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $coreSession;

    /**
     * @var \Webkul\Affiliate\Model\SaleFactory
     */
    private $saleFactory;

    /**
     * @var \Webkul\Affiliate\Model\UserFactory
     */
    private $userFactory;

    /**
     * @var \Webkul\Affiliate\Helper\Email
     */
    private $helperEmail;

    /**
     * @var \Webkul\Affiliate\Helper\Data
     */
    private $helper;

    /**
     * Checkout Session
     *
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var \Webkul\Affiliate\Logger\Logger
     */
    private $logger;

    /**
     * Product Repository
     *
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $productRepo;

    /**
     * Category Factory
     *
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var DateTimeFactory
     */
    private $_dateFactory;

    /**
     * @param CustomerSession   $customerSession
     * @param CheckoutSession   $checkoutSession
     * @param UserFactory       $userFactory
     * @param SaleFactory       $saleFactory
     * @param HelperEmail       $helperEmail
     * @param Data              $helper
     * @param ProductRepository $productRepo
     * @param CategoryFactory   $categoryFactory
     * @param DateTimeFactory   $dateFactory
     * @param Logger            $logger
     */
    public function __construct(
        CoreSession $coreSession,
        CheckoutSession $checkoutSession,
        UserFactory $userFactory,
        SaleFactory $saleFactory,
        HelperEmail $helperEmail,
        Data $helper,
        \Magento\Catalog\Model\ProductRepository $productRepo,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        DateTimeFactory $dateFactory,
        Logger $logger
    ) {
        $this->coreSession = $coreSession;
        $this->checkoutSession = $checkoutSession;
        $this->userFactory = $userFactory;
        $this->saleFactory = $saleFactory;
        $this->helper = $helper;
        $this->helperEmail = $helperEmail;
        $this->productRepo = $productRepo;
        $this->categoryFactory = $categoryFactory;
        $this->_dateFactory = $dateFactory;
        $this->logger = $logger;
    }

    /**
     * Sales Order Place After event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            /** @var $orderInstance Order */
            $totalAffIds = [];
            $totalAffIds2 = $this->checkoutSession->getData('aff_ids');
            $totalAffIds = $this->coreSession->getData('aff_ids');
            if($totalAffIds2) {
                $totalAffIds = array_merge($totalAffIds, $totalAffIds2);
            }
            $this->logger->info('coreSession affDetail : '.json_encode($totalAffIds2));
            $this->logger->info('checkoutSession affDetail : '.json_encode($totalAffIds));
            $order = $observer->getEvent()->getOrder();
            if ($order) {
                $incrementId = (int)$order->getIncrementId();
                $lastOrderId = $incrementId;
                $orderProducts = [];
                foreach ($order->getAllVisibleItems() as $item) {
                    $orderProducts[$item->getProductId()][] = $item;
                }
                if ($totalAffIds) {
                    foreach ($totalAffIds as $affDetail) {
                        $this->logger->info('affDetail : '.json_encode($affDetail));
                        if (($affDetail["hit_type"]=="product" || $affDetail["hit_type"]=="textbanner")
                                && isset($orderProducts[$affDetail["hit_id"]])) {
                            $productItems = $orderProducts[$affDetail["hit_id"]];
                            foreach ($productItems as $productItem) {
                                $this->logger->info('productItem : '.json_encode($productItem));
                                $this->logger->info('affDetail : '.implode(',', $affDetail));
                                $this->getOrderedProduct($productItem, $order, $affDetail);
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->info('affiliate order place : '.$e->getMessage());
        }
    }

    /**
     * get detail of products in order
     *
     * @param $productItem
     * @param $order
     * @param $affDetail
     * @return void
     */
    public function getOrderedProduct($productItem, $order, $affDetail)
    {
        $totalPrice = $productItem->getBaseRowTotal()-$productItem->getDiscountAmount();
        $orderedQty = $productItem->getQtyOrdered();
        $this->logger->info('getAffiliateConfig : '.$this->helper->getAffiliateConfig()["priority"]);
        if ($this->helper->getAffiliateConfig()["priority"]=="category") {
            $catIds = $this->productRepo
                        ->getById($productItem->getProductId())->getCategoryIds();
            $this->logger->info('catIds : '.implode(',', $catIds));
            $catCollection = $this->categoryFactory
                                            ->create()->getCollection()
                                            ->addFieldToFilter("entity_id", ["in"=>$catIds])
                                            ->addAttributeToSelect("*");
            $commissionArr = [];
            foreach ($catCollection as $category) {
                $catAffCommission = $category->getAffiliateCommission();
                if ($catAffCommission != "") {
                    $catCommission = $catAffCommission;
                    if ($category->getAffiliateCommissionType()=="percent") {
                        $catCommission = $catCommission * $totalPrice / 100;
                    } else {
                        $catCommission = $catCommission * $orderedQty;
                    }
                    $commissionArr[] = $catCommission;
                }
            }
            $this->logger->info('commissionArr : '.implode(',', $commissionArr));
            if (!empty($commissionArr)) {
                $this->saveDetail($order, $affDetail, $totalPrice, $orderedQty, max($commissionArr));
            } else {
                $this->saveDetail($order, $affDetail, $totalPrice, $orderedQty);
            }
        } else {
            $this->saveDetail($order, $affDetail, $totalPrice, $orderedQty);
        }
        $this->coreSession->unsAffIds();
    }

    /**
     * save sales detail and notify seller via mail
     *
     * @param $order
     * @param $affDetail
     * @param $totalPrice
     * @param $orderedQty
     * @param $affCommission
     * @return void
     */
    public function saveDetail($order, $affDetail, $totalPrice, $orderedQty, $affCommission = null)
    {
        $userColl = $this->userFactory->create()
                        ->getCollection()
                        ->addFieldToFilter('customer_id', $affDetail['aff_customer_id']);
        
        $this->logger->info('userColl : '.$userColl->getSize());
        if ($userColl->getSize()) {
            foreach ($userColl as $affUser) {
                if ($affCommission==null) {
                    $affCommission = $affUser->getCommission();
                    if ($affUser->getCommissionType() == 'percent') {
                        $affCommission=($totalPrice * $affCommission)/100;
                    } else {
                        $affCommission = $affCommission * $orderedQty;
                    }
                }
                $createdAt = $order->getCreatedAt();
                if (empty($createdAt)) {
                    $createdAt = $this->_dateFactory->create()->gmtDate();
                }
                $data = [
                    'order_id'           =>  $order->getId(),
                    'order_increment_id' =>  $order->getIncrementId(),
                    'aff_customer_id'    =>  $affDetail['aff_customer_id'],
                    'order_status'       =>  $order->getStatus(),
                    'affilate_status'    =>  0,
                    'price'              =>  $totalPrice,
                    'commission'         =>  $affCommission,
                    'come_from'          =>  $affDetail['come_from'],
                    'created_at'         =>  $createdAt
                ];
                $affTmpSale = $this->saleFactory->create();
                $affTmpSale->setData($data);
                $affTmpSale->save();
            }
        }

        /*send placed order mail notification to seller*/
        $this->helperEmail->sendMailToAffiliateAdmin(
            $data['aff_customer_id'],
            $order->getIncrementId()
        );
        $this->coreSession->unsAffIds();
    }
}
