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

define(
    [
    "jquery",
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    "jquery/ui"
    ],
    function ($, $t, alert) {
        'use strict';
        $.widget(
            'mage.mpPaypalRedirect',
            {
                _create: function () {
                    var self = this;
                    setTimeout(redirect, 5000);
                    function redirect()
                    {
                        document.getElementById("paypal_standard_checkout").submit();
                    }

                }
            }
        );
        return $.mage.mpPaypalRedirect;
    }
);
