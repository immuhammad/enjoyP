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

namespace Webkul\MpRmaSystem\Controller\Rewrite\Order;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Webkul\Marketplace\Helper\Notification as NotificationHelper;
use Webkul\Marketplace\Model\Notification;
use Webkul\Marketplace\Helper\Data as HelperData;
use Webkul\Marketplace\Model\SaleslistFactory;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Response\Http\FileFactory;
use Webkul\Marketplace\Model\OrdersFactory as MpOrdersModel;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Collection as InvoiceCollection;
use Webkul\Marketplace\Model\SellerFactory as MpSellerModel;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RawFactory;

class Invoice extends \Webkul\Marketplace\Controller\Order
{
    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;

    /**
     * @var \Magento\Framework\DB\Transaction
     */
    protected $dbTransaction;

    /**
     * Initialize Dependencies
     *
     * @param Context $context
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Framework\DB\Transaction $dbTransaction
     * @param PageFactory $resultPageFactory
     * @param InvoiceSender $invoiceSender
     * @param ShipmentSender $shipmentSender
     * @param ShipmentFactory $shipmentFactory
     * @param Shipment $shipment
     * @param CreditmemoSender $creditmemoSender
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param CreditmemoFactory $creditmemoFactory
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository
     * @param StockConfigurationInterface $stockConfiguration
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderManagementInterface $orderManagement
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Webkul\Marketplace\Helper\Orders $orderHelper
     * @param NotificationHelper $notificationHelper
     * @param HelperData $helper
     * @param \Magento\Sales\Api\CreditmemoManagementInterface $creditmemoManagement
     * @param SaleslistFactory $saleslistFactory
     * @param CustomerUrl $customerUrl
     * @param DateTime $date
     * @param FileFactory $fileFactory
     * @param \Webkul\Marketplace\Model\Order\Pdf\Creditmemo $creditmemoPdf
     * @param \Webkul\Marketplace\Model\Order\Pdf\Invoice $invoicePdf
     * @param MpOrdersModel $mpOrdersModel
     * @param InvoiceCollection $invoiceCollection
     * @param \Magento\Sales\Api\InvoiceManagementInterface $invoiceManagement
     * @param \Magento\Catalog\Model\ProductFactory $productModel
     * @param MpSellerModel $mpSellerModel
     * @param \Psr\Log\LoggerInterface $logger
     * @return void
     */
    public function __construct(
        Context $context,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $dbTransaction,
        PageFactory $resultPageFactory,
        InvoiceSender $invoiceSender,
        ShipmentSender $shipmentSender,
        ShipmentFactory $shipmentFactory,
        Shipment $shipment,
        CreditmemoSender $creditmemoSender,
        CreditmemoRepositoryInterface $creditmemoRepository,
        CreditmemoFactory $creditmemoFactory,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        StockConfigurationInterface $stockConfiguration,
        OrderRepositoryInterface $orderRepository,
        OrderManagementInterface $orderManagement,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\Marketplace\Helper\Orders $orderHelper,
        NotificationHelper $notificationHelper,
        HelperData $helper,
        \Magento\Sales\Api\CreditmemoManagementInterface $creditmemoManagement,
        SaleslistFactory $saleslistFactory,
        CustomerUrl $customerUrl,
        DateTime $date,
        FileFactory $fileFactory,
        \Webkul\Marketplace\Model\Order\Pdf\Creditmemo $creditmemoPdf,
        \Webkul\Marketplace\Model\Order\Pdf\Invoice $invoicePdf,
        MpOrdersModel $mpOrdersModel,
        InvoiceCollection $invoiceCollection,
        \Magento\Sales\Api\InvoiceManagementInterface $invoiceManagement,
        \Magento\Catalog\Model\ProductFactory $productModel,
        MpSellerModel $mpSellerModel,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->invoiceService = $invoiceService;
        $this->dbTransaction  = $dbTransaction;
        parent::__construct(
            $context,
            $resultPageFactory,
            $invoiceSender,
            $shipmentSender,
            $shipmentFactory,
            $shipment,
            $creditmemoSender,
            $creditmemoRepository,
            $creditmemoFactory,
            $invoiceRepository,
            $stockConfiguration,
            $orderRepository,
            $orderManagement,
            $coreRegistry,
            $customerSession,
            $orderHelper,
            $notificationHelper,
            $helper,
            $creditmemoManagement,
            $saleslistFactory,
            $customerUrl,
            $date,
            $fileFactory,
            $creditmemoPdf,
            $invoicePdf,
            $mpOrdersModel,
            $invoiceCollection,
            $invoiceManagement,
            $productModel,
            $mpSellerModel,
            $logger
        );
    }

