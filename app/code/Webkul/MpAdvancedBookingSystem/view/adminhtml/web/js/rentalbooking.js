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
        'rentalbooking.rentalbooking',
        {
            options: {},
            _create: function () {
                var self = this;
                var slotType1Index = 0;
                var slotType2Index = 0;
                var slotType3Index = 0;
                var slotType4Index = 0;
                var slotType5Index = 0;
                var slotType6Index = 0;
                var slotType7Index = 0;
                var isSavedDataAdded = {
                    1: 0,
                    2: 0,
                    3: 0,
                    4: 0,
                    5: 0,
                    6: 0,
                    7: 0
                };
                var availableEveryWeek = parseInt(self.options.availableEveryWeek);
                var slotHasQuantity = parseInt(self.options.slotHasQuantity);
                var slotDataType = parseInt(self.options.slotDataType);
                var slotData = JSON.parse(self.options.slotData);
                var rentingType = parseInt(self.options.rentingType);
                var currentSlotType = '';
                $(".wk-booking-date-block").dateRange({
                    'dateFormat': 'mm/dd/yy',
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
                    $('#wk-available-every-week').trigger('click');
                }

                $('body').on('change', '#wk-available-every-week', function () {
                    if ($(this).prop('checked')) {
                        $('.wk-booking-date-block').hide();
                        $('#wk-booking-available-from').removeClass('required-entry');
                        $('#wk-booking-available-to').removeClass('required-entry');
                    } else {
                        $('#wk-booking-available-from').addClass('required-entry');
                        $('#wk-booking-available-to').addClass('required-entry');
                        $('.wk-booking-date-block').show();
                    }
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
                    if (!isSavedDataAdded[slotTypeId] && currentSlotType == slotDataType) {
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
                                $('#wk-booking-slot-container' + slotTypeId).append(tmpl);
                                $(".wk-booking-slot-picker").timepicker({
                                    timeFormat: 'hh:mm tt',
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
                                $('#wk-bk-label-slot-box-container' + slotTypeId).append(tmpl);

                                if (!$('#wk-slot-has-quantity').prop('checked')) {
                                    $('#wk-bk-label-slot-box-container' + slotTypeId).find('.wk-bk-label-slot-box').find('span.wk-span-label-qty').hide();
                                }
                                isSavedDataAdded[slotTypeId] = 1;
                                isNewRow = 0;
                            });
                        }
                    }
                    if ((type != 'auto' && isNewRow) || (type == 'auto' && slotTypeId == 1 && isNewRow)) {
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
                                qtyclass: wkQtyClass,
                                qtyfieldclass: wkQtyFieldClass
                            }
                        });
                        $('#wk-booking-slot-container' + slotTypeId).append(tmpl);
                        if (!$('#wk-slot-has-quantity').prop('checked')) {
                            $('#wk-bk-label-slot-box-container' + slotTypeId).find('.wk-bk-label-slot-box').find('span.wk-span-label-qty').hide();
                        }
                        $(".wk-booking-slot-picker").timepicker({
                            timeFormat: 'hh:mm tt',
                            'showButtonPanel': true,
                            'showOn': "button",
                            'buttonImage': null,
                            'buttonImageOnly': null,
                            'buttonText': ''
                        });
                    }
                    if (!$('#wk-booking-slot-container' + slotTypeId).find('.wk-booking-slot-block').length) {
                        $('#wk-booking-slot-container' + slotTypeId)
                            .parents('.wk-booking-slot-row')
                            .find('.wk-booking-slot-container-label')
                            .addClass('wk-booking-slot-closed');
                    } else {
                        $('#wk-booking-slot-container' + slotTypeId)
                            .parents('.wk-booking-slot-row')
                            .find('.wk-booking-slot-container-label')
                            .removeClass('wk-booking-slot-closed');
                    }
                });

                $('#wk-renting-type').each(function () {
                    var thisObj = $(this);
                    if (thisObj.val() != 1) {
                        var progressTmpl = mageTemplate(
                            '#wk-booking-hourly-booking-container-template'
                        ),
                            tmpl;
                        tmpl = progressTmpl({
                            data: {}
                        });
                        $('#wk-hourly-info-container').html(tmpl);
                        if (slotDataType) {
                            $('#wk-slot-all-days').trigger('click');
                        }
                        if (slotHasQuantity) {
                            $('#wk-slot-has-quantity').trigger('click');
                        }
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
                        $('.wk-booking-row-add-btn').trigger('click', ["auto"]);
                    } else {
                        $('#wk-hourly-info-container').html('');
                    }
                    if (thisObj.val() != 3) {
                        var progressTmpl = mageTemplate(
                            '#wk-booking-daily-booking-container-template'
                        ),
                            tmpl;
                        tmpl = progressTmpl({
                            data: {}
                        });
                        $('#wk-daily-info-container').html(tmpl);
                    } else {
                        $('#wk-daily-info-container').html('');
                    }
                });

                $('body').on('change', '#wk-renting-type', function () {
                    isSavedDataAdded = {
                        1: 0,
                        2: 0,
                        3: 0,
                        4: 0,
                        5: 0,
                        6: 0,
                        7: 0
                    };
                    var thisObj = $(this);
                    if (thisObj.val() != 1) {
                        var progressTmpl = mageTemplate(
                            '#wk-booking-hourly-booking-container-template'
                        ),
                            tmpl;
                        tmpl = progressTmpl({
                            data: {}
                        });
                        $('#wk-hourly-info-container').html(tmpl);
                        if (slotDataType) {
                            $('#wk-slot-all-days').trigger('click');
                        } else {
                            var progressTmpl = mageTemplate('#wk-booking-slot-container-template'),
                                tmpl;
                            tmpl = progressTmpl({
                                data: {}
                            });
                            currentSlotType = 0;
                            $('#wk-slot-has-quantity-row').after(tmpl);
                            $('.wk-booking-row-add-btn').trigger('click', ["auto"]);
                        }
                        if (slotHasQuantity) {
                            $('#wk-slot-has-quantity').trigger('click');
                        }
                    } else {
                        $('#wk-hourly-info-container').html('');
                    }
                    if (thisObj.val() != 3) {
                        var progressTmpl = mageTemplate(
                            '#wk-booking-daily-booking-container-template'
                        ),
                            tmpl;
                        tmpl = progressTmpl({
                            data: {}
                        });
                        $('#wk-daily-info-container').html(tmpl);
                    } else {
                        $('#wk-daily-info-container').html('');
                    }
                });

                $('body').on('change', '#wk-slot-all-days', function () {
                    isSavedDataAdded = {
                        1: 0,
                        2: 0,
                        3: 0,
                        4: 0,
                        5: 0,
                        6: 0,
                        7: 0
                    };
                    slotType1Index = slotType2Index = slotType3Index = slotType4Index = slotType5Index = 0;
                    slotType6Index = slotType7Index = 0;
                    $('.wk-booking-slot-row').remove();
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
                    $('.wk-booking-row-add-btn').trigger('click', ["auto"]);
                });

                $('body').on('click', '.wk-booking-row-remove-btn', function () {
                    var slotTypeId = $(this)
                        .parents('.wk-booking-slot-row')
                        .find('.wk-booking-row-add-btn')
                        .attr('data-day-type');

                    var id = $(this).parents('.wk-booking-slot-block').attr('id');
                    var idArr = id.split('wk-booking-slot-block');
                    var index = idArr[1];
                    $('#wk-bk-label-slot-box' + index).remove();

                    $(this).parents('.wk-booking-slot-block').remove();
                    if (!$('#wk-booking-slot-container' + slotTypeId).find('.wk-booking-slot-block').length) {
                        $('#wk-booking-slot-container' + slotTypeId)
                            .parents('.wk-booking-slot-row')
                            .find('.wk-booking-slot-container-label')
                            .addClass('wk-booking-slot-closed');
                    }
                });

                $('body').on('click', '.wk-booking-slot-container-label', function () {
                    $(this).parents('.admin__field').find('.admin__collapsible-content').toggle(
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
                        $('.wk-bk-label-slot-box').find('span.wk-span-label-qty').show();
                    } else {
                        $('.wk-booking-row-field-qty').addClass('wk-bk-hide');
                        $('.wk-booking-row-field-qty').removeClass('_required');
                        $('.wk-booking-row-field-qty').find('.wk-booking-qty-field').removeClass('required-entry');
                        $('.wk-booking-row-field-qty').find('.wk-booking-qty-field').removeClass('validate-digits');
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
                    $('#wk-booking-slot-block' + index).remove();
                    if (!$('#wk-booking-slot-container' + slotTypeId).find('.wk-booking-slot-block').length) {
                        $('#wk-booking-slot-container' + slotTypeId)
                            .parents('.wk-booking-slot-row')
                            .find('.wk-booking-slot-container-label')
                            .addClass('wk-booking-slot-closed');
                    }
                });
            }
        }
    );
    return $.rentalbooking.rentalbooking;
});