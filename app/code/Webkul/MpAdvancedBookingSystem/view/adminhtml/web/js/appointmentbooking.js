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
        'moment',
        "mage/calendar"
    ], function ($, alert, mageTemplate, moment) {
        'use strict';
        $.widget(
            'appointmentbooking.appointmentbooking',
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
                        $('#wk-available-every-week').trigger('click');
                    }
                    if (slotDataType) {
                        $('#wk-slot-all-days').trigger('click');
                    }
                    if (slotHasQuantity) {
                        $('#wk-slot-has-quantity').trigger('click');
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

                    $("body").on('change','.wk-booking-slot-picker', function () {
                        
                        if ($(this).attr('name') && $(this).attr('name').endsWith('[from]')) {
                            let fromCurEl = $(this);
                            if ($(this).siblings('.time-error').length) {
                                $(this).siblings('.time-error').remove();
                            }
                            let toCurEl = $(this).parents('.wk-booking-slot-block').find("[name$='[to]']");
                            let fromCurVal = moment(fromCurEl.val(), "hh:mm a").unix();
                            let toCurVal = moment(toCurEl.val(), "hh:mm a").unix();
                            if (toCurEl.val() && fromCurVal >= toCurVal) {
                                $(this).parent().append("<div class='time-error'><p class='admin__field-error'>Invalid slot time.<p></div>");
                                $(this).val("");
                                return;
                            }
                            let slot_blocks = $(this).parents('.wk-booking-slot-container').find('.wk-booking-slot-block');
                            slot_blocks.each(function () {
                            let fromEl = $(this).find("[name$='[from]']");
                            let toEl = $(this).find("[name$='[to]']");
                            let fromVal = moment(fromEl.val(), "hh:mm a").unix();
                            let toVal = moment(toEl.val(), "hh:mm a").unix();
                            if (!fromEl.is(fromCurEl) && fromEl.val()) {
                                if (fromCurEl.val() == fromEl.val() || (toEl.val() &&
                                    fromCurVal > fromVal && fromCurVal <= toVal)
                                ) {
                                    fromCurEl.parent().append("<div class='time-error'><p class='admin__field-error'>Invalid slot time.<p></div>");
                                    fromCurEl.val("");
                                }
                            }
                            });
                        } else {
                            let toCurEl = $(this);
                            if ($(this).siblings('.time-error').length) {
                                $(this).siblings('.time-error').remove();
                            }
                            let fromCurEl = $(this).parents('.wk-booking-slot-block').find("[name$='[from]']");
                            let fromCurVal = moment(fromCurEl.val(), "hh:mm a").unix();
                            let toCurVal = moment(toCurEl.val(), "hh:mm a").unix();
                            if (fromCurEl.val() && fromCurVal >= toCurVal) {
                                $(this).parent().append("<div class='time-error'><p class='admin__field-error'>Invalid slot time.<p></div>");
                                $(this).val("");
                                return;
                            }
                            let slot_blocks = $(this).parents('.wk-booking-slot-container').find('.wk-booking-slot-block');
                            slot_blocks.each(function () {
                                let fromEl = $(this).find("[name$='[from]']");
                                let toEl = $(this).find("[name$='[to]']");
                                let fromVal = moment(fromEl.val(), "hh:mm a").unix();
                                let toVal = moment(toEl.val(), "hh:mm a").unix();
                                if (!toEl.is(toCurEl) && toEl.val()) {
                                    if (toCurEl.val() == toEl.val() || (fromEl.val() && 
                                    toCurVal >= fromVal && toCurVal <= toVal)
                                    ) {
                                        toCurEl.parent().append("<div class='time-error'><p class='admin__field-error'>Invalid slot time.<p></div>");
                                        toCurEl.val("");
                                    }
                                }
                            });
                        }
                    });

                    $('body').on('click', '.wk-booking-row-add-btn', function (event, type) {
                        let slotContEl = $(this).parent().siblings('.wk-booking-slot-container');
                        let slotBlockEl = slotContEl.find('.wk-booking-slot-block');
                        if (slotBlockEl.length > 0) {
                            let lastSlotBlock = slotBlockEl.last();
                            if (!lastSlotBlock.find("[name$='[from]']").val() || !lastSlotBlock.find("[name$='[to]']").val()) {
                                alert({
                                    content: "Please fill the current slot time's first."
                                });
                                return;
                            }
                        }
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
        return $.appointmentbooking.appointmentbooking;
    });
