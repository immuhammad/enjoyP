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

namespace Webkul\MpServiceFee\Controller\Order;

use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\DB\Transaction;

/**
 * Webkul Marketplace Order Invoice Controller.
 */
class Invoice extends \Webkul\Marketplace\Controller\Order\Invoice
{

    /**
     * Constructor function
     *
     * @param InvoiceService $invoiceService
     * @param Transaction $transaction
     */
    public function __construct(
        InvoiceService $invoiceService,
        Transaction $transaction
    ) {
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
    }

    /**
     * Execute
     *
     * @return void
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
     * Create invoice
     *
     * @param \Webkul\Marketplace\Helper\Orders $marketplaceOrder
     * @param array $data
     * @param \Webkul\Marketplace\Helper\Data $helper
     * @param int $sellerId
     * @param int $orderId
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return void
     */
    public function createInvoice(
        $marketplaceOrder,
        $data,
        $helper,
        $sellerId,
        $orderId,
        $order
    ) {
        $items = [];
        $itemsarray = [];
        $shippingAmount = 0;
        $couponAmount = 0;
        $codcharges = 0;
        $paymentCode = '';
        $paymentMethod = '';
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

        $itemsarray = $this->_getItemQtys($order, $items);

        if (count($itemsarray) > 0 && $order->canInvoice()) {
            $invoice = $this->invoiceService->create()->prepareInvoice($order, $itemsarray['data']);
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
                $itemsarray['currentServiceFee'] +
                $itemsarray['subtotal'] +
                $currentShippingAmount +
                $currentCodcharges +
                $currentTaxAmount -
                $currentCouponAmount
            );
            $invoice->setBaseGrandTotal(
                $itemsarray['serviceFee'] +
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

            $transactionSave = $this->transaction->create()->addObject(
                $invoicezzz
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
        if ($invoiceId != '') {
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
                foreach ($saleslistColl as $saleslist) {
                    $saleslist->setCollectCodStatus(1);
                    $saleslist->save();
                }
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

    /**
     * Invice execution
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
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
                    $this->createInvoice(
                        $marketplaceOrder,
                        $data,
                        $helper,
                        $sellerId,
                        $orderId,
                        $order
                    );
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Controller_Order_Invoice doInvoiceExecution : " . $e->getMessage()
            );
            $this->messageManager->addError(
                __('We can\'t save the invoice right now.')
            );
            $this->messageManager->addError($e->getMessage());
        }
    }

    /**
     * Get Item Child Prod
     *
     * @param \Magento\Sales\Model\Order\Item $_item
     * @return void
     */
    public function getItemChildProd($_item)
    {
        return array_merge([$_item], $_item->getChildrenItems());
    }
    
    /**
     * Get item qty
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param array $items
     * @return void
     */
    protected function _getItemQtys($order, $items)
    {
        $data = [];
        $subtotal = 0;
        $baseSubtotal = 0;
        $serviceFee = 0;
        $currentServiceFee = 0;
        foreach ($order->getAllItems() as $item) {
            if (in_array($item->getItemId(), $items)) {
                $data[$item->getItemId()] = (int) ($item->getQtyOrdered() - ($item
                        ->getQtyInvoiced() + $item->getQtyCanceled()));

                $_item = $item;

                // for bundle product
                $bundleitems = $this->getItemChildProd($_item);

                if ($_item->getParentItem()) {
                    continue;
                }

                if ($_item->getProductType() == 'bundle') {
                    foreach ($bundleitems as $_bundleitem) {
                        if ($_bundleitem->getParentItem()) {
                            $data[$_bundleitem->getItemId()] = (int) (
                                $_bundleitem->getQtyOrdered() - ($_bundleitem
                                        ->getQtyInvoiced() + $_bundleitem->getQtyCanceled())
                            );
                        }
                    }
                }
                $subtotal += $_item->getRowTotal();
                $baseSubtotal += $_item->getBaseRowTotal();
                $currentServiceFee += $_item->getCurrentCurrencyServiceFees();
                $serviceFee += $_item->getServiceFees();
            } else {
                if (!$item->getParentItemId()) {
                    $data[$item->getItemId()] = 0;
                }
            }
        }

        return [
            'data' => $data,
            'subtotal' => $subtotal,
            'baseSubtotal' => $baseSubtotal,
            'currentServiceFee' => $currentServiceFee,
            'serviceFee' => $serviceFee,
        ];
    }
}
