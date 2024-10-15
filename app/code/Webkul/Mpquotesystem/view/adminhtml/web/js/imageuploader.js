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
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    "mage/mage"
], function ($,$t,alert) {
    'use strict';

    return function (config) {
        var invalidExtension = $t('Invalid Image Extension. Allowed extensions are ');
        var dataForm = $('#form-quote');
        dataForm.mage('validation', {});
        $("#save_butn").on("click",function(){
            if($("#form-quote").valid()!=false){
                jQuery("#save_butn").attr("disabled","disabled");
                jQuery("#form-quote").submit();
            }
        });
        $('input[name="quote_attachment"]').on('change', function () {
            var allowedTypes = $(this).attr("data-allowed-types").split(",");
            var attachVal = $(this).val();
            var splitType = attachVal.substring(attachVal.lastIndexOf(".") + 1, attachVal.length);
            if (allowedTypes.indexOf(splitType) < 0) {
                var thisEle = $(this);
                alert({
                    title: 'Attention!',
                    content: invalidExtension+$(this).attr("data-allowed-types"),
                    actions: {
                        always: function () {
                            thisEle.val('');
                        }
                    }
                });
            }
        });
    };
});
