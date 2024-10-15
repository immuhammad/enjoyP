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
        var minQtyVal = config.minQtyVal;
        function setDefaultQty() {
            if ($('input[name="product[min_quote_qty]"]').val()==='') {
                $('input[name="product[min_quote_qty]"]').val(minQtyVal)
            }
        }
        $(document).ajaxStop(function() {
            setDefaultQty();
            if (!parseInt($('select[name="product[quote_status]"] option:selected').val())) {
                $('input[name="product[min_quote_qty]"]').prop('disabled', true);
            }
            $('input[name="product[min_quote_qty]"]').focus(function() {
                $('#quoteminqty-error-msg').remove();
            }).blur(function() {
                var minQty = $('input[name="product[min_quote_qty]"]').val();
                if ((minQty % 1) != 0) {
                    var str = '<span id="quoteminqty-error-msg">'+$t('Enter a valid minimum quantity')+'</span>';
                    $('input[name="product[min_quote_qty]"]').after(str);
                    $('#quoteminqty-error-msg').css("color", "red");
                    $('input[name="product[min_quote_qty]"]').val('');
                }
            });
            $('select[name="product[quote_status]"]').change(function(){
                if (!parseInt($('select[name="product[quote_status]"] option:selected').val())) {
                    $('input[name="product[min_quote_qty]"]').prop('disabled', true);
                } else {
                    $('input[name="product[min_quote_qty]"]').prop('disabled', false);
                    setDefaultQty();
                }
            })
        });
    };
});
