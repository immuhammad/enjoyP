/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_B2BMarketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

var config = {
    map: {
        '*': {
            productAjaxSearch: 'Webkul_B2BMarketplace/js/product-search',
            requestQuoteProduct: 'Webkul_B2BMarketplace/js/request-quote-product'
        }
    },
    config: {
        mixins: {
            'Magento_ConfigurableProduct/js/configurable': {
                'Webkul_B2BMarketplace/js/configurable-mixin': true
            },
            'Magento_Swatches/js/swatch-renderer': {
                'Webkul_B2BMarketplace/js/swatch-renderer-mixin':true
            }
        }
    }
};
