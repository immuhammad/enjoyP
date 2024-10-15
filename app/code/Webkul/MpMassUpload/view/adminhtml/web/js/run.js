/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMassUpload
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
"jquery",
'Magento_Ui/js/modal/alert',
"jquery/ui",
], function ($, alert) {
    'use strict';
    $.widget('mpmassupload.run', {
        options: {},
        _create: function () {
            var self = this;
            $(document).ready(function () {
                var defaultUrl = self.options.defaultUrl;
                var btnHtml = self.options.btnHtml;
                var sellerWarning = self.options.sellerWarning;
                var profileWarning = self.options.profileWarning;
                $("#profile").after(btnHtml);
                $('#run-profile').on('click', function () {
                    var sellerId = $("#seller_id").val();
                    var id = $("#profile").val();
                    if (sellerId == "") {
                        alert({
                            title: 'Warning',
                            content: sellerWarning,
                            actions: {
                                always: function (){}
                            }
                        });
                    } else if (id == "") {
                        alert({
                            title: 'Warning',
                            content: profileWarning,
                            actions: {
                                always: function (){}
                            }
                        });
                    } else {
                        var url = defaultUrl+"id/"+id+"/seller_id/"+sellerId;
                        window.open(url);
                    }
                });
                
            });
        }
    });
    return $.mpmassupload.run;
});