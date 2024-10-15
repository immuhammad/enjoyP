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
use Magento\Framework\Event\Observer;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;

class SalesOrderCreditmemoSaveAfter implements ObserverInterface
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
     * Class constructor
     *
     * @param \Magento\Quote\Model\QuoteFactory $quoteModel
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Webkul\MpServiceFee\Helper\Servicehelper $helper
     */
    public function __construct(
        \Magento\Quote\Model\QuoteFactory $quoteModel,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Webkul\MpServiceFee\Helper\Servicehelper $helper
    ) {
        $this->_quote = $quoteModel;
        $this->_helper = $helper;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Execute
     *
     * @param Observer $observer
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order\Creditmemo $creditmemo */
        $creditmemo = $observer->getEvent()->getCreditmemo();
        /** @var \Magento\Sales\Model\Order $order */
        $order = $creditmemo->getOrder();

        $currentServiceFees = $order->getCurrentCurrencyServiceFees();

        $orderRefund = $order->getTotalRefunded();

        $orderTotalPaid = $order->getTotalPaid();

        $totalRefunded = $orderTotalPaid - $this->priceCurrency->round($orderRefund);

        if (abs(($totalRefunded - $currentServiceFees)) < .0001) {
            $order->setTotalRefunded($orderTotalPaid);
            $order->save();
        }
    }
}
