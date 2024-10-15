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

class Quoteitemqtysetafter implements ObserverInterface
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
     * @param Data                                $helper
     * @param ManagerInterface                    $messageManager
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
            $params = $this->_request->getParams();
            $helper = $this->_helper;
            $session = $helper->getCheckoutSession();
            $quoteId = '';
            if (array_key_exists('quote_id', $params)) {
                $quoteId = $params['quote_id'];
            }
            if ($quoteId != "") {
                $quote = $helper->getWkQuoteModel()->load($quoteId);
                $lastItem = new \Magento\Framework\DataObject;
                foreach ($session->getQuote()->getAllItems() as $item) {
                    if ($item->getParentItemId() === null && $item->getItemId() > 0) {
                        $lastItemId = $item->getItemId();
                    }
                }
                if ($lastItemId!=0) {
                    $quote->setItemId($lastItemId)->save();
                }
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
        return $this;
    }
}
