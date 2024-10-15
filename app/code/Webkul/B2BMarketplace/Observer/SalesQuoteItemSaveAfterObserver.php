<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_B2BMarketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\B2BMarketplace\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session as CheckoutSession;

/**
 * Webkul B2BMarketplace SalesQuoteItemSaveAfterObserver Observer.
 */
class SalesQuoteItemSaveAfterObserver implements ObserverInterface
{
    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;

    /**
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        CheckoutSession $checkoutSession
    ) {
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * sale quote item add after event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quoteItem = $observer->getItem();
        if ($quoteItem->getIsLastAddedItem()) {
            $this->_checkoutSession->setLastAddedItem($quoteItem->getId());
            $this->_checkoutSession->setLastAddedItemPrice($quoteItem->getRowTotal());
            $this->_checkoutSession->setLastAddedItemQty($quoteItem->getQty());
        }
    }
}
