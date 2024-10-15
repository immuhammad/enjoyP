/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Stripe
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*browser:true*/
/*global define*/
define(
    [
        'ko',
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/customer-data',
        'mage/translate',
        'Magento_Checkout/js/action/set-billing-address',
        'Magento_Ui/js/model/messageList'
    ],
    function (
        ko,
        $,
        Component,
        selectPaymentMethodAction,
        checkoutData,
        quote,
        customerData,
        $t,
        setBillingAddressAction,
        globalMessageList
    ) {
        'use strict';
        /**
         * stripeConfig contains all the payment configuration
         */
         var stripeConfig = window.checkoutConfig.payment.stripe;
         var sections = ['cart'];
         customerData.invalidate(sections);
         customerData.reload(sections, true);
 
        return Component.extend(
            {
                defaults: {
                    template: 'Webkul_Stripe/payment/stripe',
                    stripeObject: null,
                    logoUrl:stripeConfig.image_on_form
                },

                /**
                 * @override
                 */
                initObservable: function () {
                    var self =  this;
                    window.webkulStripeSelf =  this;
                    this._super();
                    this.initStripeCheckout();
                    return this;
                    
                },

                initStripeCheckout: function () {
                    this.stripeObject = Stripe(
                        stripeConfig.api_publish_key,
                        {
                          betas: ['checkout_beta_4']
                        }
                    );
                },

                getCode: function () {
                    return "stripe";
                },

                /**
                 * validate  to validate the payment method fields at checkout page
                 *
                 * @return boolean
                 */
                validate: function () {
                    return true;
                },

                placeOrderHtml: function () {

                    let that = this;
                    that.redirectAfterPlaceOrder = false;
                    let deferred = $.Deferred();
                    $('body').trigger('processStart');
                    if (!window.checkoutConfig.quoteData.customer_id || window.checkoutConfig.quoteData.customer_id == null) {
                        var email = that.getGuestEmail();
                    } else {
                        var email = that.getEmail();
                    }
                    if (quote.isVirtual()) {
                        setBillingAddressAction(globalMessageList);
                    }
                    $.ajax({
                        url: stripeConfig.get_session_url,
                        dataType:'json',
                        contentType: "application/json",
                        method: 'get',
                        data: {
                            'email' : email
                        },
                        success: function (response) {
                            if (response.id) {
                                deferred.resolve(response);
                            } else {
                                    deferred.reject(response);
                            }
                        },
                        error: function (error) {
                                deferred.reject(error);
                        }
                    });

                    deferred.promise().then(
                        function (data) {
                            that.stripeObject.redirectToCheckout({
                                sessionId: data.id
                            });
                        },
                        function (error) {
                            $('body').trigger('processStop');
                            console.log(error);
                            that.messageContainer.addErrorMessage({message: $t("something went wrong")});
                        }
                    );
                },

                /**
                 * selectPaymentMethod called when payment method is selected
                 *
                 * @return boolean
                 */
                selectPaymentMethod: function address()
                {
                    selectPaymentMethodAction(this.getData());
                    checkoutData.setSelectedPaymentMethod(stripeConfig.method);
                    return true;

                },

                /**
                 * getData set payment method data for making it available in PaymentMethod Class
                 *
                 * @return object
                 */
                getData: function () {
                    var self = this;

                    return {
                        'method': stripeConfig.method,
                    };
                },
                /**
                 * get the guest user email address
                 */
                getGuestEmail: function () {
                    return quote.guestEmail;
                },

                /**
                 * get the logged in user email address
                 */
                getEmail: function () {
                    return window.checkoutConfig.customerData.email;
                }
            }
        );
    }
);
