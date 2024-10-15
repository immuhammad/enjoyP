/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MultiCoupon
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

 define(['jquery'], function($) {
    $.widget('items', {
        _create: function () {
            console.log("check");
            if (self.options.serviceFee > 0)
            $(".wk-grandtotal-divider .price .price").html(self.options.reducedTotal);
        }
    });
    return $.items;
});