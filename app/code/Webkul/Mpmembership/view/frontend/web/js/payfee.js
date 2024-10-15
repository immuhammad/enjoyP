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
            'mage.mpSellerMembership',
            {
                options: {
                    formId: '#form-slider-validate',
                    productId: '.mp-pid',
                    totalAmount: '.wk_amountdata .value',
                    numberOfProducts: '.allowed_product .value',
                    allowedProducts: '#allowed_products',
                    amountHidden: '#amount_1'
                },
                _create: function () {

                    var self = this;
                    var products = self.options.products;
                    var currencySymbol = self.options.currencySymbol;

                    // calculateFee();

                    $(self.options.productId).on(
                        'click',
                        function () {
                            calculateFee();
                        }
                    );

                    function calculateFee()
                    {
                        var cost=0;
                        var counter = 0;

                        $(self.options.productId).each(
                            function () {
                                if ($(this).is(':checked')) {
                                    counter++;
                                    cost = cost+products[$(this).val()];
                                } else {
                                    $(this).removeAttr('checked');
                                }
                            }
                        );

                        $(self.options.totalAmount).text(currencySymbol+parseFloat(cost).toFixed(2));
                        $(self.options.numberOfProducts).text(counter);
                        $('body '+self.options.amountHidden).val(parseFloat(cost).toFixed(2));
                        $('body '+self.options.allowedProducts).val(counter);

                        if (counter==0) {
                            alert({
                                content: $t('At least one product need to pay fee')
                            });
                        }
                    }
                }
            }
        );
        return $.mage.mpSellerMembership;
    }
);
