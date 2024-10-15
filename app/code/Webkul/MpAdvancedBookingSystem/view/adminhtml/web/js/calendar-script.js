/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_AdvancedBookingSystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
    "jquery",
    "underscore",
    "Magento_Catalog/js/price-utils",
    "Magento_Ui/js/modal/modal",
    "mage/template",
    "jquery/ui",
    "mage/translate"
    ], function ($, _, utils, modal, mageTemplate) {
        'use strict';
        $.widget('advancedbookingadmincalendar.view', {
            options: {},
            _create: function () {
                var self = this;
                $(document).ready(function () {
                    var calendarElementId = self.options.calendarElementId;
                    var localeElementId = self.options.localeElementId;
                    var eventsUrl = self.options.eventsUrl;
                    var orderUrl = self.options.orderUrl;
                    var locale = self.options.locale;
                    var currentDate = new Date();
                    var calendarElement = document.getElementById(calendarElementId);
                    var localeElement = document.getElementById(localeElementId);

                    var bookingViewModel = self.options.bookingViewModel;
                    var bookingViewModelTemplate = self.options.bookingViewModelTemplate;
                    var modelOptions = {
                        type: 'popup',
                        responsive: true,
                        innerScroll: true,
                        buttons: []
                    };

                    var popup = modal(modelOptions, $(bookingViewModel));
                    var progressTmpl = mageTemplate(bookingViewModelTemplate), tmpl;
                    var calendar = new FullCalendar.Calendar(calendarElement, {
                        headerToolbar: {
                            left: 'prevYear,prev,next,nextYear',
                            center: 'title',
                            right: 'today,dayGridMonth,timeGridWeek,timeGridDay,listMonth'
                        },
                        locale: locale,
                        navLinks: true,
                        nowIndicator: true,
                        now: currentDate.toISOString(),
                        views: {
                            agenda: {
                                eventLimit: 6
                            }
                        },
                        events: function(info, successCallback, failureCallback) {
                            $.ajax({
                                url: eventsUrl,
                                dataType: 'json',
                                data: {
                                    start: info.start.valueOf()/1000,
                                    end: info.end.valueOf()/1000
                                },
                                success: function(doc) {
                                    var eventsData = [];
                                    if (doc.events.length > 0) {
                                        eventsData = doc.events;
                                    }
                                    successCallback(eventsData);
                                }
                            });
                        },
                        eventClick: function(eventInfo) {
                            let data = {};
                            data.start = eventInfo.event.start;
                            data.end = eventInfo.event.end;
                            data.orderUrl = orderUrl+'order_id/'+eventInfo.event.extendedProps.orderId;
                            data.customerEmail = eventInfo.event.extendedProps.customerEmail;
                            data.incrementId = eventInfo.event.extendedProps.incrementId;
                            data.orderId = eventInfo.event.extendedProps.orderId;
                            data.status = eventInfo.event.extendedProps.status;
                            data.title = eventInfo.event.title;
                            showOrderModel(data, progressTmpl, tmpl);
                        }
                    });
                    let fullcalenderlocale = window.localStorage.getItem("fullcalender-locale");
                    if (fullcalenderlocale) {
                        calendar.setOption('locale', fullcalenderlocale);
                        locale = fullcalenderlocale;
                    }
                    calendar.render();
                    Array.prototype.push.apply(FullCalendar.globalLocales, window.locales);
                    initLocales(calendar, localeElement, locale);
                });

                function initLocales(calendar, localeElement, locale) {
                    // build the locale selector's options
                    let localeArr = [];
                    FullCalendar.globalLocales.forEach(function(localeCode) {
                        localeArr.push(localeCode.code);
                        var optionEl = document.createElement('option');
                        optionEl.value = localeCode.code;
                        optionEl.selected = localeCode.code == locale;
                        optionEl.innerText = localeCode.code;
                        localeElement.appendChild(optionEl);
                    });
                    calendar.setOption('locales', localeArr);

                    // when the selected option changes, dynamically change the calendar option
                    localeElement.addEventListener('change', function() {
                        if (this.value) {
                            calendar.setOption('locale', this.value);
                            window.localStorage.setItem("fullcalender-locale", this.value);
                        }
                    });
                }

                function showOrderModel(event, progressTmpl, tmpl) {
                    $(self.options.bookingViewModel).modal("openModal");
                    tmpl = progressTmpl({
                        data : event
                    });

                    $('#booking-view-model-template-container').empty().html(tmpl);
                }
            }
        });
        return $.advancedbookingadmincalendar.view;
    }
);
