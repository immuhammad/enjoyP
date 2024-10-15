<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpStripe
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpStripe\Model\Order\Creditmemo\Total;

class Tax extends \Magento\Sales\Model\Order\Creditmemo\Total\Tax
{

    /**
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Webkul\Marketplace\Model\SaleslistFactory $sellerSalesList
     */
    public function __construct(
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Framework\App\RequestInterface $request,
        \Webkul\Marketplace\Model\SaleslistFactory $sellerSalesList
    ) {
        $this->mpHelper = $mpHelper;
        $this->sellerSalesList = $sellerSalesList;
        $this->request = $request;
    }
    /**
     * Collects credit memo taxes.
     *
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $shippingTaxAmountOverride = 0;
        $baseShippingTaxAmountOverride = 0;
        $totalTaxOverride = 0;
        $baseTotalTaxOverride = 0;
        $totalDiscountTaxCompensationOvrRd = 0;
        $baseTotalDiscountTaxCompensationOvrrd = 0;
        
        $order = $creditmemo->getOrder();

        /** @var $item \Magento\Sales\Model\Order\Creditmemo\Item */
        foreach ($creditmemo->getAllItems() as $item) {
            $orderItem = $item->getOrderItem();
            if ($orderItem->isDummy() || $item->getQty() <= 0) {
                continue;
            }
            $orderItemTax = (double)$orderItem->getTaxInvoiced();
            $baseOrderItemTax = (double)$orderItem->getBaseTaxInvoiced();
            $orderItemQty = (double)$orderItem->getQtyInvoiced();

            if ($orderItemTax && $orderItemQty) {

                $taxOverride = $orderItemTax - $orderItem->getTaxRefunded();
                $baseTaxOverride = $baseOrderItemTax - $orderItem->getBaseTaxRefunded();
                $discountTaxCompensation = $orderItem->getDiscountTaxCompensationInvoiced() -
                    $orderItem->getDiscountTaxCompensationRefunded();
                $baseDiscountTaxCompensation = $orderItem->getBaseDiscountTaxCompensationInvoiced() -
                    $orderItem->getBaseDiscountTaxCompensationRefunded();
                if (!$item->isLast()) {
                    $availableQty = $orderItemQty - $orderItem->getQtyRefunded();
                    $taxOverride= $creditmemo->roundPrice($taxOverride/ $availableQty * $item->getQty());
                    $baseTaxOverride = $creditmemo->roundPrice(
                        $baseTaxOverride / $availableQty * $item->getQty(),
                        'base'
                    );
                    $discountTaxCompensation =
                        $creditmemo->roundPrice($discountTaxCompensation / $availableQty * $item->getQty());
                    $baseDiscountTaxCompensation =
                        $creditmemo->roundPrice($baseDiscountTaxCompensation / $availableQty * $item->getQty(), 'base');
                }
                if (!$this->mpHelper->getConfigTaxManage()) {
                    $taxArr = $this->manageTax($creditmemo, $taxOverride, $baseTaxOverride);
                    $taxOverride = $taxArr['tax'];
                    $baseTaxOverride = $taxArr['baseTax'];
                }
                $item->setTaxAmount($taxOverride);
                $item->setBaseTaxAmount($baseTaxOverride);
                $item->setDiscountTaxCompensationAmount($discountTaxCompensation);
                $item->setBaseDiscountTaxCompensationAmount($baseDiscountTaxCompensation);

                $totalTaxOverride += $taxOverride;
                $baseTotalTaxOverride += $baseTaxOverride;
                $totalDiscountTaxCompensationOvrRd += $discountTaxCompensation;
                $baseTotalDiscountTaxCompensationOvrrd += $baseDiscountTaxCompensation;
            }
        }

