/**
 * Webkul Affiliate banner list script
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
            'affiliate.register',
            {
                _create: function () {
                    var importOpts = this.options;
                    $('body').delegate(
                        '#aff_term_light',
                        'click',
                        function (e) {
                            e.preventDefault();
                            var options = {
                                type: 'popup',
                                responsive: true,
                                innerScroll: true,
                                width:'200px',
                                title: $t('Affiliate Terms'),
                                buttons: [{
                                    text: $.mage.__('Ok'),
                                    class: '',
                                    click: function () {
                                        this.closeModal();
                                    }
                                }]
                            };
                            var cont = $(this).attr('data-terms');
                            cont = $('<div />').append(cont);

                            modal(options, cont);
                            cont.modal('openModal');
                        }
                    );
                }
            }
        );
        return $.affiliate.register;
    }
);
