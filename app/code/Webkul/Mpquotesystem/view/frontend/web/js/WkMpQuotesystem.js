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
var globalFlag = true;
define(
    [
    "jquery",
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/confirm',
    'Magento_Customer/js/customer-data',
    "Magento_Ui/js/modal/modal",
    "Magento_Catalog/js/product/provider"
    ], function ($, $t, alert, confirm, customerData, modal,productProvider) {
        'use strict';
        $.widget(
            'mage.WkMpQuotesystem', {
                options: {
                    backUrl: '',
                    confirmMessageForEditQuote: $t(' Are you sure you want to edit this quote ? '),
                    errorNoCheckBoxChecked: $t(' No Checkbox is checked '),
                    confirmMessageForDeleteQuote: $t(' Are you sure you want to delete these quotes ? '),
                    confirmMessageForsingleDeleteQuote: $t(' Are you sure you want to delete this quote ? '),
                    errorRequestedQuantity: $t('Requested Amount is not available please contact Admin'),
                    errorQuoteItemAlreadyInCart: $t('A Quote item of same product is already added in cart.'),
                    errorContactAdmin: $t('Something Went Wrong, Please try again later'),
                    ajaxErrorMessage: $t('There is some error during executing this process, please try again later.'),
                    addToQuoteButtonTextWhileAdding: '',
                    addToQuoteButtonDisabledClass: 'disabled',
                    addToQuoteButtonTextAdded: '',
                    addToQuoteButtonTextDefault: '',
                    categoryListAction: '',
                    categoryListItem : '',
                    addQuoteModel:null,
                    invalidExtension : $t('Invalid Image Extension. Allowed extensions are ')
                },
                _create: function () {
                    $(document).ready(function() {
                        $('.mp_quote_delete').attr('data-url',window.btoa($('.mp_quote_delete').attr('data-url')));
                    });
                    var self = this;
                    var dataForm = $(self.options.quoteForm);
                    var html = $(self.options.quoteButtonHtml);
                    var showCart = self.options.showCart;
                    $('input[name="quote_attachment"]').on(
                        'change', function (e) {
                            var allowedTypes = $(this).attr("data-allowed-types").split(",");
                            var attachVal = $(this).val();
                            var splitType = attachVal.substring(attachVal.lastIndexOf(".") + 1, attachVal.length);
                            if (allowedTypes.indexOf(splitType) < 0) {
                                var thisEle = $(this);
                                e.stopImmediatePropagation();
                                alert(
                                    {
                                        title: 'Attention!',
                                        content: self.options.invalidExtension+$(this).attr("data-allowed-types"),
                                        actions: {
                                            always: function () {
                                                thisEle.val('');
                                            }
                                        }
                                    }
                                );
                            }
                        }
                    );

                    if (!showCart && $(html).length > 0) {
                        $('.action.primary.tocart').remove();
                        $('.action.primary.customize').html('<span>Customize and Add to Quote</span>');
                    }

                    $('#quoteminqty').focus(function() {
                        $('#quoteminqty-error-msg').remove();
                    }).blur(function() {
                        var minQty = $('#quoteminqty').val();
                        if ((minQty % 1) != 0) {
                            var str = '<span id="quoteminqty-error-msg">'+self.options.errorMessage+'</span>';
                            $('#quoteminqty').after(str);
                            $('#quoteminqty-error-msg').css("color", "red");
                            $('#quoteminqty').val('');
                        }
                    });

                    $('.change-attach').on(
                        'click', function (e) {
                            e.preventDefault();

                            $(this).hide().siblings('.attach-link').hide();
                            $(this).siblings('.control').removeClass('_attachment-hide');
                        }
                    );

                    $('#mpqsresetbtn').on(
                        'click', function (e) {
                            var form = $(self.options.productAddToCartForm);
                            $(form)[0].reset();
                            $('#quote_qty-error').hide();
                            $('.quote_qty').removeClass('mage-error');
                        }
                    );

                    if ($(self.options.addToCartAction).length > 0) {
                        var popoverbackgroundhtml = $(self.options.popoverbackgroundhtml);
                        if ($(self.options.paypal).length >0) {
                            $(self.options.productAddToCartForm).append(popoverbackgroundhtml);
                            $(html).css("float", "left");
                            $(self.options.addToCartAction).append(html);
                        } else {
                            $(self.options.addToCartAction).append(html);
                        }
                    } else {
                        $(self.options.productAddToCartForm).append(html);
                    }
                    var popoverhtml = $(self.options.popoverbackgroundhtml);
                    $(self.options.productAddToCartForm).append(popoverhtml);
                    var productForm = $(self.options.productAddToCartForm);
                    productForm.mage('validation', {});
                    var formAction = $(self.options.productAddToCartForm).attr("action");

                    dataForm.mage('validation', {});
                    $(self.options.mpquoteedit).on(
                        'click', function () {
                            var element = $(this);
                            var dicision = confirm(
                                {
                                    title:$t('Edit Quote'),
                                    content: self.options.confirmMessageForEditQuote,
                                    actions: {
                                        confirm: function () {
                                            var $url=$(element).attr('data-url');
                                            window.location = $url;
                                        },
                                    }
                                }
                            );
                        }
                    );
                    if (self.options.quotestatus==0) {
                        $('.quote-min-qty-field').hide();
                        $('.quote-min-qty-field input').removeClass('required-entry');
                        $('.quote-min-qty-field input').removeClass('validate-number');
                    }
                    $(self.options.mpmassdelete).click(
                        function (e) {
                            e.preventDefault();
                            var flag =0;
                            $(self.options.mpquotecheckbox).each(
                                function () {
                                    if (this.checked == true) {
                                        flag = 1;
                                    }
                                }
                            );
                            if (flag == 0) {
                                alert(
                                    {
                                        content: self.options.errorNoCheckBoxChecked
                                    }
                                );
                                return false;
                            } else {
                                var dicision = confirm(
                                    {
                                        title:$t('Delete Quote'),
                                        content: self.options.confirmMessageForDeleteQuote,
                                        actions: {
                                            confirm: function () {
                                                $(self.options.massdeleteform).submit();
                                            },
                                        }
                                    }
                                );
                            }
                        }
                    );

                    $(self.options.mpselectall).click(
                        function (event) {
                            if (this.checked) {
                                $(self.options.mpquotecheckbox).each(
                                    function () {
                                        this.checked = true;
                                    }
                                );
                            } else {
                                $(self.options.mpquotecheckbox).each(
                                    function () {
                                        this.checked = false;
                                    }
                                );
                            }
                        }
                    );
                    $(self.options.mpquotedelete).click(
                        function () {
                            var element = $(this);
                            var dicision = confirm(
                                {
                                    title:$t('Delete Quote'),
                                    content: self.options.confirmMessageForsingleDeleteQuote,
                                    actions: {
                                        confirm: function () {
                                            var $url=$(element).attr('data-url');
                                            window.location = window.atob($url);
                                        },
                                    }
                                }
                            );
                        }
                    );
                    $(self.options.mpquotestatus).on(
                        "click", function () {
                            self.ajaxRequestForAddToCart(this);
                        }
                    );
                    $(self.options.saveButton).on(
                        "click",function () {
                            if ($(self.options.quoteForm).valid()!=false) {
                                $(self.options.saveButton).attr("disabled","disabled");
                                $(self.options.quoteForm).submit();
                            }
                        }
                    );
                    $(self.options.switchOption).on(
                        "click",function () {
                            if ($(this).is(":checked")) {
                                $(self.options.quotePrice).removeAttr("disabled");
                                $(self.options.quoteQuantity).removeAttr("disabled");
                            } else {
                                $(self.options.quoteQuantity).attr("disabled","disabled");
                                $(self.options.quotePrice).attr("disabled","disabled");
                            }
                        }
                    );
                    $(self.options.quoteButtonHtml).on(
                        "click",function () {
                            $('body').trigger('processStart');
                            var customer = customerData.get('customer');
                            if (customer().firstname == false || customer().firstname == undefined) {
                                if ($('body').find('a.proceed-to-checkout').length) {
                                    $('body').trigger('processStop');
                                    $('body').find('a.proceed-to-checkout').trigger('click');
                                } else {
                                    self.updateCustomerData();
                                }
                            } else {
                                $('body').trigger('processStop');
                                if ($(self.options.productAddToCartForm).valid()!=false) {
                                    $(self.options.popoverbackgroundhtml).find('.wk-mp-model-popup').addClass('_show');
                                    $(self.options.popoverbackgroundhtml).show();
                                }
                            }
                        }
                    );
                    $(self.options.popOverclose).on(
                        "click",function () {
                            $(self.options.productAddToCartForm).attr("action",formAction);
                            $(self.options.popoverbackgroundhtml).hide();
                        }
                    );
                    $(self.options.submitButton).on(
                        'click', function () {
                            var form = $(self.options.productAddToCartForm);
                            if ($(form).validation() && $(form).validation('isValid')) {
                                self.submitQuote(this);
                            } else {
                                $('.mage-error').hide();
                                $('.fieldset .mage-error').show();
                                $('.quote_submit_fields').show();
                            }
                        }
                    );

                    $(self.options.quoteStatus).on(
                        'change', function () {
                            var status = $(this).val();
                            if (status == 1) {
                                $(self.options.quoteMinQuantity).parents('.quote-min-qty-field').show();
                                $(self.options.quoteMinQuantity).addClass('required-entry');
                                $(self.options.quoteMinQuantity).addClass('validate-number');
                            } else {
                                $(self.options.quoteMinQuantity).parents('.quote-min-qty-field').hide();
                                $(self.options.quoteMinQuantity).removeClass('validate-number');
                                $(self.options.quoteMinQuantity).removeClass('required-entry');
                            }
                        }
                    );
                    $(self.options.productitems).each(
                        function () {
                            var product = $(this);
                            var quoteData = $(self.options.quoteProductData);
                            var productId = product.find('.price-box').attr('data-product-id');
                            if (quoteData!==undefined && quoteData[0]!==undefined && quoteData[0][productId]!==undefined) {
                                self.addQuoteButton(product, quoteData[0][productId], productId, showCart);
                            }
                        }
                    );
                    $(self.options.wishlistproductitems).each(
                        function () {
                            var product = $(this);
                            var quoteData = $(self.options.quoteProductData);
                            var productId = product.find('.price-box').attr('data-product-id');
                            if (quoteData!==undefined && quoteData[0]!==undefined && quoteData[0][productId]!==undefined) {
                                self.addQuoteButtonToWishlist(product, quoteData[0][productId], productId, showCart);
                            }
                        }
                    );
                    $(self.options.compareproductitems).each(
                        function () {
                            var product = $(this);
                            var quoteData = $(self.options.quoteProductData);
                            var productId = product.find('.price-box').attr('data-product-id');
                            if (quoteData!==undefined && quoteData[0]!==undefined && quoteData[0][productId]!==undefined) {
                                self.addQuoteButtonToCompare(product, quoteData[0][productId], productId, showCart);
                            }
                        }
                    );
                    if(globalFlag) {
                        $('body').delegate(
                            ".mpquotesystem_cat_add", 'click', function (event) {
                                event.stopPropagation();
                                event.preventDefault();
                                self.options.categoryListItem = $(this);
                                self.options.categoryListAction = 'popup';
                                self.checkAndAddToQuote($(this), 'popup');
                            }
                        );
                        globalFlag = false;
                    }
                    $('body').delegate(
                        ".mpquotesystem_redirect", 'click', function () {
                            self.options.categoryListItem = $(this);
                            self.options.categoryListAction = 'redirect';
                            self.checkAndAddToQuote($(this), 'redirect');
                        }
                    );
                    $(self.options.categorySubmitButton).on(
                        'click', function () {
                            var form = $(this).parents('form');
                            if ($(form).valid()!=false) {
                                self.categorySubmitQuote(form);
                            }
                        }
                    )
                },
                updateCustomerData:function () {
                    var self = this;
                    customerData.reload([], true).done(
                        function (sections) {
                            var customername = sections.customer.firstname;
                            if (customername == undefined) {
                                $('body').trigger('processStop');
                                confirm(
                                    {
                                        title:$t('Confirm Action'),
                                        content: $t('You will be redirected to Login/Sign Up page'),
                                        actions: {
                                            confirm: function () {
                                                window.location = self.options.loginurl;
                                            },
                                        }
                                    }
                                );
                            } else {
                                $('body').trigger('processStop');
                                if ($(self.options.quoteButtonHtml).length) {
                                    $(self.options.quoteButtonHtml).trigger('click');
                                } else {
                                    self.checkAndAddToQuote($(self.options.categoryListItem), self.options.categoryListAction);
                                }
                            }
                        }
                    );
                },
                submitQuote:function (this_this) {
                    var self = this;
                    var productForm = $(self.options.productAddToCartForm);
                    if ($(self.options.productAddToCartForm).valid()!==false) {
                        $('body').trigger('processStart');
                        $(this_this).text($t("Saving")+'..');
                        $(this_this).css('opacity','0.7');
                        $(this_this).css('cursor','default');
                        $(this_this).attr('disabled','disabled');
                        var action = self.options.saveQuoteUrl;
                        $(self.options.productAddToCartForm).attr("action",action);
                        $(self.options.productAddToCartForm).attr("enctype",'multipart/form-data');
                        $(this_this).removeAttr("onclick");
                        productForm.submit();
                    }
                },
                ajaxRequestForAddToCart:function (e) {
                    var self = this;
                    $("body").append($("<div>").addClass("wk_qs_front_loader").css("height",$(window).width()).append($("<div>")));
                    var quoteId = $(e).parents("td").siblings(".id").val();
                    var quoteQty = $(e).parents("td").siblings(".wk_qs_quote_qty").find("span").text();
                    var quotePrice = $(e).parents("td").siblings(".wk_qs_quote_price").val();
                    $.ajax(
                        {
                            url         :   self.options.addtocarturl,
                            data        :   {quote_id:quoteId,quote_qty:quoteQty,quote_price:quotePrice},
                            type        :   "post",
                            datatype    :   "html",
                            success     :   function (data) {
                                if (data.error===1) {
                                    alert(
                                        {
                                            content: data.message
                                        }
                                    );
                                } else {
                                    alert(
                                        {
                                            content: data.message
                                        }
                                    );
                                    if (data.redirecturl!='') {
                                        document.location.href = data.redirecturl;
                                    }
                                }
                                $("body").find(".wk_qs_front_loader").remove();

                            },
                            error: function (data) {
                                alert(
                                    {
                                        content: self.options.ajaxErrorMessage
                                    }
                                );
                                $("body").find(".wk_qs_front_loader").remove();
                            }
                        }
                    );
                },
                checkAndAddToQuote: function (element, type) {
                    if (type!=='') {
                        var self = this;
                        $('body').trigger('processStart');
                        var customer = customerData.get('customer');
                        if (customer().firstname == false || customer().firstname == undefined) {
                            self.updateCustomerData();
                        } else {
                            if (type=='redirect') {
                                window.location = $(element).attr('data-url');
                            } else {
                                self.validateAddToCartForm(element);
                                $('body').trigger('processStop');
                            }
                        }
                    }
                },
                validateAddToCartForm: function (element) {
                    var self = this;
                    var addToCartForm = $(element).parents('.product.actions.product-item-actions').find('form[data-role="tocart-form"]');
                    if ($(addToCartForm).validation() && $(addToCartForm).validation('isValid')) {
                        $('body').trigger('processStop');
                        self.addQuoteFormToPage(element);
                    }
                    $('body').trigger('processStop');
                },
                addQuoteButton:function (currentObject, quoteData, productId, showCart) {
                    var self = this;
                    if (currentObject.find(".mpquotesystem_cat_add").length > 0) {
                        return;
                    }
                    if (quoteData['status'] === 1) {
                        if (quoteData['min_qty'] === true) {
                            quoteData['min_qty'] = 1;
                        }
                        var html = "<button title='Add Quote' class='mpquotesystem_cat_add action toquote tocart primary' data-product-id='"+productId+"' data-url='"+quoteData['url']+"' data-qty='"+quoteData['min_qty']+"'>"+
                        "<span>"+$t('Add to Quote')+"</span>"+
                        "</button>";
                    } else {
                        var html = "<button title='Add Quote' class='mpquotesystem_redirect action toquote tocart primary' data-url='"+quoteData['url']+"'>"+
                        "<span>"+$t('Add to Quote')+"</span>"+
                        "</button>";
                    }
                    var attrC = $(location).attr("href");
                    var incStr = attrC.includes("wishlist");  
                    if (incStr == true && showCart == 1 && quoteData['stockStatus']) {
                        currentObject.find(".product-item-actions .actions-primary button").data('post')['data']['product'] = productId;
                    }
                    currentObject.find(".product-item-actions .actions-primary").append(html);
                    if (!showCart) {
                        currentObject.find(".action.tocart.primary").not(".mpquotesystem_cat_add").remove();
                    }
                },
                addQuoteButtonToWishlist:function (currentObject, quoteData, productId, showCart) {
                    var self = this;
                    var html;
                    if (currentObject.find(".mpquotesystem_cat_add").length > 0) {
                        return;
                    }
                    if (quoteData['status'] === 1) {
                        if (quoteData['min_qty'] === true) {
                            quoteData['min_qty'] = 1;
                        }
                        html = "<button title='Add Quote' class='mpquotesystem_cat_add action tocart primary' data-product-id='"+productId+"' data-url='"+quoteData['url']+"' data-qty='"+quoteData['min_qty']+"'>"+
                        "<span>"+$t('Add to Quote')+"</span>"+
                        "</button>";
                    } else {
                        html = "<button title='Add Quote' class='mpquotesystem_redirect action tocart primary' data-url='"+quoteData['url']+"'>"+
                        "<span>"+$t('Add to Quote')+"</span></button>";
                    }
                    currentObject.find(".product-item-actions .actions-primary").append(html);
                    if (!showCart) {
                        currentObject.find(".action.tocart.primary").not(".mpquotesystem_cat_add").remove();
                    }
                },
                addQuoteButtonToCompare:function (currentObject, quoteData, productId, showCart) {
                    var self = this;
                    if (currentObject.find(".mpquotesystem_cat_add").length > 0) {
                        return;
                    }
                    if (quoteData['status'] === 1) {
                        if (quoteData['min_qty'] === true) {
                            quoteData['min_qty'] = 1;
                        }
                        var html = "<button title='Add Quote' class='mpquotesystem_cat_add tocart action primary' data-product-id='"+productId+"' data-url='"+quoteData['url']+"' data-qty='"+quoteData['min_qty']+"'>"+
                        "<span>"+$t('Add to Quote')+"</span>"+
                        "</button>";
                    } else {
                        var html = "<button title='Add Quote' class='mpquotesystem_redirect tocart action primary' data-url='"+quoteData['url']+"'>"+
                        "<span>"+$t('Add to Quote')+"</span>"+
                        "</button>";
                    }
                    currentObject.find(".product-item-actions .actions-primary").append(html);
                    if (!showCart) {
                        currentObject.find(".action.tocart.primary").not(".mpquotesystem_cat_add").remove();
                    }
                },
                categorySubmitQuote: function (form) {
                    var self = this;
                    self.ajaxSubmit(form);
                },
                /**
                 * @param {String} form
                 */
                disableAddToQuoteButton: function (form) {
                    var addToQuoteButtonTextWhileAdding = this.options.addToQuoteButtonTextWhileAdding || $t('Adding...'),
                    addToQuoteButton = $(form).find('.submit_button');

                    addToQuoteButton.addClass(this.options.addToQuoteButtonDisabledClass);
                    addToQuoteButton.find('span').text(addToQuoteButtonTextWhileAdding);
                    addToQuoteButton.attr('title', addToQuoteButtonTextWhileAdding);
                },
                /**
                 * @param {String} form
                 */
                ajaxSubmit: function (form) {
                    var self = this;
                    var formEle = document.forms.namedItem("mpquotesystem_quote_add_cat");
                    self.disableAddToQuoteButton(form);

                    $.ajax(
                        {
                            url: form.attr('action'),
                            type: 'post',
                            dataType: 'json',
                            data: new FormData(formEle),
                            contentType: false,
                            cache: false,
                            processData:false,

                            /**
                             * @inheritdoc 
                             */
                            beforeSend: function () {
                                $('body').trigger('processStart');
                            },

                            /**
                             * @inheritdoc 
                             */
                            success: function (res) {
                                var eventData, parameters;
                                $('body').trigger('processStop');
                                self.enableaddToQuoteButton(form);
                                $(form)[0].reset();
                                $(self.options.addQuoteModel).modal('closeModal');
                            }
                        }
                    );
                },
                /**
                 * @param {String} form
                 */
                enableaddToQuoteButton: function (form) {
                    var addToQuoteButtonTextAdded = this.options.addToQuoteButtonTextAdded || $t('Added'),
                    self = this,
                    addToQuoteButton = $(form).find('.submit_button');

                    addToQuoteButton.find('span').text(addToQuoteButtonTextAdded);
                    addToQuoteButton.attr('title', addToQuoteButtonTextAdded);

                    setTimeout(
                        function () {
                            var addToQuoteButtonTextDefault = self.options.addToQuoteButtonTextDefault || $t('Add to Quote');

                            addToQuoteButton.removeClass(self.options.addToCartButtonDisabledClass);
                            addToQuoteButton.find('span').text(addToQuoteButtonTextDefault);
                            addToQuoteButton.attr('title', addToQuoteButtonTextDefault);
                        }, 1000
                    );
                },
                addQuoteFormToPage: function (element) {
                    var self = this;
                    var options = {
                        type: 'popup',
                        responsive: true,
                        innerScroll: true,
                        validation:{},
                        title: "Enter Quote details", //write your popup title
                        buttons: [
                        {
                            text: $.mage.__('Submit'),
                            class: 'button',
                            click: function () {
                                var form = $('#mpquotesystem_quote_add_cat');
                                if ($(form).validation() && $(form).validation('isValid')) {
                                    self.categorySubmitQuote(form);
                                } else {
                                    $('.mage-error').hide();
                                    if ($('input:text[name=DrugDurationLength]').val()) {
                                        $('.wk_option_select_message').show();
                                    }
                                    $('.fieldset .mage-error').show();
                                    $('.quote_submit_fields').show();
                                }
                            }
                        },
                        {
                            text: $.mage.__('Reset'),
                            class: 'reset',
                            click: function () {
                                var form = $('#mpquotesystem_quote_add_cat');
                                $(form)[0].reset();
                                $('#quote_qty-error').hide();
                                $('#quote_qty').removeClass('mage-error');
                            }
                        }
                        ]
                    };
                    // manage current product details
                    var parentelement = $(element).parents('.product-item-details').find('form[data-role="tocart-form"]');
                    $(parentelement).find('input[type="hidden"], input[type="text"]').each(
                        function () {
                            var elem = $(this).clone();
                            var elemValue = $(this).val();
                            var elemName = $(elem).attr('name');
                            if ($(self.options.popoverbackgroundhtml).find('input[name="'+elemName+'"]').length) {
                                $(self.options.popoverbackgroundhtml).find('input[name="'+elemName+'"]').remove();
                                $(self.options.popoverbackgroundhtml).find('form').append(elem);
                                $(elem).attr('value',elemValue);
                            } else {
                                $(self.options.popoverbackgroundhtml).find('form').append(elem);
                                $(elem).attr('value',elemValue);
                            }
                        }
                    );
                    var proName = $.trim($(parentelement).parents('.product-item-details').find('.product.name.product-item-name a').text());

                    var productId = $(element).attr('data-product-id');
                    $(self.options.popoverbackgroundhtml).find('input[name="product_name"]').attr('value', proName);
                    $(self.options.popoverbackgroundhtml).find('input[name="product"]').attr('value', productId);
                    $(self.options.popoverbackgroundhtml).find('.wk-qs-min-qty').html($t('Minimum quote quantity limit is ')+$(element).attr('data-qty'));
                    $(self.options.popoverbackgroundhtml).find('#quote_qty').addClass('validate-digits-range digits-range-'+$(element).attr('data-qty')+'-');
                    self.options.addQuoteModel = $(self.options.popoverbackgroundhtml);
                    modal(options, $(self.options.addQuoteModel));
                    $(self.options.addQuoteModel).modal('openModal');
                }
            }
        );
        return $.mage.WkMpQuotesystem;
    }
);
