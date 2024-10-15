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
    "jquery/ui",
], function ($, confirmation, alert) {
    'use strict';
    $.widget('mprma.cancel', {
        options: {},
        _create: function () {
            var self = this;
            var confirmationLabel = self.options.confirmationLabel;
            var cancelRmaLabel = self.options.cancelRmaLabel;
            $(document).ready(function () {
                $(".wk-cancel-rma").click(function (e) {
                    var url = $(this).attr("href");
                    confirmation({
                        title: confirmationLabel,
                        content: "<div class='wk-mprma-warning-content'>"+cancelRmaLabel+"</div>",
                        actions: {
                            confirm: function () {
                                window.location.href = url;
                            },
                            cancel: function () {
                                return false;
                            },
                            always: function () {
                                return false;
                            }
                        }
                    });
                    return false;
                });
            });
        }
    });
    return $.mprma.cancel;
});
