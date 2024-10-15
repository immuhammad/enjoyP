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
    'uiComponent',
    'mage/validation',
    'ko',
    'Webkul_MpVendorAttributeManager/js/model/group',
    'mage/adminhtml/wysiwyg/tiny_mce/setup',
    'mage/calendar'
], function ($, Component, validation, ko, groupModel) {
    'use strict';
    var vendorConfig = window.vendorConfig;
    var attributes = window.vendorConfig.groups_attribute.groups;

    var allFields = ko.observableArray([]);
    var config = window.vendorConfig.editor_config;
    return Component.extend({
        defaults: {
            template: 'Webkul_MpVendorAttributeManager/view/attribute-group/attribute-fields'
        },
        allFields: ko.observableArray([]),
        initialize: function () {
            this._super();
            groupModel.selectedGroup.subscribe(function (group) {
                this.loadAttributeFields(group);
                this._refreshElements();
            }, this);
        },
        loadAttributeFields: function (group) {
            var self = this;
            self.allFields([]);
            var groupId = group;
            $.each(attributes, function (index, data) {
                if (groupId !== 'undefined' && groupId == index) {
                    $.each(data, function (i, v) {
                        if (window.isVendorForm == '1' && v.used_for == "1") {
                            return true;
                        }
                        if (window.isVendorForm == '0' && v.used_for == "2") {
                            return true;
                        }
                        self.allFields.push(v);
                        if (v.frontend_input == 'textarea') {
                        }
                    });
                }
            });
        },
        getGroupFields: function () {
            return allFields;
        },
        _refreshElements: function () {
            $(document).ready(function () {
                $.each($('.dob_type'), function () {
                    $(this).calendar({
                        dateFormat: "yyyy-MM-dd",
                        changeYear: false,
                        changeMonth: false,
                        yearRange: "-100:+100",
                        showOn: "both",
                        buttonText: ""
                    });
                });
                $.each($('body textarea'), function () {
                    if ($(this).hasClass('wysiwyg_enabled')) {
                        var id = $(this).attr('id');

                        var wysiwygEditor = new wysiwygSetup(id, {
                            "width":"100%",
                            "height":"200px",
                            "plugins":[{"name":"image"}],
                            "tinymce4":{"toolbar":"formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link table charmap","plugins":"advlist autolink lists link charmap media noneditable table contextmenu paste code help table",
                            },
                            files_browser_window_url: window.wysiwygUrl
                        });
                        wysiwygEditor.setup("exact");
                    }
                });
            });
        }
    });
});
