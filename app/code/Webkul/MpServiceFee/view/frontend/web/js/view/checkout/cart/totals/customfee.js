/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpServiceFee
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

define(
    [
        'Webkul_MpServiceFee/js/view/checkout/summary/customfee'
    ],
    function (Component) {
        'use strict';

        return Component.extend({
            /**
             * use to define amount is display setting
             */
            isDisplayed: function () {
              if (window.wkServiceFeeEnable == 1) {
                return true;
              } else {
                return false;
              }
            }
        });
    }
);
