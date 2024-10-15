/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpquotesystem
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint jquery:true*/
define(
    [
    'jquery',
    'ko',
    'Magento_Ui/js/form/form',
    'Magento_Customer/js/action/login',
    'Magento_Customer/js/customer-data',
    'Magento_Customer/js/model/authentication-popup',
    'mage/translate',
    'mage/url',
    'mage/validation'
    ], function ($, ko, Component, loginAction, customerData, ajaxLogin, $t, url) {
        'use strict';

        return Component.extend(
            {
                registerUrl: window.ajaxLogin.customerRegisterUrl,
                forgotPasswordUrl: window.ajaxLogin.customerForgotPasswordUrl,
                autocomplete: window.ajaxLogin.autocomplete,
                modalWindow: null,
                isLoading: ko.observable(false),

                defaults: {
                    template: 'Webkul_Mpquotesystem/ajaxlogin-popup'
                },

                /**
                 * Init
                 */
                initialize: function () {
                    var self = this;

                    this._super();

                    url.setBaseUrl(window.ajaxLogin.baseUrl);
                    loginAction.registerLoginCallback(
                        function () {
                            self.isLoading(false);
                        }
                    );
                },

                /**
                 * Init popup login window 
                 */
                setAjaxModelElement: function (element) {
                    if (ajaxLogin.modalWindow == null) {
                        ajaxLogin.createPopUp(element);
                    }
                },

                /**
                 * Is login form enabled for current customer 
                 */
                isActive: function () {
                    var customer = customerData.get('customer');

                    return customer() == false; //eslint-disable-line eqeqeq
                },

                /**
                 * Show login popup window 
                 */
                showModal: function () {
                    if (this.modalWindow) {
                        $(this.modalWindow).modal('openModal');
                    }
                },

                /**
                 * Provide login action
                 *
                 * @return {Boolean}
                 */
                login: function (formUiElement, event) {
                    var loginData = {},
                    formElement = $(event.currentTarget),
                    formDataArray = formElement.serializeArray();

                    event.stopPropagation();
                    event.preventDefault();

                    formDataArray.forEach(
                        function (entry) {
                            loginData[entry.name] = entry.value;
                        }
                    );

                    if (formElement.validation() 
                        && formElement.validation('isValid')
                    ) {
                        this.isLoading(true);
                        loginAction(loginData);
                    }

                    return false;
                }
            }
        );
    }
);
