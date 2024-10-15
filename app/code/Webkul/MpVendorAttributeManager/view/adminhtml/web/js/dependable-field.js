/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpVendorAttributeManager
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint jquery:true*/
define([
        "jquery",
        'mage/translate',
        "mage/template",
        "mage/mage",
        "mage/calendar",
    ], function ($, $t,mageTemplate, alert) {
        'use strict';
        $.widget('mage.dependableField', {

            options: {
                optionTemp : '',
                editorTamplate: '#wysiwyg_editor_template',
                allowedExtensionTmp : '#allowed_extension_template',
                tabMainContent : '#vendor_attribute_tabs_main_content',
                customFiledOption : '.customfield_options',
                baseFieldSelector : '#customfields_base_fieldset',
                frontEndClass : '.field-frontend_class',
                isRequired : '.field-is_required',
                frontEndInput: '#customfields_frontend_input',
                wysiwygOption: '#customfields_default_value'
            },
            _create: function () {
                var self = this;
                this.options.optionTemp = $(self.options.tabMainContent).find(self.options.customFiledOption);
                $(self.options.tabMainContent).find(self.options.customFiledOption).hide();

                if (self.options.codeSignal == 1) {
                    $(self.options.baseFieldSelector).find("#customfields_attribute_code").attr('disabled','true');
                    $(self.options.baseFieldSelector).find(self.options.frontEndInput).attr('disabled','true');
                    $(self.options.baseFieldSelector).find(self.options.frontEndClass).show();
                    $(self.options.baseFieldSelector).find(self.options.isRequired).show();
                }
                if (self.options.fileSignal == "1") {
                    $(self.options.baseFieldSelector).find(self.options.frontEndClass).hide();
                    $(self.options.baseFieldSelector).find(self.options.isRequired).show();
                }
                if (self.options.booleanSignal == "1") {
                    $(self.options.baseFieldSelector).find("#customfields_frontend_class").attr('disabled','true');
                    $(self.options.baseFieldSelector).find(self.options.isRequired).show();
                }
                if (self.options.selectSignal == "1") {
                    $(self.options.tabMainContent).find(self.options.customFiledOption).show();
                    $(self.options.baseFieldSelector).find("#customfields_frontend_class").attr('disabled','true');
                }
                if (self.options.textareaSignal == "1") {
                    $(self.options.baseFieldSelector).find("#customfields_frontend_class").attr('disabled','true');
                    var progressTmpl = mageTemplate(self.options.editorTamplate),tmpl;
                    tmpl = progressTmpl({
                        data: {}
                    });
                    $('.field-frontend_input').after(tmpl);
                    
                    var wysiwygValue = $(self.options.wysiwygOption).find(":selected").val();
                    if (wysiwygValue == 1) {
                        $(self.options.baseFieldSelector).find("#customfields_is_required").val(0).prop('disabled',true);
                    } else {
                        $(self.options.baseFieldSelector).find("#customfields_is_required").prop('disabled',false);
                    }
                }
                $(self.options.frontEndInput).on('change', function () {
                    self._manageFields($(this));
                });
                //    customfields_is_required
                $(self.options.baseFieldSelector).on('change', self.options.wysiwygOption, function () {
                    var wysiwygValue = $(self.options.wysiwygOption).find(":selected").val();
                    if (wysiwygValue == 1) {
                        $(self.options.baseFieldSelector).find("#customfields_is_required").val(0).prop('disabled',true);
                    } else {
                        $(self.options.baseFieldSelector).find("#customfields_is_required").prop('disabled',false);
                    }
                });
                

            },
            _manageFields: function (thisval) {
                var self = this;
                $(thisval).parents(self.options.baseFieldSelector).find(".dependable_type_container").remove();
                $(thisval).parents(self.options.baseFieldSelector).find(".selectoption_type_container").remove();
                self.options.optionTemp.remove();
                $(thisval).parents(self.options.tabMainContent).find(".allowed_extensions_type_container").remove();
                $(thisval).parents(self.options.tabMainContent).find(".default_value_type_container").remove();

                if ($(thisval).val() == 'text') {
                    $(self.options.tabMainContent).find(self.options.customFiledOption).hide();
                    $(thisval).parents(self.options.baseFieldSelector).find(self.options.frontEndClass).show();
                    $(thisval).parents(self.options.baseFieldSelector).find(".field-is_required").show();
                    $(thisval).parents(self.options.baseFieldSelector).find("#customfields_frontend_class").removeAttr('disabled');
                }
                if ($(thisval).val() == 'textarea') {
                    self.options.optionTemp.remove();
                    $(thisval).parents(self.options.baseFieldSelector).find(self.options.frontEndClass).show();
                    $(thisval).parents(self.options.baseFieldSelector).find(".field-is_required").show();
                    $(thisval).parents(self.options.baseFieldSelector).find("#customfields_frontend_class").attr('disabled','true');
                    var progressTmpl = mageTemplate(self.options.editorTamplate),
                                  tmpl;
                            tmpl = progressTmpl({
                                data: {}
                            });
                            $('.field-frontend_input').after(tmpl);
                }
                if ($(thisval).val() == 'boolean') {
                    self.options.optionTemp.remove();
                    $(thisval).parents(self.options.baseFieldSelector).find(self.options.frontEndClass).show();
                    $(thisval).parents(self.options.baseFieldSelector).find(".field-is_required").show();
                    $(thisval).parents(self.options.baseFieldSelector).find("#customfields_frontend_class").attr('disabled','true');
                }
                if ($(thisval).val() == 'date') {
                    self.options.optionTemp.remove();
                    $(thisval).parents(self.options.baseFieldSelector).find(self.options.frontEndClass).show();
                    $(thisval).parents(self.options.baseFieldSelector).find(".field-is_required").show();
                    $(thisval).parents(self.options.baseFieldSelector).find("#customfields_frontend_class").attr('disabled','true');
                }
                if ($(thisval).val() == 'select' || $(thisval).val() == 'multiselect') {
                    $(thisval).parents(self.options.baseFieldSelector).find(self.options.frontEndClass).show();
                    $(thisval).parents(self.options.baseFieldSelector).find(".field-is_required").show();
                    $(thisval).parents(self.options.baseFieldSelector).find("#customfields_frontend_class").attr('disabled','true');
                    $(self.options.tabMainContent).find("#customfields_base_fieldset-wrapper").append(self.options.optionTemp);
                    self.options.optionTemp.show();
                }
                if ($(thisval).val() == 'file' || $(thisval).val() == 'image') {
                    $(thisval).parents(self.options.baseFieldSelector).find(self.options.frontEndClass).hide();
                    $(self.options.tabMainContent).find(self.options.customFiledOption).hide();
                }
            },
        });
        return $.mage.dependableField;
    });
