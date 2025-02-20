/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpVendorAttributeManager
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

/* global $, $H */

define([
    'mage/adminhtml/grid'
], function () {
    'use strict';

    return function (config) {
        var selectedAttributes = config.selectedAttributes,
            assignedAttributes = $H(selectedAttributes),
            gridJsObject = window[config.gridJsObjectName],
            trRowIndex;
        $('attr_ids').value = Object.toJSON(assignedAttributes);

        /**
         * Register Category Product
         *
         * @param {Object} grid
         * @param {Object} element
         * @param {Boolean} checked
         */
        function registerAssignedProduct(grid, element, checked)
        {
            if (element.className != "admin__control-checkbox") {
                var trElement = jQuery('#' + element.id).parents('tr');
                trRowIndex = trElement.index();
                var length = assignedAttributes.keys().length;
                if (checked) {
                    assignedAttributes.set(element.value, length+1);
                } else {
                    assignedAttributes.unset(element.value);
                }
                $('attr_ids').value = Object.toJSON(assignedAttributes);
                grid.reloadParams = {
                    'selected_attributes[]': assignedAttributes.keys()
                };
            }
        }

        /**
         * Click on product row
         *
         * @param {Object} grid
         * @param {String} event
         */
        function assignedProductRowClick(grid, event)
        {
            var trElement = Event.findElement(event, 'tr'),
                isInput = Event.element(event).tagName === 'INPUT',
                checked = false,
                checkbox = null;
            trRowIndex = trElement.rowIndex-2;
            if (trElement) {
                checkbox = Element.getElementsBySelector(trElement, 'input');
                if (checkbox[0]) {
                    checked = isInput ? checkbox[0].checked : !checkbox[0].checked;
                    gridJsObject.setCheckboxChecked(checkbox[0], checked);
                }
            }
        }
        gridJsObject.rowClickCallback = assignedProductRowClick;
        gridJsObject.checkboxCheckCallback = registerAssignedProduct;
    };
});
