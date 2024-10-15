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
    'mage/translate',
    'ko',
    'Webkul_MpVendorAttributeManager/js/model/group'
], function ($, Component, validation,$t, ko, groupModel) {
        'use strict';
        var vendorConfig = window.vendorConfig;
        var groups = window.vendorConfig.groups;
        var is_attribute_assigned_to_any_customer = window.vendorConfig.is_attribute_assigned_to_any_customer;
        var is_attribute_assigned_to_any_seller = window.vendorConfig.is_attribute_assigned_to_any_seller;
        var allGroups = ko.observableArray([]);
        return Component.extend({
            defaults: {
                template: 'Webkul_MpVendorAttributeManager/view/attribute-group'
            },
            selectedChoice: ko.observable(),
            initialize: function () {
                this._super();
                this._createGroup();
                this.selectedChoice.subscribe(function (selected) {
                    groupModel.setGroup(selected);
                }, this);
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
                        $('<div>').html('Invalid Extension. Allowed extensions are '+$(this).attr("data-allowed"))
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
            _createGroup: function () {
                var self = this;
                $.each(groups, function (i, v) {
                    allGroups.push(v);
                });
            },

            hasGroups: function () {
                if (window.isVendorForm == 0) {
                    if (!is_attribute_assigned_to_any_customer) {
                        return 0;
                    }
                } else {
                    if (!is_attribute_assigned_to_any_seller) {
                        return 0;
                    }
                }

                return groups.length;
            },
            getGroups: function () {
                return allGroups;
            },
            getLabel: function () {
                if (window.isVendorForm == 0) {
                    return $.mage.__("Select Customer Group");
                } else {
                    return $.mage.__("Select Vendor Group");
                }
            },
            getCaption: function () {
                return $.mage.__("Choose...");
            }
        });
    });
