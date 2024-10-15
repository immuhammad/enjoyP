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
define(
    [
        'ko'
    ],
    function (ko) {
        'use strict';
        return {
            selectedGroup: ko.observable(),

            /**
             * @return {Function}
             */
            getGroup: function () {
                return this.selectedGroup();
            },
            /**
             * @return {Function}
             */
            setGroup: function (group) {
                return this.selectedGroup(group);
            },

        };
    }
);
