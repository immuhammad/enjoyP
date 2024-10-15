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
            'affiliate.bannerlist',
            {
                _create: function () {
                    var importOpts = this.options;
                    $('body').delegate(
                        '.action.preview',
                        'click',
                        function () {
                            var options = {
                                type: 'popup',
                                responsive: true,
                                innerScroll: true,
                                width:'200px',
                                title: $t('Banner Preview'),
                                buttons: [{
                                    text: $.mage.__('Ok'),
                                    class: '',
                                    click: function () {
                                        this.closeModal();
                                    }
                                }]
                            };
                            var cont = $(this).parents('.wk-row-view').find('.banner_content').val();
                            cont = $('<div />').append(cont);

                            modal(options, cont);
                            cont.modal('openModal');
                        }
                    );
                    $(".banner_content").on("click",function () {
                        $(this).select();
                    });
                    $(".wk-copy-txt-btn").on("click",function () {
                        var copyText = $(this).parents('.wk-row-view').find('.wk-copy-to-txt');
                        copyText.select();
                        document.execCommand("copy");
                    });
                }
            }
        );
        return $.affiliate.bannerlist;
    }
);