        $isPartialShippingRefunded = false;
        $baseOrderShippingAmountOverride = (float)$order->getBaseShippingAmount();
        if ($invoice = $creditmemo->getInvoice()) {
            //recalculate tax amounts in case if refund shipping value was changed
            if ($baseOrderShippingAmountOverride && $creditmemo->getBaseShippingAmount() !== null) {
                $taxFactor = $creditmemo->getBaseShippingAmount() / $baseOrderShippingAmountOverride;
                $shippingTaxAmountOverride = $invoice->getShippingTaxAmount() * $taxFactor;
                $baseShippingTaxAmountOverride = $invoice->getBaseShippingTaxAmount() * $taxFactor;
                $totalDiscountTaxCompensationOvrRd += $invoice->getShippingDiscountTaxCompensationAmount() * $taxFactor;
                $baseTotalDiscountTaxCompensationOvrrd +=
                    $invoice->getBaseShippingDiscountTaxCompensationAmnt() * $taxFactor;
                $shippingTaxAmountOverride = $creditmemo->roundPrice($shippingTaxAmountOverride);
                $baseShippingTaxAmountOverride = $creditmemo->roundPrice($baseShippingTaxAmountOverride, 'base');
                $totalDiscountTaxCompensationOvrRd = $creditmemo->roundPrice($totalDiscountTaxCompensationOvrRd);
                $baseTotalDiscountTaxCompensationOvrrd = $creditmemo->roundPrice(
                    $baseTotalDiscountTaxCompensationOvrrd,
                    'base'
                );
                if ($taxFactor < 1 && $invoice->getShippingTaxAmount() > 0) {
                    $isPartialShippingRefunded = true;
                }
                $totalTaxOverride += $shippingTaxAmountOverride;
                $baseTotalTaxOverride += $baseShippingTaxAmountOverride;
            }
        } else {
            $orderShippingAmountOverride = $order->getShippingAmount();

            $baseOrderShippingRefundedAmountOverride = $order->getBaseShippingRefunded();

            $shippingTaxAmountOverride = 0;
            $baseShippingTaxAmountOverride = 0;
            $shippingDiscountTaxCompensationAmountOverride = 0;
            $baseShippingDiscountTaxCompensationAmountOverride = 0;

            $shippingDeltaOverride = $baseOrderShippingAmountOverride - $baseOrderShippingRefundedAmountOverride;

            if ($shippingDeltaOverride > $creditmemo->getBaseShippingAmount()) {
                $part = $creditmemo->getShippingAmount() / $orderShippingAmountOverride;
                $basePart = $creditmemo->getBaseShippingAmount() / $baseOrderShippingAmountOverride;
                $shippingTaxAmountOverride = $order->getShippingTaxAmount() * $part;
                $baseShippingTaxAmountOverride = $order->getBaseShippingTaxAmount() * $basePart;
                $shippingDiscountTaxCompensationAmountOverride =
                    $order->getShippingDiscountTaxCompensationAmount() * $part;
                $baseShippingDiscountTaxCompensationAmountOverride =
                    $order->getBaseShippingDiscountTaxCompensationAmnt() * $basePart;
                $shippingTaxAmountOverride = $creditmemo->roundPrice($shippingTaxAmountOverride);
                $baseShippingTaxAmountOverride = $creditmemo->roundPrice($baseShippingTaxAmountOverride, 'base');
                $shippingDiscountTaxCompensationAmountOverride =
                    $creditmemo->roundPrice($shippingDiscountTaxCompensationAmountOverride);
                $baseShippingDiscountTaxCompensationAmountOverride =
                    $creditmemo->roundPrice($baseShippingDiscountTaxCompensationAmountOverride, 'base');
                if ($part < 1 && $order->getShippingTaxAmount() > 0) {
                    $isPartialShippingRefunded = true;
                }
            } elseif ($shippingDeltaOverride == $creditmemo->getBaseShippingAmount()) {
                $shippingTaxAmountOverride = $order->getShippingTaxAmount() - $order->getShippingTaxRefunded();
                $baseShippingTaxAmountOverride = $order->getBaseShippingTaxAmount() -
                    $order->getBaseShippingTaxRefunded();
                $shippingDiscountTaxCompensationAmountOverride = $order->getShippingDiscountTaxCompensationAmount() -
                    $order->getShippingDiscountTaxCompensationRefunded();
                $baseShippingDiscountTaxCompensationAmountOverride =
                    $order->getBaseShippingDiscountTaxCompensationAmnt() -
                    $order->getBaseShippingDiscountTaxCompensationRefunded();
            }
            $totalTaxOverride += $shippingTaxAmountOverride;
            $baseTotalTaxOverride += $baseShippingTaxAmountOverride;
            $totalDiscountTaxCompensationOvrRd += $shippingDiscountTaxCompensationAmountOverride;
            $baseTotalDiscountTaxCompensationOvrrd += $baseShippingDiscountTaxCompensationAmountOverride;
        }
        if ($creditmemo->getInvoice() == null || $creditmemo->getInvoice()->getId() == null) {
            $allowedTaxOverride = $order->getTaxInvoiced() - $order->getTaxRefunded() - $creditmemo->getTaxAmount();
            $allowedBaseTax = $order->getBaseTaxInvoiced() - $order->getBaseTaxRefunded() -
            $creditmemo->getBaseTaxAmount();
            $allowedDiscountTaxCompensation = $order->getDiscountTaxCompensationInvoiced() +
                $order->getShippingDiscountTaxCompensationAmount() -
                $order->getDiscountTaxCompensationRefunded() -
                $order->getShippingDiscountTaxCompensationRefunded() -
                $creditmemo->getDiscountTaxCompensationAmount() -
                $creditmemo->getShippingDiscountTaxCompensationAmount();
            $allowedBaseDiscountTaxCompensation = $order->getBaseDiscountTaxCompensationInvoiced() +
                $order->getBaseShippingDiscountTaxCompensationAmnt() -
                $order->getBaseDiscountTaxCompensationRefunded() -
                $order->getBaseShippingDiscountTaxCompensationRefunded() -
                $creditmemo->getBaseShippingDiscountTaxCompensationAmnt() -
                $creditmemo->getBaseDiscountTaxCompensationAmount();
    
            if ($creditmemo->isLast() && !$isPartialShippingRefunded) {
                $totalTaxOverride = $allowedTaxOverride;
                $baseTotalTaxOverride = $allowedBaseTax;
                $totalDiscountTaxCompensationOvrRd = $allowedDiscountTaxCompensation;
                $baseTotalDiscountTaxCompensationOvrrd = $allowedBaseDiscountTaxCompensation;
            } else {
                $totalTaxOverride = min($allowedTaxOverride, $totalTaxOverride);
                $baseTotalTaxOverride = min($allowedBaseTax, $baseTotalTaxOverride);
                $totalDiscountTaxCompensationOvrRd =
                    min($allowedDiscountTaxCompensation, $totalDiscountTaxCompensationOvrRd);
                $baseTotalDiscountTaxCompensationOvrrd =
                    min($allowedBaseDiscountTaxCompensation, $baseTotalDiscountTaxCompensationOvrrd);
            }
        } else {
            if (!$this->mpHelper->getConfigTaxManage()) {
                $taxArr = $this->manageTax($creditmemo, $totalTaxOverride, $baseTotalTaxOverride);
                $totalTaxOverride = $taxArr['tax'];
                $baseTotalTaxOverride = $taxArr['baseTax'];
                if ($totalTaxOverride != 0) {
                    $totalTaxOverride = $order->getTaxAmount();
                    $baseTotalTaxOverride = $order->getBaseTaxAmount();
                }
            }
        }
        
