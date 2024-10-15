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
            'mage.membershipTransactionList',
            {
                _create: function () {
                    var self = this;

                    $("#from_date").calendar({'dateFormat':'mm/dd/yy'});
                    $("#to_date").calendar({'dateFormat':'mm/dd/yy'});

                    $("#seller_from_date").calendar({'dateFormat':'mm/dd/yy'});
                    $("#seller_to_date").calendar({'dateFormat':'mm/dd/yy'});

                }
            }
        );
        return $.mage.membershipTransactionList;
    }
);
