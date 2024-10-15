/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpquotesystem
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

require(
    [
    'jquery'
    ], function (jQuery) {
        'use strict';
        jQuery("body").on(
            'change',"select[name='product[quote_status]']", function () {
                var quoteStatusVal = jQuery(this).val();
                if (parseInt(quoteStatusVal) == 1) {
                    jQuery('body').find("input[name='product[min_quote_qty]']").prop('enabled', true);
                } else {
                    jQuery('body').find("input[name='product[min_quote_qty]']").prop('disabled', true);
                }
            }
        );
    }
);

