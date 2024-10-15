/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mpquotesystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'mage/template',
    'uiComponent',
    'ko',
    ], function ($, mageTemplate, Component, ko) {
        'use strict';
        return Component.extend({
            initialize: function () {
                this._super();
                this.notifyTmp = mageTemplate('#wk_notification_template');
                this.quoteData = window.notificationConfig.quoteNotification;
                if (this.quoteData.length) {
                    this._showQuoteNotification(this.quoteData);
                }
            },
            _showQuoteNotification: function (quoteData) {
                $('[data-ui-id="menu-webkul-mpquotesystem-mpquotes"]').css('position','relative');
                $('[data-ui-id="menu-webkul-mpquotesystem-mpquotes"]').css('padding-right', '50px');
                var data = {},
                    notifyTmp;

                data.notificationCount = quoteData.length;
                data.notificationImage = window.notificationConfig.image;
                data.notifications = quoteData;
                data.notificationType = 'quote';
                notifyTmp = this.notifyTmp({
                    data: data
                });
                $(notifyTmp)
                .appendTo($('[data-ui-id="menu-webkul-mpquotesystem-mpquotes"]'));
            }
        });
    });