    /**
     * Marketplace order invoice controller.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $helper = $this->helper;
        $isPartner = $helper->isSeller();
        if ($isPartner == 1) {
            if ($order = $this->_initOrder()) {
                $this->doInvoiceExecution($order);
                $this->doAdminShippingInvoiceExecution($order);

                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/view',
                    [
                        'id' => $order->getEntityId(),
                        '_secure' => $this->getRequest()->isSecure(),
                    ]
                );
            } else {
                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/history',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }

    /**
     * Invoice Execute
     *
     * @param \Webkul\Marketplace\Controller\Order $order
     * @return void
     */
    protected function doInvoiceExecution($order)
    {
        try {
            $helper = $this->helper;
            $sellerId = $this->_customerSession->getCustomerId();
            $orderId = $order->getId();
            if ($order->canUnhold()) {
                $this->messageManager->addError(
                    __('Can not create invoice as order is in HOLD state')
                );
            } else {
                $data = [];
                $data['send_email'] = 1;
                $marketplaceOrder = $this->orderHelper->getOrderinfo($orderId);
                $invoiceId = $marketplaceOrder->getInvoiceId();
                if (!$invoiceId) {
                    $items = [];
                    $itemsarray = [];
                    $paymentCode = '';
                    $paymentMethod = '';
                    $resultTrackingData = $this->getTrackingData(
                        $order,
                        $sellerId
                    );
                    $shippingAmount = $resultTrackingData['shippingAmount'] ?? 0;
                    $couponAmount = $resultTrackingData['couponAmount'] ?? 0;
                    $codcharges = $resultTrackingData['codcharges'] ?? 0;
                    $paymentCode = $resultTrackingData['paymentCode'] ?? '';
                    
                    $resultSaleProduct = $this->getSaleProduct($order, $sellerId, $paymentCode, $items);
                    $codCharges = $resultSaleProduct['codCharges'] ?? 0;
                    $tax = $resultSaleProduct['tax'] ?? 0;
                    $currencyRate = $resultSaleProduct['currencyRate'] ?? 0;
                    $itemsarray = $resultSaleProduct['itemsarray'] ?? [];
                    
                    if (!empty($itemsarray) > 0 && $order->canInvoice()) {
                        $itemsarrayData = $itemsarray['data'];
                        $invoice = $this->createInvoice($order, $itemsarray, $itemsarrayData);
                        $currentCouponAmount = $currencyRate * $couponAmount;
                        $currentShippingAmount = $currencyRate * $shippingAmount;
                        $currentTaxAmount = $currencyRate * $tax;
                        $currentCodcharges = $currencyRate * $codcharges;
                        $invoice->setBaseDiscountAmount($couponAmount);
                        $invoice->setDiscountAmount($currentCouponAmount);
                        $invoice->setShippingAmount($currentShippingAmount);
                        $invoice->setBaseShippingInclTax($shippingAmount);
                        $invoice->setBaseShippingAmount($shippingAmount);
                        $invoice->setSubtotal($itemsarray['subtotal']);
                        $invoice->setBaseSubtotal($itemsarray['baseSubtotal']);
                        if ($paymentCode == 'mpcashondelivery') {
                            $invoice->setMpcashondelivery($currentCodcharges);
                            $invoice->setBaseMpcashondelivery($codCharges);
                        }
                        $invoice->setGrandTotal(
                            $itemsarray['subtotal'] +
                            $currentShippingAmount +
                            $currentCodcharges +
                            $currentTaxAmount -
                            $currentCouponAmount
                        );
                        $invoice->setBaseGrandTotal(
                            $itemsarray['baseSubtotal'] +
                            $shippingAmount +
                            $codcharges +
                            $tax -
                            $couponAmount
                        );
                        
                        $invoice->register();

                        $invoice->getOrder()->setCustomerNoteNotify(
                            !empty($data['send_email'])
                        );
                        $invoice->getOrder()->setIsInProcess(true);

                        $transactionSave = $this->dbTransaction->addObject(
                            $invoice
                        )->addObject(
                            $invoice->getOrder()
                        );
                        $transactionSave->save();

                        $invoiceId = $invoice->getId();

                        $this->_invoiceSender->send($invoice);

                        $this->messageManager->addSuccess(
                            __('Invoice has been created for this order.')
                        );
                    }
                    /*update mpcod table records*/
                    if ($invoiceId != '') {
                        $this->updateMpcod($order, $invoiceId, $sellerId, $paymentCode);
                    }
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Controller_Order_Invoice doInvoiceExecution : ".$e->getMessage()
            );
            $this->messageManager->addError(
                __('We can\'t save the invoice right now.')
            );
            $this->messageManager->addError($e->getMessage());
        }
    }

    /**
     * Tracking Order Data
     *
     * @param \Webkul\Marketplace\Controller\Order $order
     * @param int $sellerId
     * @return void
     */
    protected function getTrackingData($order, $sellerId)
    {
        $shippingAmount = 0;
        $couponAmount = 0;
        $codcharges = 0;
        $paymentCode = 0;
        try {
            $orderId = $order->getId();
            if ($order->getPayment()) {
                $paymentCode = $order->getPayment()->getMethod();
            }
            $trackingsdata = $this->mpOrdersModel->create()
                ->getCollection()
                ->addFieldToFilter(
                    'order_id',
                    $orderId
                )
                ->addFieldToFilter(
                    'seller_id',
                    $sellerId
                );
            foreach ($trackingsdata as $tracking) {
                $shippingAmount = $tracking->getShippingCharges();
                $couponAmount = $tracking->getCouponAmount();
                if ($paymentCode == 'mpcashondelivery') {
                    $codcharges = $tracking->getCodCharges();
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->helper->logDataInLogger(
                "getTrackingData : ".$e->getMessage()
            );
        }
        return ['shippingAmount' => $shippingAmount,'couponAmount' => $couponAmount,
            'codcharges' => $codcharges,'paymentCode' => $paymentCode];
    }

    /**
     * Get Sale Product
     *
     * @param \Webkul\Marketplace\Controller\Order $order
     * @param int $sellerId
     * @param int $paymentCode
     * @param array $items
     * @return void
     */
    protected function getSaleProduct($order, $sellerId, $paymentCode, $items)
    {
        try {
            $orderId = $order->getId();
            $codCharges = 0;
            $tax = 0;
            $currencyRate = 1;
            $collection = $this->saleslistFactory->create()
            ->getCollection()
            ->addFieldToFilter(
                'order_id',
                ['eq' => $orderId]
            )
            ->addFieldToFilter(
                'seller_id',
                ['eq' => $sellerId]
            );
            foreach ($collection as $saleproduct) {
                $currencyRate = $saleproduct->getCurrencyRate();
                if ($paymentCode == 'mpcashondelivery') {
                    $codCharges = $codCharges + $saleproduct->getCodCharges();
                }
                $tax = $tax + $saleproduct->getTotalTax();
                array_push($items, $saleproduct['order_item_id']);
            }

            $itemsarray = $this->getItemQtys($order, $items);
            
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->helper->logDataInLogger(
                "getSaleProduct : ".$e->getMessage()
            );
        }
        return ['currencyRate' => $currencyRate,'codCharges' => $codCharges,'tax' => $tax, 'itemsarray' => $itemsarray];
    }

    /**
     * Create Invoice
     *
     * @param \Webkul\Marketplace\Controller\Order $order
     * @param array $itemsarray
     * @param array|null $itemsarrayData
     * @return void
     */
    protected function createInvoice($order, $itemsarray, $itemsarrayData)
    {
        $orderId = $order->getId();
        $invoice = $this->invoiceService->prepareInvoice($order, $itemsarrayData);
        if (!$invoice) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We can\'t save the invoice right now.')
            );
        }
        if (!$invoice->getTotalQty()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('You can\'t create an invoice without products.')
            );
        }
        $this->_coreRegistry->register(
            'current_invoice',
            $invoice
        );

