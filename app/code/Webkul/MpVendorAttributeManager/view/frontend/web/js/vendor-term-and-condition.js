/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpVendorAttributeManager
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function ($) {
    'use strict';

    return function (optionsConfig) {
      
        var conditionModel = $('<div>').html(optionsConfig.condition).modal({
            modalClass: 'term-condition',
            type: optionsConfig.animate,
            title: optionsConfig.termheading,
            responsive: true,
            innerScroll: true,
            buttons: [{
                text: optionsConfig.buttontitle,
                'class': 'primary',
                click: function () {
                    this.closeModal();
                }
            }]
        });
        var privacyModel = $('<div>').html(optionsConfig.privacy).modal({
            modalClass: 'privacy-condition',
            type: optionsConfig.animate,
            title: optionsConfig.privacyheading,
            responsive: true,
            innerScroll: true,
            buttons: [{
                text: optionsConfig.buttontitle,
                'class': 'primary',
                click: function () {
                    this.closeModal();
                }
            }]
        });
        $('body').on('click', '#read-term-condition', function (event) {
            event.preventDefault();
            conditionModel.modal('openModal');
        });
        $('body').on('click', '#read-privacy', function (event) {
            event.preventDefault();
            privacyModel.modal('openModal');
        });
    };
});
