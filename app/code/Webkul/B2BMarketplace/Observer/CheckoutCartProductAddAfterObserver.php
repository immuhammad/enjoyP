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

/**
 * Webkul B2BMarketplace CheckoutCartProductAddAfterObserver Observer.
 */
class CheckoutCartProductAddAfterObserver implements ObserverInterface
{
    /**
     * Checkout cart product add event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($observer->getQuoteItem()) {
            $item = $observer->getQuoteItem();
            $item->setIsLastAddedItem(true);
        }
    }
}
