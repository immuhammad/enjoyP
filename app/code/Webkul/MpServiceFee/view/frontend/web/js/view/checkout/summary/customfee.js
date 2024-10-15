/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpServiceFee
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

/*global alert*/
define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/model/cart/totals-processor/default',
        'Magento_Checkout/js/model/cart/cache'
    ],
    function (Component, quote, priceUtils, totals, defaultTotal, cartCache) {
        "use strict";
        return Component.extend({
            defaults: {
                counter: 0,
                isFullTaxSummaryDisplayed: window.checkoutConfig.isFullTaxSummaryDisplayed || false,
                template: 'Webkul_MpServiceFee/checkout/summary/customfee'
            },
            totals: quote.getTotals(),
            isTaxDisplayedInGrandTotal: window.checkoutConfig.includeTaxInGrandTotal || false,
            isDisplayed: function () {
                return this.isFullMode();
            },
            getValue: function () {
                var price = 0;
                if (!this.counter) {
                    cartCache.set('totals',null);
                    defaultTotal.estimateTotals();
                }
                this.counter++;
                if (this.totals()) {
                    price = totals.getSegment('customfee').value;
                }
                return this.getFormattedPrice(price);
            },
            getTitle: function () {
                var title = "";
                if (this.totals()) {
                    this.title = totals.getSegment('customfee').title;
                }
                return this.title;
            },
            getBaseValue: function () {
                var price = 0;
                if (this.totals()) {
                    price = this.totals().base_customfee;
                }
                return priceUtils.formatPrice(price, quote.getBasePriceFormat());
            }
        });
    }
);
