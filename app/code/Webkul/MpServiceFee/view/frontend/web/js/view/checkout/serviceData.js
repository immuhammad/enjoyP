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
    $.widget('serviceData', {
        _create: function () {
            $(".wk-mp-btn").on("click", function(){
                var dataForm = $('#form-validate-fee');
                if (dataForm.validation('isValid')) {
                    $('body').trigger('processStart');
                }
            });
        }
    });
    return $.serviceData;
});