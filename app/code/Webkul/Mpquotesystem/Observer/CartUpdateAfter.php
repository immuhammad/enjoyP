<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpquotesystem
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpquotesystem\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\Mpquotesystem\Helper\Data;
use \Magento\Framework\Message\ManagerInterface;

class CartUpdateAfter implements ObserverInterface
{
    /**
     * @var \Webkul\Mpquotesystem\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @param Data                         $helper
     * @param ManagerInterface             $messageManager
     * @param \Magento\Checkout\Model\Cart $cart
     */
    public function __construct(
        Data $helper,
        ManagerInterface $messageManager,
        \Magento\Checkout\Model\Cart $cart
    ) {
        $this->_helper = $helper;
        $this->_messageManager = $messageManager;
        $this->_cart = $cart;
    }
    
    /**
     * Cart update after event
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $helper = $this->_helper;
            $session = $helper->getCheckoutSession();
            $flag = 0;
            foreach ($session->getQuote()->getAllItems() as $item) {
                if ($item->getParentItemId() === null && $item->getItemId() > 0) {
                    $price = 0;
                    $quoteId = 0;
                    $quoteQty = 0;
                    $quoteCollection = $helper->getWkQuoteModel()->getCollection()
                        ->addFieldToFilter("item_id", $item->getItemId());
                    $baseCurrencyCode = $helper->getBaseCurrencyCode();
                    $quoteCurrencyCode = $currentCurrencyCode = $helper->getCurrentCurrencyCode();
                    if ($quoteCollection->getSize()) {
                        foreach ($quoteCollection as $quote) {
                            $price = $quote->getQuotePrice();
                            $quoteId = $quote->getEntityId();
                            $quoteQty = $quote->getQuoteQty();
                            $quoteCurrencyCode = $quote->getQuoteCurrencyCode();
                        }
                        $priceOne = $helper->getwkconvertCurrency(
                            $baseCurrencyCode,
                            $currentCurrencyCode,
                            $price,
                            $quoteCurrencyCode
                        );
                    }
                    
                    if ($quoteId != 0 && ($priceOne!=$item->getCustomPrice() || $quoteQty!=$item->getQty())) {
                        $flag = 1;
                        $item->setCustomPrice($priceOne);
                        $item->setOriginalCustomPrice($priceOne);
                        $item->setQty($quoteQty);
                        $item->setRowTotal($priceOne * $quoteQty);
                        $item->getProduct()->setIsSuperMode(true);
                        if ($helper->checkAndUpdateForDiscount($item)) {
                            $item->setNoDiscount(1);
                        } else {
                            $item->setNoDiscount(0);
                        }
                        $this->_helper->commitMethod($item);
                        $this->_messageManager->addNotice(
                            __(
                                "You can't edit quote items"
                            )
                        );
                    }
                }
            }
            if ($flag) {
                $this->_cart->save();
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
        return $this;
    }
}
