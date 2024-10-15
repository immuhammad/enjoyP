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

class CheckoutIndexPredispatch implements ObserverInterface
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
     * @param Data $helper
     * @param CheckoutSession $checkoutSession
     * @param ManagerInterface $messageManager
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Store\Model\StoreManagerInterface $storemanager
     */
    public function __construct(
        Data $helper,
        CheckoutSession $checkoutSession,
        ManagerInterface $messageManager,
        \Magento\Quote\Model\Quote\Item $quoteItem,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\UrlInterface $url,
        \Magento\Store\Model\StoreManagerInterface $storemanager
    ) {
        $this->_helper = $helper;
        $this->_checkoutSession = $checkoutSession;
        $this->_messageManager = $messageManager;
        $this->_quoteItem = $quoteItem;
        $this->_cart = $cart;
        $this->_quoteRepository = $quoteRepository;
        $this->_url = $url;
        $this->_storemanager = $storemanager;
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
            $cartCount = $session->getQuote()->getAllItems();
            foreach ($session->getQuote()->getAllItems() as $item) {
                if ($item->getParentItemId() === null && $item->getItemId() > 0) {
                    $price = 0;
                    $quoteId = 0;
                    $quoteQty = 0;
                    $quoteCollection = $helper->getWkQuoteModel()->getCollection()
                    ->addFieldToFilter("item_id", $item->getItemId());
                    if ($quoteCollection->getSize()) {
                        $flag = $this->checkIfStatusIsDeclined($quoteCollection, $item);

                        if (($flag > 0) && ($flag == $cartCount)) {
                            $url= $this->_storemanager->getStore()->getBaseUrl();
                            return $observer->getControllerAction()
                                    ->getResponse()
                                    ->setRedirect($url);
                        } elseif (($flag > 0) && ($flag != $cartCount)) {
                            $url = $this->_url->getUrl('checkout/cart/index');
                            return $observer->getControllerAction()
                                    ->getResponse()
                                    ->setRedirect($url);
                        }

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

    /**
     * Check status for quote product in cart
     *
     * @param collection $quoteCollection
     * @param object $item
     * @return int
     */
    protected function checkIfStatusIsDeclined($quoteCollection, $item)
    {
        $flag = 0;
        foreach ($quoteCollection as $data) {
            if ($data->getStatus() === '3') {
                $quoteId = $item->getQuoteId();
                $quote = $this->_quoteRepository->getActive($quoteId);

                foreach ($quote->getAllVisibleItems() as $quoteItem) {
                    if (($quoteItem->getItemId()) === $data->getItemId()) {
                        $this->_cart->removeItem($quoteItem->getItemId())->save();
                        $quote->deleteItem($quoteItem)->save();
                        $flag++;
                        $this->_messageManager->addNotice(
                            __(
                                'Status for Quote Product "%1" is declined',
                                $data->getProductName()
                            )
                        );
                    }
                }

                $this->_quoteRepository->save($quote);
            }
        }
        return $flag;
    }
}
