define([
    'jquery',
    'mage/template',
    'Magento_Ui/js/modal/alert',
    'jquery/ui',
    'mage/translate',
    'Magento_Ui/js/modal/modal',
    "mage/adminhtml/events",
    "mage/calendar",
    "mage/adminhtml/wysiwyg/tiny_mce/setup"
], function ($, mageTemplate, alert) {
    'use strict';

    $.widget('mage.displayVendorFields', {
        options: {
            formCount: 0,
            dataForm: $('#form-validate'),
        },

        _create: function () {
            var self = this;
            $('.fieldset.info').append($('.custom-fieldset'));
            $.each($(self.options.dateField), function (i, v) {
                $(this).calendar({
                    dateFormat: 'yyyy-MM-dd',
                    changeYear: false,
                    changeMonth: false,
                    showOn: "both",
                    buttonText: "",
                });
            });




            $('fieldset.form_fields_' + (self.options.fieldsetCount - 1)).append($('div.privacy-container'));
            $('.wk-list-container-registration .form-create-account').prepend($(".vendorfields"));
            $(document).ready(function () {
                $(".vendorfields textarea").each(function (index, field) {
                    var id = $(this).attr('id');
                    var isEnabled = $(this).attr('data-iswyswyg-enabled');
                    if (isEnabled) {
                        var wysiwygDescription = new wysiwygSetup(id, {
                            "width" : "100%",
                            "height" : "200px",
                            "plugins" : [{"name":"image"}],
                            "tinymce4" : {
                                "toolbar":"formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link table charmap",
                                "plugins":"advlist autolink lists link charmap media noneditable table contextmenu paste code help table",
                            },
                            files_browser_window_url: self.options.wysiwygUrl
                        });
                        wysiwygDescription.setup("exact");
                    }

                });
            });

            if ($('#wk-termsconditions-box').length) {
                $('#wk-termsconditions-box').remove();
            }
            if ($('#vendor-component').length) {
                $('.wk-list-container-registration .form-create-account').prepend($("#vendor-component"));
                $("#vendor-component").append($('div.privacy-container'));
            }

            $('.form-create-account .actions-toolbar .submit.primary').text($.mage.__('Next'));


            $('.form-create-account .actions-toolbar .submit.primary').on('click', function (event) {
                event.preventDefault();
                if ($('#vendor-component').length) {
                    self._nextGroupFields($(this));
                } else {
                    self._nextFields($(this));
                }
            });

            $('body').on('click', '.button.back', function (event) {
                event.preventDefault();
                if ($('#vendor-component').length) {
                    self._backGroupFields($(this));
                } else {
                    self._backFields($(this));
                }
            });
            //vendor image validation
            $('body').on('change', '.custom_file', function () {
                var ext_arr = $(this).attr("data-allowed").split(",");
                var new_ext_arr = [];
                for (var i = 0; i < ext_arr.length; i++) {
                    new_ext_arr.push(ext_arr[i]);
                    new_ext_arr.push(ext_arr[i].toUpperCase());
                }
                if (new_ext_arr.indexOf($(this).val().split("\\").pop().split(".").pop()) < 0) {
                    var self = $(this);
                    self.val('');
                    $('<div>').html('Invalid Extension. Allowed extensions are ' + $(this).attr("data-allowed"))
                        .modal({
                            title: 'Attention!',
                            autoOpen: true,
                            buttons: [{
                                text: 'Ok',
                                attr: {
                                    'data-action': 'cancel'
                                },
                                'class': 'action',
                                click: function () {
                                    self.val('');
                                    this.closeModal();
                                }
                            }]
                        });
                }
            });
        },
        _nextFields: function (button) {
            var self = this;
            if (button.hasClass('end-fields')) {
                $('.form-create-account').submit();
                return true;
            }
            $.each($('fieldset'), function () {
                $(this).css({ 'position': 'fixed', 'opacity': '0', 'z-index': '-9999', 'transition': 'opacity 0.1s ease-in-out' });
            });
            $('fieldset.form_fields_' + self.options.formCount).css({ 'position': 'relative', 'opacity': '1', 'z-index': '1', 'transition': 'opacity 0.6s ease-in-out' });
            button.text($.mage.__('Next'));
            self.options.formCount++;
            if (self.options.fieldsetCount === self.options.formCount) {
                button.text($.mage.__('Create Account'));
                button.addClass('end-fields');
            }
        },
        _backFields: function (button) {
            var self = this;
            $.each($('fieldset'), function () {
                $(this).css({ 'position': 'fixed', 'opacity': '0', 'z-index': '-9999', 'transition': 'opacity 0.1s ease-in-out' });
                if (button.attr('id') == 'main' && !$(this).hasClass('vendorfields')) {
                    $(this).css({ 'position': 'relative', 'opacity': '1', 'z-index': '1', 'transition': 'opacity 0.6s ease-in-out' });
                }
            });
            $('fieldset.' + button.attr('id')).css({ 'position': 'relative', 'opacity': '1', 'z-index': '1', 'transition': 'opacity 0.6s ease-in-out' });
            $('.form-create-account .actions-toolbar .submit.primary').removeClass('end-fields');
            $('.form-create-account .actions-toolbar .submit.primary').text($.mage.__('Next'));
            self.options.formCount--;
        },
        _nextGroupFields: function (button) {
            var self = this;
            if (button.hasClass('end-fields')) {
                $('.form-create-account').submit();
                return true;
            }
            $.each($('fieldset.create'), function () {
                $(this).css({ 'position': 'fixed', 'opacity': '0', 'z-index': '-9999', 'transition': 'opacity 0.1s ease-in-out' });
            });
            button.addClass('end-fields');
            $('#vendor-component').css({ 'position': 'relative', 'opacity': '1', 'z-index': '1', 'transition': 'opacity 0.6s ease-in-out' });
            button.text($.mage.__('Create Account'));
        },
        _backGroupFields: function (button) {
            var self = this;
            $.each($('fieldset.create'), function () {
                if (button.attr('id') == 'main' && !$(this).hasClass('vendorfields')) {
                    $(this).css({ 'position': 'relative', 'opacity': '1', 'z-index': '1', 'transition': 'opacity 0.6s ease-in-out' });
                }
            });
            $('.form-create-account .actions-toolbar .submit.primary').removeClass('end-fields');
            $('.form-create-account .actions-toolbar .submit.primary').text($.mage.__('Next'));
            $('#vendor-component').css({ 'position': 'fixed', 'opacity': '0', 'z-index': '-9999', 'transition': 'opacity 0.1s ease-in-out' });
        }
    });
    return $.mage.displayVendorFields;
});
