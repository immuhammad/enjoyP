/**
 * Webkul Affiliate Preference page script
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define(
    [
    "jquery",
    "mage/translate",
    "Magento_Ui/js/modal/modal"
    ],
    function ($, $t, modal) {
        "use strict";
        $.widget(
            'affiliateadmin.preference',
            {
                _create: function () {
                    var methodOpts = this.options;
                    $('body').delegate(
                        '#payment_method',
                        'change',
                        function () {
                            $('#payment-data').html($('#'+$(this).val()+'-template').clone().html());
                            $.each(
                                methodOpts['account_data'],
                                function (index, value) {
                                    $('#'+index).val(value);
                                }
                            );
                        }
                    );
                    $('#payment_method').val(methodOpts['payment_method']).trigger('change');
                }
            }
        );
        return $.affiliateadmin.preference;
    }
);
