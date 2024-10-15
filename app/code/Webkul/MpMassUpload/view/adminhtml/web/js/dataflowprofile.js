/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMassUpload
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
 /*jshint jquery:true*/
define([
    "jquery",
    'mage/translate',
    'mage/template'
], function ($, $t, mageTemplate) {
    'use strict';
    $.widget('mage.dataflowprofile', {
        _create: function () {
            var self = this;
            var fieldIndex = self.options.fieldIndex;
            $('#edit_form').append($('#wk-dataflow-profile-field-wrapper'));
            $('#wk-dataflow-profile-field-wrapper').show();

            $('body').on('click', '.wk-fieldmap-row-add', function () {
                var obj = $('#wk-fieldmap-template').html();

                var progressTmpl = mageTemplate('#fieldmapTemplate'),tmpl;
                tmpl = progressTmpl({
                    data: {
                        fieldIndex: fieldIndex
                    }
                });
                $('#wk-fieldmap-container').append(tmpl);
                fieldIndex = fieldIndex+1;
            });

            $('body').on('click', '.wk-fieldmap-row-delete', function () {
                $(this).parents('.field-row').remove();
            });

            $('body').on('change', '.wk-fieldmap-attr-select', function () {
                var value = $(this).val();
                $(this).parents('.field-row').find('.wk-fieldmap-attr-input').val(value);
            });
        }
    });
    return $.mage.dataflowprofile;
});
