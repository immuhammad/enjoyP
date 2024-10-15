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
    'mage/template',
    'text!Webkul_Affiliate/templates/grid/cells/user/paytoaffibybanktemplate.html',
    'text!Webkul_Affiliate/templates/grid/cells/user/paytoaffibychecktemplate.html',
    'text!Webkul_Affiliate/templates/grid/cells/user/paytoaffibypaypaltemplate.html',
    'Magento_Ui/js/modal/modal'
    ],
    function (Column, $, mageTemplate, payToAffiByBankTemplate, payToAffiByCheckTemplate, payToAffiByPaypalTemplate) {
        'use strict';
        return Column.extend(
            {
                defaults: {
                    bodyTmpl: 'ui/grid/cells/html',
                    fieldClass: {'data-grid-html-cell': true }
                },
                getFlag: function (row) {
                    return row[this.index + '_flag'];
                },
                gethtml: function (row) {
                    return row[this.index + '_html'];
                },
                getFormaction: function (row) {
                    return row[this.index + '_formaction'];
                },
                getSellerid: function (row) {
                    return row[this.index + '_sellerid'];
                },
                getLabel: function (row) {
                    return row[this.index + '_html']
                },
                getTitle: function (row) {
                    return row[this.index + '_title']
                },
                getSubmitlabel: function (row) {
                    return row[this.index + '_submitlabel']
                },
                getCancellabel: function (row) {
                    return row[this.index + '_cancellabel']
                },
                getPaymentMethod: function (row) {
                    if (row[this.index + '_seller_payment_method'] != '') {
                        return $.parseJSON(row[this.index + '_seller_payment_method']);
                    } else {
                        return null;
                    }
                },
                getAddTransactionContent: function (row) {
                    if (row['aff_balance_amount'] > 0 ) {
                        return $('<div />', {'class':'tran'})
                            .append($('<h3 />').text('Enter Transaction Detail'))
                            .append(
                                $('<div />',{'class':'field'})
                                .append($('<label />',{'class':'label'}).text('Transaction Id : '))
                                .append($('<input />',{'type':'text','class':'admin__control-text transaction_id','name':'transaction_id'}))
                            )
                            .append(
                                $('<div />',{'class':'field'})
                                .append($('<label />',{'class':'label'}).text('IPN Transaction Id : '))
                                .append($('<input />',{'type':'text','class':'admin__control-text ipn_transaction_id','name':'ipn_transaction_id'}))
                            )
                            .append(
                                $('<div />',{'class':'field'})
                                .append($('<label />',{'class':'label'}).text('Amount : '))
                                .append($('<input />',{'type':'text','class':'admin__control-text transaction_amount','name':'transaction_amount'}))
                            );
                    } else {
                        return "";
                    }
                },
                preview: function (row) {
                    console.log(row);
                    var paymentMethod = this.getPaymentMethod(row);
                    var content = this.getAddTransactionContent(row);
                    var transSaveAction = this.getFormaction(row);
                    var loader = $('<img />',{'src':row[this.index + "_loader_src"],'class':'loader affiliate-loader','style':'margin-right:5px;vertical-align: middle;'});
                    var allowedPayment = paymentMethod == null ? 'xxx' : paymentMethod['payment_method'];
                    console.log(allowedPayment);
                    switch (allowedPayment) {
                        case 'banktransfer':
                            var modalHtml = mageTemplate(
                                payToAffiByBankTemplate,
                                {
                                    formaction: this.getFormaction(row),
                                    sellerid: this.getSellerid(row),
                                    accountHolder: paymentMethod['account_data']['account_holder'],
                                    bankName:paymentMethod['account_data']['bank_name'],
                                    accountNumber:paymentMethod['account_data']['account_number'],
                                    bankAddress:paymentMethod['account_data']['bank_address'],
                                    code:paymentMethod['account_data']['code'],
                                    linkText: $.mage.__('Go to Details Page')
                                }
                            );
                            var previewPopup = $('<div/>').html(modalHtml);
                            previewPopup.modal(
                                {
                                    title: this.getTitle(row),
                                    innerScroll: true,
                                    modalClass: '_image-box',
                                    buttons: [{
                                        text: $.mage.__('Add Transaction Detail'),
                                        class: 'transaction-button',
                                        click: function () {
                                            if (content != '') {
                                                var length = this._getElem($('.tran')).length;
                                                var transbutton = this._getElem($('.transaction-button'));
                                                if (length) {
                                                    transbutton.prevAll('.message').remove();
                                                    $(".affiliate-loader").remove();
                                                    transbutton.before(loader.clone());
                                                    jQuery.ajax(
                                                        {
                                                            url: transSaveAction,
                                                            data: {
                                                                form_key: window.FORM_KEY,
                                                                aff_customer_id:row['aff_user_id'],
                                                                transaction_amount : this._getElem($('.transaction_amount')).val(),
                                                                payment_method : paymentMethod['payment_method'],
                                                                transaction_id : this._getElem($('.transaction_id')).val(),
                                                                ipn_transaction_id : this._getElem($('.ipn_transaction_id')).val()
                                                            },
                                                            type: 'POST',
                                                            success: function (responsedata) {
                                                                transbutton.prevAll('.loader').remove();
                                                                if (responsedata['status']) {
                                                                    location.reload();
                                                                    // transbutton.before(
                                                                    //     $('<span />', {'class':'message affiliate message-success'})
                                                                    //     .append($('<span />').text(responsedata['msg']))
                                                                    // );
                                                                } else {
                                                                    transbutton.before(
                                                                        $('<span />', {'class':'message affiliate message-error'})
                                                                        .append($('<span />').text(responsedata['msg']))
                                                                    );
                                                                }
                                                            },
                                                            error: function (error) {
                                                                transbutton.prevAll('.loader').remove();
                                                                transbutton.before(
                                                                    $('<span />', {'class':'message affiliate message-error'})
                                                                    .append($('<span />').text(error))
                                                                );
                                                            }
                                                        }
                                                    ).done(
                                                        function () {
                                                            transbutton.prev('.loader').remove();
                                                        }
                                                    );
                                                } else {
                                                    this._getElem($('.bootbox-body')).append(content);
                                                    transbutton.find('span').text($.mage.__('Save Transaction Detail'));
                                                }
                                            } else {
                                                alert($.mage.__('Payment amount is not available'));
                                            }
                                        }
                                    }]
                                }
                            ).trigger('openModal');
                            break;
                        case 'checkmo':
                            var modalHtml = mageTemplate(
                                payToAffiByCheckTemplate,
                                {
                                    flag: this.getFlag(row),
                                    html: this.gethtml(row),
                                    title: this.getTitle(row),
                                    label: this.getLabel(row),
                                    formaction: this.getFormaction(row),
                                    sellerid: this.getSellerid(row),
                                    payableTo: paymentMethod['account_data']['payable_to'],
                                    linkText: $.mage.__('Go to Details Page')
                                }
                            );
                            var previewPopup = $('<div/>').html(modalHtml);
                            var content = this.getAddTransactionContent(row);
                            previewPopup.modal(
                                {
                                    title: this.getTitle(row),
                                    innerScroll: true,
                                    modalClass: '_image-box',
                                    buttons: [{
                                        text: $.mage.__('Add Transaction Detail'),
                                        class: 'transaction-button',
                                        click: function () {
                                            if (content != '') {
                                                var length = this._getElem($('.tran')).length;
                                                var transbutton = this._getElem($('.transaction-button'));
                                                if (length) {
                                                    transbutton.prevAll('.message').remove();
                                                    $(".affiliate-loader").remove();
                                                    transbutton.before(loader.clone());
                                                    jQuery.ajax(
                                                        {
                                                            url: transSaveAction,
                                                            data: {
                                                                form_key: window.FORM_KEY,
                                                                aff_customer_id:row['aff_user_id'],
                                                                transaction_amount : this._getElem($('.transaction_amount')).val(),
                                                                payment_method : paymentMethod['payment_method'],
                                                                transaction_id : this._getElem($('.transaction_id')).val(),
                                                                ipn_transaction_id : this._getElem($('.ipn_transaction_id')).val()
                                                            },
                                                            type: 'POST',
                                                            success: function (responsedata) {
                                                                transbutton.prevAll('.loader').remove();
                                                                if (responsedata['status']) {
                                                                    location.reload();
                                                                    // transbutton.before(
                                                                    //     $('<span />', {'class':'message affiliate message-success'})
                                                                    //     .append($('<span />').text(responsedata['msg']))
                                                                    // );
                                                                } else {
                                                                    transbutton.before(
                                                                        $('<span />', {'class':'message affiliate message-error'})
                                                                        .append($('<span />').text(responsedata['msg']))
                                                                    );
                                                                }
                                                            },
                                                            error: function (error) {
                                                                transbutton.prevAll('.loader').remove();
                                                                transbutton.before(
                                                                    $('<span />', {'class':'message affiliate message-error'})
                                                                    .append($('<span />').text(error))
                                                                );
                                                            }
                                                        }
                                                    ).done(
                                                        function () {
                                                            transbutton.prev('.loader').remove();
                                                        }
                                                    );
                                                } else {
                                                    this._getElem($('.bootbox-body')).append(content);
                                                    transbutton.find('span').text($.mage.__('Save Transaction Detail'));
                                                }
                                            } else {
                                                alert($.mage.__('Payment amount is not available'));
                                            }
                                        }
                                    }]
                                }
                            ).trigger('openModal');
                            break;
                        case 'paypal_standard':
                            var modalHtml = mageTemplate(
                                payToAffiByPaypalTemplate,
                                {
                                    urlFix : row[this.index + "_urlfix"],
                                    currentCurrencyCode :row[this.index + "_currency"],
                                    returnurl: row[this.index + "_returnurl"],
                                    cancelReturn: row[this.index + "_cancelreturn"],
                                    ipnnotify:row[this.index + "_ipnnotify"],
                                    adminEmail: row[this.index + "_admin_email"],
                                    adminFirstName : row[this.index + "_firstname"],
                                    adminLastName : row[this.index + "_lastname"],
                                    sellerid : row[this.index + "_sellerid"],
                                    ammounttopay : row['balance_amount'].substr(1).replace(new RegExp(",", 'g'), ""),
                                    invoice : Math.floor((Math.random() * 10000000) + 1)+'-'+row[this.index + "_sellerid"]
                                }
                            );
                            var sellerid = row[this.index + "_sellerid"];
                            var previewPopup = $('<div/>').html(modalHtml);
                            previewPopup.modal(
                                {
                                    title: this.getTitle(row),
                                    innerScroll: true,
                                    modalClass: '_image-box',
                                    buttons: [{
                                        text: $.mage.__('Pay to Affiliate By Paypal'),
                                        class: 'transaction-button paypal',
                                        click: function () {
                                            $(".affiliate-loader").remove();
                                            this._getElem($('.transaction-button')).before(loader.clone());
                                            $('#wk_aff_paypal_standard_checkout'+sellerid).submit();
                                        }
                                    }]
                                }
                            ).trigger('openModal');
                            break;
                        default :
                            alert($.mage.__('Payment method is not set by user'));
                            break;
                    }
                },
                getFieldHandler: function (row) {
                    return this.preview.bind(this, row);
                }
            }
        );
    }
);
