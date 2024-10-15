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

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * QuoteSubmitObserver is used to add details of quote
 */
class QuoteSubmitObserver implements ObserverInterface
{
    
    /**
     * @var $quoteItems
     */
    private $quoteItems = [];
    
    /**
     * @var $quote
     */
    private $quote = null;
    
    /**
     * @var $order
     */
    private $order = null;
  
    /**
     * @param \Webkul\Mpquotesystem\Helper\Data $helper
     * @param Json                              $serializer
     */
    public function __construct(
        \Webkul\Mpquotesystem\Helper\Data $helper,
        Json $serializer
    ) {
        $this->helper   = $helper;
        $this->serializer = $serializer;
    }

    /**
     * Execute function to submit quote
     *
     * @param EventObserver $observer
     *
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        try {
            $this->quote = $observer->getQuote();
            $this->order = $observer->getOrder();

            if ($this->order->getRemoteIp()) {
                foreach ($this->order->getItems() as $orderItem) {
                    $quoteItem = $this->getQuoteItemById($orderItem->getQuoteItemId());
                    if ($quoteItem && $additionalOptionsQuote = $quoteItem->getOptionByCode('additional_options')) {
                        if ($additionalOptionsOrder = $orderItem->getProductOptionByCode('additional_options')) {
                            $additionalOptions = $this->mergeArrays($additionalOptionsQuote, $additionalOptionsOrder);
                        } else {
                            $additionalOptions = $additionalOptionsQuote;
                        }
                        if (!empty($additionalOptions) > 0) {
                            $options = $orderItem->getProductOptions();
                            $options['additional_options'] = $this->serializer
                            ->unserialize($additionalOptions->getValue());
                            $orderItem->setProductOptions($options);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }

    /**
     * Get Quote Item By Id
     *
     * @param int $id
     *
     * @return void
     */
    private function getQuoteItemById($id)
    {
        if (empty($this->quoteItems)) {
            foreach ($this->quote->getItems() as $item) {
                $this->quoteItems[$item->getId()] = $item;
            }
        }
        if (array_key_exists($id, $this->quoteItems)) {
            return $this->quoteItems[$id];
        }
        return null;
    }

    /**
     * Merge Arrays
     *
     * @param array $additionalOptionsQuote
     * @param array $additionalOptionsOrder
     *
     * @return array
     */
    public function mergeArrays($additionalOptionsQuote, $additionalOptionsOrder)
    {
        return array_merge($additionalOptionsQuote, $additionalOptionsOrder);
    }
}
