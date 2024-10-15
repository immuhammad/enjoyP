/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Stripe
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

 define([
    'jquery',
    'mage/template',
    'prototype',
], function(jQuery, mageTemplate){
    return function (config) {
        jQuery('#webhooks').click(function () {
            jQuery('body').trigger('processStart');
            var self = config;
            new Ajax.Request(self.url, {
                loaderArea:     false,
                asynchronous:   true,
                onSuccess: function(transport) {
                    jQuery('body').trigger('processStop');
                    var response = JSON.parse(transport.responseText);
                    location.reload();
                }
            });
        });
    }
});