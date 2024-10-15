/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpServiceFee
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
    $.widget('mage.wkservicerequire', {
        options: {
            confirmMsg: ('divElement is removed.')
        },
        _create: function () {
            $('button[title="Add Selected Product(s) to Order"], button[title="Update Items and Quantities"]').click(function(){
                var callback = function () {
                    $('#order-shipping-method-summary > a.action-default').trigger('click');
                };
                $("body").on('processStop', callback);
            })
        }
    });
    return $.mage.addButton;
});