        if (!empty($data['capture_case'])) {
            $invoice->setRequestedCaptureCase(
                $data['capture_case']
            );
        }

        if (!empty($data['comment_text'])) {
            $invoice->addComment(
                $data['comment_text'],
                isset($data['comment_customer_notify']),
                isset($data['is_visible_on_front'])
            );

            $invoice->setCustomerNote($data['comment_text']);
            $invoice->setCustomerNoteNotify(
                isset($data['comment_customer_notify'])
            );
        }
        return $invoice;
    }

    /**
     * Update Marketplace Cod
     *
     * @param \Webkul\Marketplace\Controller\Order $order
     * @param int $invoiceId
     * @param int $sellerId
     * @param int $paymentCode
     * @return void
     */
    protected function updateMpcod($order, $invoiceId, $sellerId, $paymentCode)
    {
        $orderId = $order->getId();
        if ($paymentCode == 'mpcashondelivery') {
            $saleslistColl = $this->saleslistFactory->create()
            ->getCollection()
            ->addFieldToFilter(
                'order_id',
                $orderId
            )
            ->addFieldToFilter(
                'seller_id',
                $sellerId
            );
            $this->changeCodStatus($saleslistColl);
        }

        $trackingcol1 = $this->mpOrdersModel->create()
        ->getCollection()
        ->addFieldToFilter(
            'order_id',
            $orderId
        )
        ->addFieldToFilter(
            'seller_id',
            $sellerId
        );
        $this->setStatus($trackingcol1, $invoiceId);
    }

    /**
     * AdminShippingInvoiceExecution function
     *
     * @param \Webkul\Marketplace\Controller\Order $order
     * @return void
     */
    protected function doAdminShippingInvoiceExecution($order)
    {
        try {
            $paymentCode = '';
            $paymentMethod = '';
            if ($order->getPayment()) {
                $paymentCode = $order->getPayment()->getMethod();
            }
            if (!$order->canUnhold() && ($order->getGrandTotal() > $order->getTotalPaid())) {
                $isAllItemInvoiced = $this->isAllItemInvoiced($order);

                if ($isAllItemInvoiced && $order->getShippingAmount()) {
                    $invoice = $this->invoiceService->prepareInvoice(
                        $order,
                        []
                    );
                    if (!$invoice) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('We can\'t save the invoice right now.')
                        );
                    }

                    $baseSubtotal = $order->getBaseShippingAmount();
                    $subtotal = $order->getShippingAmount();

                    if (!empty($data['capture_case'])) {
                        $invoice->setRequestedCaptureCase(
                            $data['capture_case']
                        );
                    }

                    if (!empty($data['comment_text'])) {
                        $invoice->addComment(
                            $data['comment_text'],
                            isset($data['comment_customer_notify']),
                            isset($data['is_visible_on_front'])
                        );

                        $invoice->setCustomerNote($data['comment_text']);
                        $invoice->setCustomerNoteNotify(
                            isset($data['comment_customer_notify'])
                        );
                    }
                    $invoice->setShippingAmount($subtotal);
                    $invoice->setBaseShippingInclTax($baseSubtotal);
                    $invoice->setBaseShippingAmount($baseSubtotal);
                    $invoice->setSubtotal($subtotal);
                    $invoice->setBaseSubtotal($baseSubtotal);
                    $invoice->setGrandTotal($subtotal);
                    $invoice->setBaseGrandTotal($baseSubtotal);
                    $invoice->register();

                    $invoice->getOrder()->setCustomerNoteNotify(
                        !empty($data['send_email'])
                    );
                    $invoice->getOrder()->setIsInProcess(true);

                    $transactionSave = $this->dbTransaction->addObject(
                        $invoice
                    )->addObject(
                        $invoice->getOrder()
                    );
                    $transactionSave->save();

                    $this->_eventManager->dispatch(
                        'mp_order_shipping_invoice_save_after',
                        ['invoice' => $invoice, 'order' => $order]
                    );

                    $this->_invoiceSender->send($invoice);
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->helper->logDataInLogger(
                "Controller_Order_Invoice doAdminShippingInvoiceExecution : ".$e->getMessage()
            );
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Controller_Order_Invoice doAdminShippingInvoiceExecution : ".$e->getMessage()
            );
        }
    }

    /**
     * Get item qty
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param array $items
     * @return array
     */
    protected function getItemQtys($order, $items)
    {
        $data = [];
        $subtotal = 0;
        $baseSubtotal = 0;
        foreach ($order->getAllItems() as $item) {
            if (in_array($item->getItemId(), $items)) {
                $data[$item->getItemId()] = (int)(
                    $item->getQtyOrdered() - ($item->getQtyInvoiced() + $item->getQtyCanceled())
                );

                $_item = $item;

                // for bundle product
                $bundleitems = $this->mergerArray($_item);
                if ($_item->getParentItem()) {
                    continue;
                }

                if ($_item->getProductType() == 'bundle') {
                    foreach ($bundleitems as $_bundleitem) {
                        if ($_bundleitem->getParentItem()) {
                            $data[$_bundleitem->getItemId()] = (int)(
                                $_bundleitem->getQtyOrdered() - ($_bundleitem->getQtyInvoiced() +
                                $_bundleitem->getQtyCanceled())
                            );
                        }
                    }
                }
                $subtotal += $_item->getRowTotal();
                $baseSubtotal += $_item->getBaseRowTotal();
            } else {
                if (!$item->getParentItemId()) {
                    $data[$item->getItemId()] = 0;
                }
            }
        }

        return ['data' => $data,'subtotal' => $subtotal,'baseSubtotal' => $baseSubtotal];
    }

    /**
     * Merge item for bundle product
     *
     * @param array $_item
     * @return array
     */
    public function mergerArray($_item)
    {
        return $bundleitems = array_merge([$_item], $_item->getChildrenItems());
    }

    /**
     * Change Cod Status function
     *
     * @param Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory $saleslistColl
     * @return void
     */
    protected function changeCodStatus($saleslistColl)
    {
        foreach ($saleslistColl as $saleslist) {
            $saleslist->setCollectCodStatus(1);
            $saleslist->save();
        }
    }

    /**
     * Set status
     *
     * @param Webkul\Marketplace\Model\ResourceModel\Orders\CollectionFactory $trackingcol1
     * @param int $invoiceId
     * @return void
     */
    protected function setStatus($trackingcol1, $invoiceId)
    {
        foreach ($trackingcol1 as $row) {
            $row->setInvoiceId($invoiceId);
            if ($row->getShipmentId()) {
                $row->setOrderStatus('complete');
            } else {
                $row->setOrderStatus('processing');
            }
            $row->save();
        }
    }
}