        $creditmemo->setTaxAmount($creditmemo->getTaxAmount() + $totalTaxOverride);
        $creditmemo->setBaseTaxAmount($creditmemo->getBaseTaxAmount() + $baseTotalTaxOverride);
        $creditmemo->setDiscountTaxCompensationAmount($totalDiscountTaxCompensationOvrRd);
        $creditmemo->setBaseDiscountTaxCompensationAmount($baseTotalDiscountTaxCompensationOvrrd);

        $creditmemo->setShippingTaxAmount($shippingTaxAmountOverride);
        $creditmemo->setBaseShippingTaxAmount($baseShippingTaxAmountOverride);

        $creditmemo->setGrandTotal(
            $creditmemo->getGrandTotal() + $totalTaxOverride + $totalDiscountTaxCompensationOvrRd
        );
        $creditmemo->setBaseGrandTotal(
            $creditmemo->getBaseGrandTotal() +
            $baseTotalTaxOverride + $baseTotalDiscountTaxCompensationOvrrd
        );
        return $this;
    }

    /**
     * ManageTax function
     *
     * @param Object $creditmemo
     * @param int $tax
     * @param int $baseTaxOverride
     * @return array
     */
    public function manageTax($creditmemo, $tax, $baseTaxOverride)
    {
        $taxArr = [];
        $taxArr['tax'] = $tax;
        $taxArr['baseTax'] = $baseTaxOverride;
        $sellerInvoice = $this->sellerSalesList->create()->getCollection()
        ->addFieldToFilter('order_id', $creditmemo->getOrderId());
        $requestData = $this->request->getParam('creditmemo');
        if (count($sellerInvoice) && isset($requestData['items'])) {
            foreach ($sellerInvoice as $invoice) {
                $orderItemId = $invoice->getOrderItemId();
                if (array_key_exists($orderItemId, $requestData['items'])) {
                    $taxArr['tax'] = 0;
                    $taxArr['baseTax'] = 0;
                }
            }
        } else {
            $taxArr['tax'] = 1;
            $taxArr['baseTax'] = 1;
        }
        return $taxArr;
    }
}
