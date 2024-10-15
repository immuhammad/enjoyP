/**
 * Webkul Affiliate Pay To User Popup script.
 *
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define(
    [
    'Magento_Ui/js/grid/columns/column',
    'jquery',
    'Magento_Ui/js/modal/modal'
    ],
    function (Column, $) {
        'use strict';
        return Column.extend(
            {
                defaults: {
                    bodyTmpl: 'ui/grid/cells/html',
                    fieldClass: {
                        'data-grid-html-cell': true
                    }
                },
                preview: function (row) {
                    var previewPopup = $('<div/>').html(row['text']);
                    previewPopup.modal(
                        {
                            title: $.mage.__('Banner Layout'),
                            innerScroll: true,
                            modalClass: '_image-box',
                            buttons: [{
                                text: $.mage.__('Ok'),
                                class: 'transaction-button'
                                   // click: function () {}
                            }]
                        }
                    ).trigger('openModal');
                },
                getFieldHandler: function (row) {
                    return this.preview.bind(this, row);
                }
            }
        );
    }
);
