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
    "Magento_Ui/js/modal/confirm",
    "Magento_Ui/js/modal/alert",
    "mage/calendar",
    "jquery/ui",
    'mage/translate',
    "mage/template",
    "mage/mage",
], function ($, confirmation, alertBox) {
    'use strict';
    $.widget('mprma.allRma', {
        options: {},
        _create: function () {
            var self = this;
            var type = self.options.type;
            var filterUrl = self.options.filterUrl;
            var sortingUrl = self.options.sortingUrl;
            $(".wk-date-filter-box input").calendar({
                dateFormat:'Y-mm-dd',
            });

            $(document).keypress(function (event) {
                if (event.keyCode == 13) {
                    applyFilter();
                }
            });

            $(document).ready(function () {
                $("body").on("click", ".wk-apply-filter-btn", function () {
                    applyFilter();
                });
                $("body").on("click", ".wk-sorting-col", function () {
                    var col = $(this).attr("data-col");
                    if ($(this).parent().parent().hasClass("wk-desc-order")) {
                        $(this).parent().parent().removeClass("wk-desc-order");
                        $(this).parent().parent().addClass("wk-asc-order");
                        var sortOrder = "asc";
                    } else {
                        $(this).parent().parent().removeClass("wk-asc-order");
                        $(this).parent().parent().addClass("wk-desc-order");
                        var sortOrder = "desc";
                    }

                    $(this).parent().parent().removeClass("wk-filtered-order-ref");
                    $(this).parent().parent().removeClass("wk-filtered-date");
                    $(this).parent().parent().removeClass("wk-filtered-rma-id");
                    $(this).parent().parent().removeClass("wk-filtered-rma-customer");
                    if (col == "wk_order_ref") {
                        $(this).parent().parent().addClass("wk-filtered-order-ref");
                    } else if (col == "wk_date") {
                        $(this).parent().parent().addClass("wk-filtered-date");
                    } else if (col == "wk_customer") {
                        $(this).parent().parent().addClass("wk-filtered-rma-customer");
                    } else {
                        $(this).parent().parent().addClass("wk-filtered-rma-id");
                    }
                    applySorting(col, sortOrder);
                });
            });

            function applyFilter()
            {
                var rmaId = $("#wk-filter-rma-id").val();
                var orderRef = $("#wk-filter-order-ref").val();
                var status = $("#wk-filter-rma-status").val();
                var dateFrom = $("#wk-filter-date-from").val();
                var dateTo = $("#wk-filter-date-to").val();
                var customer = $("#wk-filter-customer").val();

                showLoader();
                $.ajax({
                    type: 'post',
                    url: filterUrl,
                    async: true,
                    dataType: 'json',
                    data : {
                                rma_id : rmaId,
                                order_ref : orderRef,
                                status : status,
                                from : dateFrom,
                                to : dateTo,
                                customer : customer,
                                type : type
                            },
                    success:function (data) {
                        location.reload();
                        hideLoader();
                    }
                });
            }

            function applySorting(col, sortOrder)
            {
                showLoader();
                $.ajax({
                    type: 'post',
                    url: sortingUrl,
                    async: true,
                    dataType: 'json',
                    data : {
                                sort_order : sortOrder,
                                sort_col : col,
                                type : type
                            },
                    success:function (data) {
                        location.reload();
                        hideLoader();
                    }
                });
            }

            function showLoader()
            {
                $(".wk-loading-mask").removeClass("wk-display-none");
            }

            function hideLoader()
            {
                $(".wk-loading-mask").addClass("wk-display-none");
            }
        }
    });
    return $.mprma.allRma;
});
