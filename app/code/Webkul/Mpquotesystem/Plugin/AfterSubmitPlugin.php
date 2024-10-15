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

namespace Webkul\Mpquotesystem\Plugin;

use Webkul\Mpquotesystem\Helper\Data;
use \Magento\Framework\Message\ManagerInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\Quote\ItemFactory;

class AfterSubmitPlugin
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
     * @var Magento\Sales\Model\QuoteFactory
     */
    protected $_salesquote;

    /**
     * @var Magento\Quote\Model\Quote\ItemFactory
     */
    protected $_salesquoteItem;

    /**
     * @param Data $helper
     * @param ManagerInterface $messageManager
     * @param QuoteFactory $salesQuote
     * @param ItemFactory $salesquoteItem
     * @param \Psr\Log\LoggerInterface $logger
     */

    public function __construct(
        Data $helper,
        ManagerInterface $messageManager,
        QuoteFactory $salesQuote,
        ItemFactory $salesquoteItem,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_helper = $helper;
        $this->_messageManager = $messageManager;
        $this->_salesquote = $salesQuote;
        $this->_salesquoteItem = $salesquoteItem;
    }
    
    /**
     * After submit plugin
     *
     * @param \Magento\Quote\Model\QuoteManagement $subject
     * @param object $result
     * @return object
     */
    public function afterSubmit(
        \Magento\Quote\Model\QuoteManagement $subject,
        $result
    ) {
        try {
            $order = $result;
            if (!empty($order)) {
                $incrementId = $order->getIncrementId();
                $quoteId = 0;
                $store = $this->_helper->getStore();
                $salesQuoteCollection = $this->_salesquote->create()->setStore($store)
                ->getCollection()
                ->addFieldToFilter('reserved_order_id', $incrementId);

                if ($salesQuoteCollection->getSize()) {
                    foreach ($salesQuoteCollection as $salesQuote) {
                        $quoteId = $salesQuote->getEntityId();
                    }
                }

                $this->salesQuote($quoteId, $store, $order->getId());
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
        return $result;
    }

    /**
     * SalesQuote
     *
     * @param int $quoteId
     * @param int $store
     * @param int $orderId
     *
     * @return void
     */
    public function salesQuote($quoteId, $store, $orderId)
    {
        if ($quoteId != 0) {
            $quoteModel = $this->_salesquote->create()->load($quoteId);
            $quoteItemModel = $this->_salesquoteItem->create()
                ->setStore($store)
                ->getCollection()
                ->setQuote($quoteModel);
            foreach ($quoteItemModel as $quoteItem) {
                $mpQuote = $this->_helper->getWkQuoteModel()
                    ->getCollection()
                    ->addFieldToFilter('item_id', $quoteItem->getItemId());
                if (!empty($mpQuote)) {
                    foreach ($mpQuote as $quote) {
                        if ($quote->getEntityId() != 0) {
                            $quote = $this->_helper->loadData(
                                $this->_helper->getWkQuoteModel(),
                                $quote->getEntityId()
                            );
                            $quote->setStatus(\Webkul\Mpquotesystem\Model\Quotes::STATUS_SOLD);
                            $quote->setOrderId($orderId);
                            $this->_helper->commitMethod($quote);
                        }
                    }
                }
            }
        }
    }
}
