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
    $.widget('mage.mpProductEdit', {
        _create: function () {
            var self = this;

            $('form#edit-product')
                .find('select[name="product[visibility]"]')
                .val(self.options.visibility)
                .attr('disabled','disabled');
        }
    });
    return $.mage.mpProductEdit;
});
