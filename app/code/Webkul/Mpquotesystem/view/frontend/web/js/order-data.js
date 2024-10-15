/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Mpquotesystem
 * @author Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
 define([
    "jquery",
    'mage/translate'
], function ($,$t) {
    'use strict';

    return function (config) {
        $(document).ready(function() {
            $("td.col.name > dl.item-options > dt").each(function( index ) {
                var label = $(this).text();
                if (label == 'Attachment') {
                    var anchorTagString = $(this).next("dl.item-options > dd").text();
                    if (anchorTagString.includes("href")) {
                        $(this).next("dl.item-options > dd").empty();
                        $(this).empty();
                        $(this).next("dl.item-options > dd").append(anchorTagString);
                    }
                }
            });
        });
    };
});
