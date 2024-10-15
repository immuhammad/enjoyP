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

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class RemoveBlockForDiscount implements ObserverInterface
{
    /**
     * @var Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    protected $quoteHelper;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Webkul\Mpquotesystem\Helper\Data                  $quoteHelper
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Webkul\Mpquotesystem\Helper\Data $quoteHelper
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->quoteHelper = $quoteHelper;
    }

    /**
     * Execute function to remove block for discount
     *
     * @param Observer $observer
     *
     * @return object
     */
    public function execute(Observer $observer)
    {
        try {
        /**
         * @var \Magento\Framework\View\Layout $layout
         */
            $layout = $observer->getLayout();
            $block = $layout->getBlock('checkout.cart.coupon');

            if ($block) {
                if (!$this->quoteHelper->getDiscountEnable() && $this->quoteHelper->checkQuoteProductIsInCart()) {
                    $layout->unsetElement('checkout.cart.coupon');
                }
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
        return $this;
    }
}
