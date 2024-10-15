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
use Webkul\MpServiceFee\Helper\Servicehelper;

/**
 * Webkul Service Fees SalesOrderPlaceAfterObserver Observer.
 */
class MarketplaceInvoiceSaveAfter implements ObserverInterface
{
    
    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $_quote;

    /**
     * @var \Webkul\MpServiceFee\Helper\Servicehelper
     */
    protected $_helper;

    /**
     * @param \Magento\Quote\Model\QuoteFactory $quoteModel
     * @param \Webkul\MpServiceFee\Helper\Servicehelper $helper
     */
    public function __construct(
        \Magento\Quote\Model\QuoteFactory $quoteModel,
        \Webkul\MpServiceFee\Helper\Servicehelper $helper
    ) {
        $this->_quote = $quoteModel;
        $this->_helper = $helper;
    }

    /**
     * Marketplace order save after event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $currentCurrencyServiceFees = 0;
            $currentCurrencyServiceBaseFees = 0;
            $order = $observer->getOrder();
            $invoice = $observer->getInvoice();
            if ($invoice) {
                $itemsInvoice = $invoice->getAllItems();
                $items = $order->getAllItems();
                foreach ($itemsInvoice as $itemInvoice) {
                    foreach ($items as $item) {
                        if ($itemInvoice->getProductId() == $item->getProductId()) {
                            $currentCurrencyServiceBaseFees += $item->getServiceFees();
                            $currentCurrencyServiceFees += $item->getCurrentCurrencyServiceFees();
                        }
                    }
                }
                if ($currentCurrencyServiceFees > 0) {
                    $invoice->setGrandTotal($invoice->getGrandTotal() + $currentCurrencyServiceFees);
                    $invoice->setBaseGrandTotal($invoice->setBaseGrandTotal() + $currentCurrencyServiceBaseFees);
                    $invoice->save();
                }
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotDeleteException(
                __("Invoice issue in Mp ServiceFee")
            );
        }
    }
}
