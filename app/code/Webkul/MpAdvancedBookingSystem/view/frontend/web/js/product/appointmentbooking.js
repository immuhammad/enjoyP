/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvancedBookingSystem
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
        "jquery",
        'Magento_Ui/js/modal/alert',
        'mage/template',
        "mage/calendar"
    ], function ($, alert, mageTemplate) {
        'use strict';
        $.widget(
            'mpappointmentbooking.mpappointmentbooking',
            {
                options: {
                },
                _create: function () {
                    var self = this;
                    var slotType1Index = 0;
                    var slotType2Index = 0;
                    var slotType3Index = 0;
                    var slotType4Index = 0;
                    var slotType5Index = 0;
                    var slotType6Index = 0;
                    var slotType7Index = 0;
                    var isSavedDataAdded = {1:0, 2:0, 3:0, 4:0, 5:0, 6:0, 7:0};
                    var availableEveryWeek = parseInt(self.options.availableEveryWeek);
                    var slotHasQuantity = parseInt(self.options.slotHasQuantity);
                    var slotDataType = parseInt(self.options.slotDataType);
                    var slotData = JSON.parse(self.options.slotData);
                    var showMapLocation = parseInt(self.options.showMapLocation);
                    var currentSlotType = '';
                    $(".wk-booking-date-block").dateRange({
                        'dateFormat':'mm/dd/yy',
                        'minDate': 'today',
                        'from': {
                            'id': 'wk-booking-available-from'
                        },
                        'to': {
                            'id': 'wk-booking-available-to'
                        },
                        'showButtonPanel': true,
                        'showOn': "button",
                        'buttonImage': null,
                        'buttonImageOnly': null,
                        'buttonText': ''
                    });

                    if (availableEveryWeek) {
                        if ($('body').find('input#avail-every-week-hide').length) {
                            $('body').find('input#avail-every-week-hide').remove();
                        }
                        $('#wk-available-every-week').trigger('click');
                    } else {
                        $('#wk-booking-available-from').addClass('required-entry');
                        $('#wk-booking-available-to').addClass('required-entry');
                        $('.wk-booking-date-block').show();
                        if ($('body').find('input#avail-every-week-hide').length) {
                            $('body').find('input#avail-every-week-hide').val(0);
                        } else {
                            $('#wk-available-every-week').after(
                                $("<input>").attr('type','hidden').attr('id','avail-every-week-hide').attr('name','product[available_every_week]').val(0)
                            );
                        }
                    }

                    if (showMapLocation) {
                        if ($('body').find('input#wk-show-map-loction-hide').length) {
                            $('body').find('input#wk-show-map-loction-hide').remove();
                        }
                        $('#wk-show-map-loction').trigger('click');
                    } else {
                        if ($('body').find('input#wk-show-map-loction-hide').length) {
                            $('body').find('input#wk-show-map-loction-hide').val(0);
                        } else {
                            $('#wk-show-map-loction').after(
                                $("<input>").attr('type','hidden').attr('id','wk-show-map-loction-hide').attr('name','product[show_map_loction]').val(0)
                            );
                        }
                    }

                    if (slotDataType) {
                        if ($('body').find('input#wk-slot-all-days-hide').length) {
                            $('body').find('input#wk-slot-all-days-hide').remove();
                        }
                        $('#wk-slot-all-days').trigger('click');
                    } else {
                        if ($('body').find('input#wk-slot-all-days-hide').length) {
                            $('body').find('input#wk-slot-all-days-hide').val(0);
                        } else {
                            $('#wk-slot-all-days').after(
                                $("<input>").attr('type','hidden').attr('id','wk-slot-all-days-hide').attr('name','product[slot_for_all_days]').val(0)
                            );
                        }
                    }

                    if (slotHasQuantity) {
                        if ($('body').find('input#wk-slot-has-quantity-hide').length) {
                            $('body').find('input#wk-slot-has-quantity-hide').remove();
                        }
                        $('#wk-slot-has-quantity').trigger('click');
                    } else {
                        if ($('body').find('input#wk-slot-has-quantity-hide').length) {
                            $('body').find('input#wk-slot-has-quantity-hide').val(0);
                        } else {
                            $('#wk-slot-has-quantity').after(
                                $("<input>").attr('type','hidden').attr('id','wk-slot-has-quantity-hide').attr('name','product[slot_has_quantity]').val(0)
                            );
                        }
                    }

                    $('body').on('change', '#wk-show-map-loction', function () {
                        if ($(this).prop('checked')) {
                            if ($('body').find('input#wk-show-map-loction-hide').length) {
                                $('body').find('input#wk-show-map-loction-hide').remove();
                            }
                        } else {
                            if ($('body').find('input#wk-show-map-loction-hide').length) {
                                $('body').find('input#wk-show-map-loction-hide').val(0);
                            } else {
                                $('#wk-show-map-loction').after(
                                    $("<input>").attr('type','hidden').attr('id','wk-show-map-loction-hide').attr('name','product[show_map_loction]').val(0)
                                );
                            }
                        }
                    });

                    $('body').on('change', '#wk-available-every-week', function () {
                        if ($(this).prop('checked')) {
                            $('.wk-booking-date-block').hide();
                            $('#wk-booking-available-from').removeClass('required-entry');
                            $('#wk-booking-available-to').removeClass('required-entry');
                            if ($('body').find('input#avail-every-week-hide').length) {
                                $('body').find('input#avail-every-week-hide').remove();
                            }
                        } else {
                            $('#wk-booking-available-from').addClass('required-entry');
                            $('#wk-booking-available-to').addClass('required-entry');
                            $('.wk-booking-date-block').show();
                            if ($('body').find('input#avail-every-week-hide').length) {
                                $('body').find('input#avail-every-week-hide').val(0);
                            } else {
                                $('#wk-available-every-week').after(
                                    $("<input>").attr('type','hidden').attr('id','avail-every-week-hide').attr('name','product[available_every_week]').val(0)
                                );
                            }
                        }
                    });
                    $('#wk-slot-all-days').each(function () {
                        if ($(this).prop('checked')) {
                            var progressTmpl = mageTemplate('#wk-booking-same-slot-container-template'),
                            tmpl;
                            tmpl = progressTmpl({
                                data: {}
                            });
                            currentSlotType = 1;
                        } else {
                            var progressTmpl = mageTemplate('#wk-booking-slot-container-template'),
                            tmpl;
                            tmpl = progressTmpl({
                                data: {}
                            });
                            currentSlotType = 0;
                        }
                        $('#wk-slot-has-quantity-row').after(tmpl);
                    });

                    $('body').on('change', '#wk-slot-all-days', function () {
                        isSavedDataAdded = {1:0, 2:0, 3:0, 4:0, 5:0, 6:0, 7:0};
                        $('.wk-booking-slot-row').remove();
                        if ($(this).prop('checked')) {
                            var progressTmpl = mageTemplate('#wk-booking-same-slot-container-template'),
                            tmpl;
                            tmpl = progressTmpl({
                                data: {}
                            });
                            currentSlotType = 1;
                            if ($('body').find('input#wk-slot-all-days-hide').length) {
                                $('body').find('input#wk-slot-all-days-hide').remove();
                            }
                        } else {
                            var progressTmpl = mageTemplate('#wk-booking-slot-container-template'),
                            tmpl;
                            tmpl = progressTmpl({
                                data: {}
                            });
                            currentSlotType = 0;
                            if ($('body').find('input#wk-slot-all-days-hide').length) {
                                $('body').find('input#wk-slot-all-days-hide').val(0);
                            } else {
                                $('#wk-slot-all-days').after(
                                    $("<input>").attr('type','hidden').attr('id','wk-slot-all-days-hide').attr('name','product[slot_for_all_days]').val(0)
                                );
                            }
                        }
                        $('#wk-slot-has-quantity-row').after(tmpl);
                        $('.wk-booking-row-add-btn').trigger('click', ["auto"]);
                    });

                    $('body').on('click', '.wk-booking-row-add-btn', function (event, type) {
                        var slotTypeId = $(this).attr('data-day-type');
                        var wkQtyClass = 'wk-bk-hide';
                        var wkQtyFieldClass = '';
                        $('#wk-slot-has-quantity').each(function () {
                            if ($(this).prop('checked')) {
                                wkQtyClass = '_required';
                                wkQtyFieldClass = 'required-entry validate-digits';
                            }
                        });
                        var isNewRow = 1;
                        var slotDataNotAvl = false;
                        if (!isSavedDataAdded[slotTypeId] && currentSlotType==slotDataType) {
                            if (slotData[slotTypeId]) {
                                $.each(slotData[slotTypeId], function (dayindex, dayvalue) {
                                    var index = slotType1Index;
                                    if (slotTypeId == 1) {
                                        slotType1Index = slotType1Index + 1;
                                    }
                                    if (slotTypeId == 2) {
                                        index = slotType2Index;
                                        slotType2Index = slotType2Index + 1;
                                    }
                                    if (slotTypeId == 3) {
                                        index = slotType3Index;
                                        slotType3Index = slotType3Index + 1;
                                    }
                                    if (slotTypeId == 4) {
                                        index = slotType4Index;
                                        slotType4Index = slotType4Index + 1;
                                    }
                                    if (slotTypeId == 5) {
                                        index = slotType5Index;
                                        slotType5Index = slotType5Index + 1;
                                    }
                                    if (slotTypeId == 6) {
                                        index = slotType6Index;
                                        slotType6Index = slotType6Index + 1;
                                    }
                                    if (slotTypeId == 7) {
                                        index = slotType7Index;
                                        slotType7Index = slotType7Index + 1;
                                    }
                                    var progressTmpl = mageTemplate('#wk-booking-slot-template'),
                                            tmpl;
                                    tmpl = progressTmpl({
                                        data: {
                                            id: slotTypeId,
                                            index: index,
                                            from: dayvalue.from,
                                            to: dayvalue.to,
                                            qty: dayvalue.qty,
                                            qtyclass: wkQtyClass,
                                            qtyfieldclass: wkQtyFieldClass
                                        }
                                    });
                                    $('#wk-booking-slot-container'+slotTypeId).append(tmpl);
                                    $(".wk-booking-slot-picker").timepicker({
                                        timeFormat: 'hh:mm tt',
                                        controlType: 'select',
                                        'showButtonPanel': true,
                                        'showOn': "button",
                                        'buttonImage': null,
                                        'buttonImageOnly': null,
                                        'buttonText': ''
                                    });
                                    // for label row slot box
                                    var progressTmpl = mageTemplate('#wk-booking-label-slot-template'),
                                            tmpl;
                                    tmpl = progressTmpl({
                                        data: {
                                            id: slotTypeId,
                                            index: index,
                                            from: dayvalue.from,
                                            to: dayvalue.to,
                                            qty: dayvalue.qty
                                        }
                                    });
                                    $('#wk-bk-label-slot-box-container'+slotTypeId).append(tmpl);
                                    if (!$('#wk-slot-has-quantity').prop('checked')) {
                                        $('#wk-bk-label-slot-box-container'+slotTypeId).find('.wk-bk-label-slot-box').find('span.wk-span-label-qty').hide();
                                    }
                                    isSavedDataAdded[slotTypeId] = 1;
                                    isNewRow = 0;
                                });
                            } else if (slotTypeId == 1 && slotDataType==0 && type=="auto") {
                                slotDataNotAvl = true;
                            }
                        }
                        if ((type!='auto' && isNewRow) || (type=='auto' && slotTypeId == 1 && isNewRow)) {
                            var index = slotType1Index;
                            if (slotTypeId == 1) {
                                slotType1Index = slotType1Index + 1;
                            }
                            if (slotTypeId == 2) {
                                index = slotType2Index;
                                slotType2Index = slotType2Index + 1;
                            }
                            if (slotTypeId == 3) {
                                index = slotType3Index;
                                slotType3Index = slotType3Index + 1;
                            }
                            if (slotTypeId == 4) {
                                index = slotType4Index;
                                slotType4Index = slotType4Index + 1;
                            }
                            if (slotTypeId == 5) {
                                index = slotType5Index;
                                slotType5Index = slotType5Index + 1;
                            }
                            if (slotTypeId == 6) {
                                index = slotType6Index;
                                slotType6Index = slotType6Index + 1;
                            }
                            if (slotTypeId == 7) {
                                index = slotType7Index;
                                slotType7Index = slotType7Index + 1;
                            }
                            
                            if (!slotDataNotAvl) {
                                var progressTmpl = mageTemplate('#wk-booking-slot-template'),
                                        tmpl;
                                tmpl = progressTmpl({
                                    data: {
                                        id: slotTypeId,
                                        index: index,
                                        qtyclass: wkQtyClass,
                                        qtyfieldclass: wkQtyFieldClass
                                    }
                                });
                                $('#wk-booking-slot-container'+slotTypeId).append(tmpl);
                                $(".wk-booking-slot-picker").timepicker({
                                    timeFormat: 'hh:mm tt',
                                    controlType: 'select',
                                    'showButtonPanel': true,
                                    'showOn': "button",
                                    'buttonImage': null,
                                    'buttonImageOnly': null,
                                    'buttonText': ''
                                });
                            }
                        }
                        if (!$('#wk-booking-slot-container'+slotTypeId).find('.wk-booking-slot-block').length) {
                            $('#wk-booking-slot-container'+slotTypeId)
                                .parents('.wk-booking-slot-row')
                                .find('.wk-booking-slot-container-label')
                                .addClass('wk-booking-slot-closed');
                        } else {
                            $('#wk-booking-slot-container'+slotTypeId)
                                .parents('.wk-booking-slot-row')
                                .find('.wk-booking-slot-container-label')
                                .removeClass('wk-booking-slot-closed');
                        }
                    });

                    $('body').on('click', '.wk-booking-row-remove-btn', function () {
                        var slotTypeId = $(this)
                            .parents('.wk-booking-slot-row')
                            .find('.wk-booking-row-add-btn')
                            .attr('data-day-type');

                        var id = $(this).parents('.wk-booking-slot-block').attr('id');
                        var idArr = id.split('wk-booking-slot-block');
                        var index = idArr[1];
                        $('#wk-bk-label-slot-box'+index).remove();

                        $(this).parents('.wk-booking-slot-block').remove();
                        if (!$('#wk-booking-slot-container'+slotTypeId).find('.wk-booking-slot-block').length) {
                            $('#wk-booking-slot-container'+slotTypeId)
                                .parents('.wk-booking-slot-row')
                                .find('.wk-booking-slot-container-label')
                                .addClass('wk-booking-slot-closed');
                        }
                    });

                    $('.wk-booking-row-add-btn').trigger('click', ["auto"]);

                    $(".wk-booking-slot-picker").timepicker({
                        timeFormat: 'hh:mm tt',
                        'showButtonPanel': true,
                        'showOn': "button",
                        'buttonImage': null,
                        'buttonImageOnly': null,
                        'buttonText': ''
                    });

                    $('body').on('click', '.wk-booking-slot-container-label', function () {
                        $(this).parents('.field').find('.collapsible-content').toggle(
                            "fast",
                            function () {
                                if ($(this).hasClass('_show')) {
                                    $(this).addClass('wk-bk-hide');
                                    $(this).removeClass('_show');
                                    $(this).parents('.wk-booking-slot-row').removeClass('_show');
                                    $(this)
                                    .parents('.wk-booking-slot-row')
                                    .find('.wk-bk-label-slot-box-container')
                                    .removeClass('wk-bk-hide')
                                    .addClass('_show');
                                } else {
                                    $(this).removeClass('wk-bk-hide');
                                    $(this).addClass('_show');
                                    $(this).parents('.wk-booking-slot-row').addClass('_show');
                                    $(this)
                                    .parents('.wk-booking-slot-row')
                                    .find('.wk-bk-label-slot-box-container')
                                    .removeClass('_show')
                                    .addClass('wk-bk-hide');
                                }
                            }
                        );
                    });

                    $('body').on('change', '#wk-slot-has-quantity', function () {
                        if ($(this).prop('checked')) {
                            $('.wk-booking-row-field-qty').addClass('_required');
                            $('.wk-booking-row-field-qty').find('.wk-booking-qty-field').addClass('required-entry');
                            $('.wk-booking-row-field-qty').find('.wk-booking-qty-field').addClass('validate-digits');
                            $('.wk-booking-row-field-qty').removeClass('wk-bk-hide');
                            if ($('body').find('input#wk-slot-has-quantity-hide').length) {
                                $('body').find('input#wk-slot-has-quantity-hide').remove();
                            }

                            $('.wk-bk-label-slot-box').find('span.wk-span-label-qty').show();
                        } else {
                            $('.wk-booking-row-field-qty').addClass('wk-bk-hide');
                            $('.wk-booking-row-field-qty').removeClass('_required');
                            $('.wk-booking-row-field-qty').find('.wk-booking-qty-field').removeClass('required-entry');
                            $('.wk-booking-row-field-qty').find('.wk-booking-qty-field').removeClass('validate-digits');
                            if ($('body').find('input#wk-slot-has-quantity-hide').length) {
                                $('body').find('input#wk-slot-has-quantity-hide').val(0);
                            } else {
                                $('#wk-slot-has-quantity').after(
                                    $("<input>").attr('type','hidden').attr('id','wk-slot-has-quantity-hide').attr('name','product[slot_has_quantity]').val(0)
                                );
                            }

                            $('.wk-bk-label-slot-box').find('span.wk-span-label-qty').hide();
                        }
                    });
                    $('body').on('click', '.wk-bk-label-slot-box-close', function () {
                        var slotTypeId = $(this)
                            .parents('.wk-booking-slot-row')
                            .find('.wk-booking-row-add-btn')
                            .attr('data-day-type');
                        var id = $(this).parents('.wk-bk-label-slot-box').attr('id');
                        var idArr = id.split('wk-bk-label-slot-box');
                        var index = idArr[1];
                        $(this).parents('.wk-bk-label-slot-box').remove();
                        $('#wk-booking-slot-block'+index).remove();
                        if (!$('#wk-booking-slot-container'+slotTypeId).find('.wk-booking-slot-block').length) {
                            $('#wk-booking-slot-container'+slotTypeId)
                                .parents('.wk-booking-slot-row')
                                .find('.wk-booking-slot-container-label')
                                .addClass('wk-booking-slot-closed');
                        }
                    });
                }
            }
        );
        return $.mpappointmentbooking.mpappointmentbooking;
    });
