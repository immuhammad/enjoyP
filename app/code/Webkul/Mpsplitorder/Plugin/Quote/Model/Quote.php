<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mpsplitorder
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mpsplitorder\Plugin\Quote\Model;

class Quote
{
    /**
     * Initialize dependencies.
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
    }

    public function afterValidateMinimumAmount(
        \Magento\Quote\Model\Quote $subject,
        $result
    ) {
        if (!$result) {
            $quote = $this->checkoutSession->getParentQuote();
            if (!$quote) {
                return $result;
            }
            $storeId = $quote->getStoreId();
            $includeDiscount = $this->_scopeConfig->getValue(
                'sales/minimum_order/include_discount_amount',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
            $amount = $this->_scopeConfig->getValue(
                'sales/minimum_order/amount',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
            $taxInclude = $this->_scopeConfig->getValue(
                'sales/minimum_order/tax_including',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
            
            $taxes = $taxInclude ? $quote->getBaseTaxAmount() : 0;
            return $includeDiscount ?
                ($quote->getBaseSubtotalWithDiscount() + $taxes >= $amount) :
                ($quote->getBaseSubtotal() + $taxes >= $amount);
        }
        return $result;
    }
}
