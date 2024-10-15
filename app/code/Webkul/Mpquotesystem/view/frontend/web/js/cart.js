/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpquotesystem
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint jquery:true*/
require(
    [
    "jquery",
    'mage/translate'
    ], function ($, $t) {
        $(document).ready(function() {
            $("tr.item-info > td.item > div.product-item-details > dl.item-options > dt").each(function( index ) {
                var label = $(this).text();
                if (label == 'Attachment') {
                    var anchorTagString = $(this).next("dl.item-options > dd").text();
                    if (anchorTagString.includes("href")) {
                        $(this).next("dl.item-options > dd").empty();
                        $(this).next("dl.item-options > dd").append(anchorTagString);
                    }
                }
            });
            if ($(".quote-pro-in-cart").length) {
                $("a[class='action multicheckout']").hide();
            }
        });
    });