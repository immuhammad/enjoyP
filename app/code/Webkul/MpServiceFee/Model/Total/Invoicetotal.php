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
namespace Webkul\MpServiceFee\Model\Total;

class Invoicetotal extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{
    
    /**
     * Collect invoice Wallet amount.
     *
     * @param \Magento\Sales\Model\Order\Invoice  $invoice
     *
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Invoice  $invoice)
    {
        $order = $invoice->getOrder();
        $value = 0;
        $baseValue = 0;
        $_items = $invoice->getAllItems();
        $orderItems = $order->getAllItems();
        foreach ($_items as $item) {
            foreach ($orderItems as $orderItem) {
                if ($orderItem->getProductId() == $item->getProductId()) {
                    $value += $orderItem->getServiceFees();
                    $baseValue += $orderItem->getCurrentCurrencyServiceFees();
                }
            }
        }
        $invoice->setGrandTotal($invoice->getGrandTotal() + $value);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseValue);
        return $this;
    }
}
