/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
 /*jshint jquery:true*/
 define([
    "jquery"
], function ($) {
    'use strict';
    $.widget('mage.affiliateBanner', {
        _create: function () {
            $('body').on('click', '#save', function () {
                if ($("#edit_form").valid()!==false) {
                    $('#save').trigger('processStart');
                }
            });
        }
    });
    return $.mage.affiliateBanner;
});
