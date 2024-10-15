<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpServiceFee
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpServiceFee\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManager;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Api\OrderRepositoryInterface;
use Webkul\Marketplace\Helper\Data as MarketplaceHelper;

/**
 * Webkul Marketplace SalesOrderPlaceAfterObserver Observer Model.
 */
class MarketplaceSalesOrderPlaceAfterObserver implements ObserverInterface
{

    /**
     * @var Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * [$_coreSession description].
     *
     * @var SessionManager
     */
    protected $_coreSession;

    /**
     * @var QuoteRepository
     */
    protected $_quoteRepository;

    /**
     * @var OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var MarketplaceHelper
     */
    protected $_marketplaceHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * Class constructor
     *
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param SessionManager $coreSession
     * @param QuoteRepository $quoteRepository
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param MarketplaceHelper $marketplaceHelper
     * @param \Webkul\Marketplace\Model\SaleslistFactory $salesList
     * @param \Webkul\MpServiceFee\Logger\Logger $logger
     * @param \Webkul\Marketplace\Model\OrdersFactory $orderFactory
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        SessionManager $coreSession,
        QuoteRepository $quoteRepository,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        OrderRepositoryInterface $orderRepository,
        MarketplaceHelper $marketplaceHelper,
        \Webkul\Marketplace\Model\SaleslistFactory $salesList,
        \Webkul\MpServiceFee\Logger\Logger $logger,
        \Webkul\Marketplace\Model\OrdersFactory $orderFactory
    ) {;
        $this->_checkoutSession = $checkoutSession;
        $this->_coreSession = $coreSession;
        $this->_quoteRepository = $quoteRepository;
        $this->_orderRepository = $orderRepository;
        $this->quoteFactory = $quoteFactory;
        $this->salesList = $salesList;
        $this->_marketplaceHelper = $marketplaceHelper;
        $this->orderFactory = $orderFactory;
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
            $quoteId = $this->_checkoutSession->getLastQuoteId();
            $quote = $this->_quoteRepository->get($quoteId);
            $isMultiShipping = $this->_checkoutSession->getQuote()->getIsMultiShipping();
            if (!$isMultiShipping) {
                $this->operationOnSingleShiping($observer);
            } else {
                if ($quote->getIsMultiShipping() == 1 || $isMultiShipping == 1) {
                    $this->operationOnMultiShiping();
                }
            }
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * Order place operation on single shipping
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function operationOnSingleShiping(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();
        $lastOrderId = $observer->getOrder()->getId();
        $this->orderPlacedOperations($order, $lastOrderId);
    }

    /**
     * Order place operation on multisshipping
     *
     * @return void
     */
    public function operationOnMultiShiping()
    {
        $orderIds = $this->_coreSession->getOrderIds();
        foreach ($orderIds as $ids => $orderIncId) {
            $lastOrderId = $ids;
            /** @var $orderInstance Order */
            $order = $this->_orderRepository->get($lastOrderId);
            $this->orderPlacedOperations($order, $lastOrderId);
        }
    }

    /**
     * Order Place Operation method.
     *
     * @param \Magento\Sales\Model\Order $order
     * @param int                        $lastOrderId
     */
    public function orderPlacedOperations($order, $lastOrderId)
    {
        $sellerProArr = [];
        $sellerServiceFeesTotal = [];
        $sellerServiceFeesCurrencyTotal = [];
        $quoteId = $order->getQuoteId();
        $quote = $this->quoteFactory->create()->load($quoteId);

        foreach ($order->getAllItems() as $orderItem) {
            foreach ($quote->getAllVisibleItems() as $item) {
                if ($orderItem->getProductId() == $item->getProductId()) {
                    $orderItem->setServiceTitleList($item->getServiceTitleList());
                    $orderItem->setServiceTitle($item->getServiceTitle());
                    $orderItem->setCurrentCurrencyServiceFees($item->getCurrentCurrencyServiceFees());
                    $orderItem->setServiceFees($item->getServiceFees());
                    $orderItem->save();
                }
            }
        }
        foreach ($order->getAllItems() as $item) {
            $itemData = $item->getData();
            $price = $itemData['base_price'];
            $sellerId = $this->getSellerIdPerProduct($item);
            $salesList = $this->salesList->create()->getCollection()
                ->addFieldToFilter("order_id", ["eq" => $order->getId()])
                ->addFieldToFilter("seller_id", ["eq" => $sellerId])
                ->addFieldToFilter("mageproduct_id", ["eq" => $item->getProductId()]);

            if ($salesList->getSize()) {
                $salesList->getFirstItem()
                    ->setCurrentCurrencyServiceFees($item->getCurrentCurrencyServiceFees())
                    ->setServiceFees($item->getServiceFees())
                    ->save();
                if ($price != 0.0000) {
                    if (!isset($sellerProArr[$sellerId])) {
                        $sellerProArr[$sellerId] = [];
                        $sellerServiceFeesTotal[$sellerId] = [];
                        $sellerServiceFeesCurrencyTotal[$sellerId] = [];
                    }
                    array_push($sellerProArr[$sellerId], $item->getProductId());
                    array_push($sellerServiceFeesTotal[$sellerId], $item->getServiceFees());
                    array_push($sellerServiceFeesCurrencyTotal[$sellerId], $item->getCurrentCurrencyServiceFees());
                } else {
                    if (!$item->getParentItemId()) {
                        if (!isset($sellerProArr[$sellerId])) {
                            $sellerProArr[$sellerId] = [];
                            $sellerServiceFeesTotal[$sellerId] = [];
                            $sellerServiceFeesCurrencyTotal[$sellerId] = [];
                        }
                        array_push($sellerProArr[$sellerId], $item->getProductId());
                        array_push($sellerServiceFeesTotal[$sellerId], $item->getServiceFees());
                        array_push($sellerServiceFeesCurrencyTotal[$sellerId], $item->getCurrentCurrencyServiceFees());
                    }
                }
            }
        }
        $sellerData = [
            'seller_pro_arr' => $sellerProArr,
            'seller_base_service_fees_arr' => $sellerServiceFeesTotal,
            'seller_currency_service_fees_arr' => $sellerServiceFeesCurrencyTotal,
        ];
        $sellerProArr = $sellerData['seller_pro_arr'];
        $sellerServiceFeesTotal = $sellerData['seller_base_service_fees_arr'];
        $sellerServiceFeesCurrencyTotal = $sellerData['seller_currency_service_fees_arr'];
        foreach ($sellerProArr as $key => $value) {
            $productIds = implode(',', $value);
            $sellerServiceFeesSum = array_sum($sellerServiceFeesTotal[$key]);
            $sellerServiceFeesCurrencySum = array_sum($sellerServiceFeesCurrencyTotal[$key]);
            $orderMarketplace = $this->orderFactory->create()->getCollection()
                ->addFieldToFilter("order_id", ["eq" => $order->getId()])
                ->addFieldToFilter("seller_id", ["eq" => $key])
                ->addFieldToFilter("product_ids", ["eq" => $productIds])
            ;
            $data = [
                'service_fees' => $sellerServiceFeesSum,
                'current_currency_service_fees' => $sellerServiceFeesCurrencySum,
            ];
            if ($orderMarketplace->getSize()) {
                $orderMarketplace->getFirstItem()
                    ->setCurrentCurrencyServiceFees($sellerServiceFeesCurrencySum)
                    ->setServiceFees($sellerServiceFeesSum)->save();
            }
        }
    }
    
    /**
     * Get Seller ID Per Product.
     *
     * @param \Magento\Sales\Model\Order\Item $item
     *
     * @return int
     */
    public function getSellerIdPerProduct($item)
    {
        $sellerId = $this->_marketplaceHelper->getSellerIdByProductId($item->getProductId());
        return $sellerId;
    }
}
