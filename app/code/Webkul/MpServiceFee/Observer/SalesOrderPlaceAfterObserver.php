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
class SalesOrderPlaceAfterObserver implements ObserverInterface
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
     * Sales order place after event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $order = $observer->getOrder();
            if ($order) {
                $quote = $this->_quote->create()->load($order->getQuoteId());
                $order->setServiceFees($quote->getServiceFees());
                $order->setCurrentCurrencyServiceFees($quote->getCurrentCurrencyServiceFees());
                $order->setServiceTitle($this->_helper->activeServiceNames());
                $order->save();
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotDeleteException(__("Fees cannot be added in the order"));
        }
    }
}
