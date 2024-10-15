/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvancedBookingSystem
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define(
    [
    "jquery"
    ],
    function ($) {
    "use strict";
      $.widget(
          'autofill.register',
          {
            _create: function () {
                var autocomplete;
                
                setTimeout(function () {
                  initAutocomplete();
                },1200);

                function initAutocomplete () {
                    let locationField = document.querySelector('#wk-booking-location');
                    autocomplete = new google.maps.places.Autocomplete(
                        /** @type {!HTMLInputElement} */(locationField),
                        {types: ['geocode']});
                }
            }

          }
        );
      return $.autofill.register;
});