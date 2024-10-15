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

class SalesQuoteAddItem implements ObserverInterface
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
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * @param Data $helper
     * @param ManagerInterface $messageManager
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        Data $helper,
        ManagerInterface $messageManager,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->_helper = $helper;
        $this->_messageManager = $messageManager;
        $this->_request = $request;
    }

    /**
     * Quote Item qty Set after
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $quoteItem = $observer->getQuoteItem();
            $params = $this->_request->getParams();
            $helper = $this->_helper;
            $session = $helper->getCheckoutSession();
            $quoteId = '';
            if (array_key_exists('quote_id', $params)) {
                $quoteId = $params['quote_id'];
            }
            if ($quoteId == "") {
                return $this;
            }
                $itemProductId = $quoteItem->getProductId();
                $quote = $helper->getWkQuoteModel()->load($quoteId);
                $quoteProductId = $quote->getProductId();
                $baseCurrencyCode = $helper->getBaseCurrencyCode();
                $currentCurrencyCode = $helper->getCurrentCurrencyCode();
                $quoteCurrencyCode = $quote->getQuoteCurrencyCode();
            if ($itemProductId == $quoteProductId) {
                $price = $quote->getQuotePrice();
                $priceOne = $helper->getwkconvertCurrency(
                    $baseCurrencyCode,
                    $currentCurrencyCode,
                    $price,
                    $quoteCurrencyCode
                );
                $quoteQty = $quote->getQuoteQty();
                $quoteItem->setCustomPrice($priceOne);
                $quoteItem->setOriginalCustomPrice($priceOne);
                $quoteItem->setRowTotal($priceOne * $quoteQty);
                if ($helper->checkAndUpdateForDiscount($quoteItem)) {
                    $quoteItem->setNoDiscount(1);
                } else {
                    $quoteItem->setNoDiscount(0);
                }
                $quote->setItemId($quoteItem->getItemId())->save();
            } else {
                $quoteBundleOption = $helper->convertStringAccToVersion($quote->getBundleOption(), 'decode');
                if (!empty($quoteBundleOption)) {
                    $bundleProductConfiguredPrice = $quote->getProductPrice();
                    foreach ($quoteBundleOption['bundle_option'] as $optionId => $optionValue) {
                        if ($quoteBundleOption['bundle_option_product'][$optionId] == $itemProductId) {
                            $currentOptionProductPrice = $quoteBundleOption['bundle_option_price'][$optionId];
                            $currentTotalPrice = $currentOptionProductPrice;
                            $calculatePercent = ($currentTotalPrice * 100)/$bundleProductConfiguredPrice;
                            $price = (($quote->getQuotePrice() * $calculatePercent)/100)/
                            $quoteBundleOption['bundle_option_qty'][$optionId];
                            $quoteCurrencyCode = $quote->getQuoteCurrencyCode();
                            $priceOne = $helper->getwkconvertCurrency(
                                $baseCurrencyCode,
                                $currentCurrencyCode,
                                $price,
                                $quoteCurrencyCode
                            );
                            $quoteItem->setCustomPrice($priceOne);
                            $quoteItem->setOriginalCustomPrice($priceOne);
                            $quoteItem->setRowTotal($priceOne * $quoteBundleOption['bundle_option_qty'][$optionId]);
                            $this->setDiscount($helper, $quoteItem);
                            $quoteItem->getProduct()->setIsSuperMode(true);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
        return $this;
    }

    /**
     * SetDiscount
     *
     * @param object $helper
     * @param object $quoteItem
     * @return void
     */
    public function setDiscount($helper, $quoteItem)
    {
        if ($helper->checkAndUpdateForDiscount($quoteItem)) {
            $quoteItem->setNoDiscount(1);
        } else {
            $quoteItem->setNoDiscount(0);
        }
    }
}
