<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mpsplitorder
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mpsplitorder\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManager;

class SalesOrderSaveAfterObserver implements ObserverInterface
{
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Webkul\Mpsplitorder\Logger\Mpsplitorder $splitorderLogger,
        SessionManager $session
    ) {
        $this->splitorderLogger = $splitorderLogger;
        $this->_objectManager = $objectManager;
        $this->session = $session;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customDiscount = $observer->getQuote()->getCustomDiscount();
        $customBaseDiscount = $observer->getQuote()->getBaseCustomDiscount();
        if (($customDiscount || $customBaseDiscount) && $this->session->getSplitSellers()>1) {
            if ($customDiscount>0) {
                $customDiscount *= -1;
            }
            if ($customBaseDiscount>0) {
                $customBaseDiscount *= -1;
            }
            $code = $this->session->getDiscountDescription();
            $discounts = $this->session->getItemDiscount();
            $baseDiscounts = $this->session->getItemBaseDiscount();
            $order = $observer->getOrder();
            $orderDiscount = abs($order->getDiscountAmount());
            $orderBaseDiscount = abs($order->getBaseDiscountAmount());
            $order->setCustomDiscount($customDiscount);
            $order->setDiscountAmount($customDiscount);
            $order->setBaseDiscountAmount($customBaseDiscount);
            $order->setBaseGrandTotal($order->getBaseGrandTotal()+$customBaseDiscount+$orderBaseDiscount);
            $order->setGrandTotal($order->getGrandTotal()+$customDiscount+$orderDiscount);
            $order->setDiscountDescription($code);
            $shippingAddress = $order->getShippingAddress();
            if ($shippingAddress) {
                $orderAddress = $order->getShippingAddress();
                $orderAddress->setCustomDiscount($customDiscount);
            }
            $orderBillingAddress = $order->getBillingAddress();
            $orderBillingAddress->setCustomDiscount($customDiscount);
            foreach ($order->getAllItems() as $item) {
                if (isset($discounts[$item->getQuoteItemId()]) && $discounts[$item->getQuoteItemId()]) {
                    $discount = $discounts[$item->getQuoteItemId()];
                    $baseDiscount = $baseDiscounts[$item->getQuoteItemId()];
                    $item->setDiscountAmount($discount);
                    $item->setBaseDiscountAmount($baseDiscount);
                }
            }
            $order->save();
        }
    }
}
