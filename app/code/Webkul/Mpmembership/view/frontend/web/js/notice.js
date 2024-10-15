/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpmembership
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

/*jshint jquery:true*/

define([
    "jquery",
    "jquery/ui"
], function ($) {
    'use strict';
    $.widget('mage.membershipNotice', {
        _create: function () {
            var self = this;

            $('#form-customer-product-new input,#form-customer-product-new select,#form-customer-product-new button')
                .attr('disabled','disabled');
        }
    });
    return $.mage.membershipNotice;
});
