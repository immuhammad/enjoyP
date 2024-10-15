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
use Magento\Checkout\Model\Session as CheckoutSession;

class CheckoutCartPredispatch implements ObserverInterface
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
     *
     * @param Data             $helper
     * @param CheckoutSession  $checkoutSession
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Data $helper,
        CheckoutSession $checkoutSession,
        ManagerInterface $messageManager
    ) {
        $this->_helper = $helper;
        $this->_checkoutSession = $checkoutSession;
        $this->_messageManager = $messageManager;
    }
    
    /**
     * Cart product add after event
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $helper = $this->_helper;
            $session = $helper->getCheckoutSession();
            foreach ($session->getQuote()->getAllItems() as $item) {
                if ($item->getParentItemId() === null && $item->getItemId() > 0) {
                    $price = 0;
                    $quoteId = 0;
                    $quoteQty = 0;
                    $quoteCollection = $helper->getWkQuoteModel()->getCollection()
                    ->addFieldToFilter("item_id", $item->getItemId());
                    if ($quoteCollection->getSize()) {
                        if ($helper->checkAndUpdateForDiscount($item)) {
                            $item->setNoDiscount(1);
                        } else {
                            $item->setNoDiscount(0);
                        }
                        $helper->commitMethod($item);
                    }
                }
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
        return $this;
    }
}
