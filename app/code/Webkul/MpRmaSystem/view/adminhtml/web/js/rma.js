/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpRmaSystem
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
    "jquery",
    "Magento_Ui/js/modal/alert",
    "jquery/ui",
], function ($, alertBox) {
    'use strict';
    $.widget('mprma.rma', {
        options: {},
        _create: function () {
            var self = this;
            var totalPrice = self.options.totalPrice;
            var totalPriceWithCurrency = self.options.totalPriceWithCurrency;
            var errorMsg = self.options.errorMsg;
            var warningLable = self.options.warningLable;
            $(document).ready(function () {
                $(".wk-refund-amount").html(totalPriceWithCurrency);
                $(".wk-refundable-amount").html(totalPriceWithCurrency);
                $(".wk-refund-block").removeClass("wk-display-none");
                $("#payment_type").change(function (e) {
                    var val = $(this).val();
                    if (val == 1) {
                        $(".wk-partial-amount").hide();
                        $("#partial_amount").removeClass("required-entry");
                    } else {
                        $(".wk-partial-amount").show();
                        $("#partial_amount").addClass("required-entry");
                    }
                });

                $("#wk_rma_conversation_form").submit(function(e){
                    var form = $("#wk_rma_conversation_form");
                    if($('#wk_rma_conversation_form').valid()){
                        if (form.data('submitted') === true) {
                            e.preventDefault();
                          } else {
                            form.data('submitted', true);
                          }
                        $('body').trigger('processStart');
                    }

                });

                $(".wk-refund").click(function (e) {
                    if ($('#wk_rma_refund_form').valid()) {
                        var price = $("#partial_amount").val();
                        if (price > totalPrice) {
                            alertBox({
                                title: warningLable,
                                content: "<div class='wk-mprma-warning-content'>"+errorMsg+"</div>",
                                actions: {
                                    always: function (){}
                                }
                            });
                            return false;
                        }
                    }
                });
                $("#wk-refund-online").click(function() {
                    $(".payment_status").val(0);
                });
            });
        }
    });
    return $.mprma.rma;
});
