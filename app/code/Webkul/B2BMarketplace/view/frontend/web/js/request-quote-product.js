/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_B2BMarketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/**
 * @api
 */
define([
    'uiComponent',
    'ko',
    'Magento_Ui/js/modal/alert',
    'jquery',
    'underscore',
    'mage/template',
    'matchMedia',
    'priceUtils'
], function (
    Component,
    ko,
    alert,
    $,
    _,
    mageTemplate,
    mediaCheck,
    utils
) {
    'use strict';

    var searchAjaxRequest = null;

    return Component.extend({
        defaults: {
            fieldSelector: '.wk-product-search-box',
            destinationSelector: '#wk_search_autocomplete',
            autocompleteVal: 'off',
            minSearchLength: 3,
            responseFieldElements: 'ul li',
            selectClass: 'selected',
            template:
                '<li class="<%- data.row_class %>" data-typeId="<%- data.type_id %>" id="qs-option-<%- data.entity_id %>" data-id="<%- data.entity_id %>" role="option">' +
                    '<span class="qs-option-name" data-typeId="<%- data.type_id %>" data-id="<%- data.entity_id %>">' +
                        ' <%- data.name %>' +
                    '</span>' +
                    '<span aria-hidden="true" class="amount" data-id="<%- data.entity_id %>" data-typeId="<%- data.type_id %>" data-price-amount="<%- data.final_price %>" id="product-amount-<%- data.index %><%- data.entity_id %>">' +
                    '<%- data.final_price %>' +
                    '</span>' +
                '</li>',
            isExpandable: null
        },

        isAjaxLoader: ko.observable(false),

        initialize: function () {
            this._super();
            this.responseList = {
                indexList: null,
                selected: null
            };
            this.searchField = $(this.fieldSelector);
            this.isExpandable = this.isExpandable;

            this.searchField.attr('autocomplete', this.autocompleteVal);

            mediaCheck({
                media: '(max-width: 768px)',
                entry: function () {
                    this.isExpandable = true;
                }.bind(this),
                exit: function () {
                    this.isExpandable = false;
                    this.searchField.removeAttr('aria-expanded');
                }.bind(this)
            });
        },

        onBlur: function (item, event) {
            var thisSelector = $(event.target);
            var autoCompleteSelector = $(this.destinationSelector);
            setTimeout($.proxy(function () {
                if (autoCompleteSelector.is(':hidden')) {
                    this.setActiveState(event, false);
                } else {
                    /*for issue #171
                    thisSelector.trigger('focus');*/
                }
                autoCompleteSelector.hide();
                this._updateAriaHasPopup(event, false);
            }, this), 250);
        },

        /**
         * Sets state of the search field to provided value.
         *
         * @param {Boolean} isActive
         */
        onFocus: function (item, event) {
            this.setActiveState(event, true);
        },

        /**
         * removes suggestions on blur
         */
        onFocusOut : function(item, event) {
            this.setActiveState(event, false);
        },

        /**
         * Sets state of the search field to provided value.
         *
         * @param {Boolean} isActive
         */
        setActiveState: function (event, isActive) {
            $(event.target).parents('div').toggleClass('active', isActive);

            if (this.isExpandable) {
                $(event.target).attr('aria-expanded', isActive);
            }
            if (isActive) {
                $(this.destinationSelector).show();
            } else {
                $(this.destinationSelector).hide();
            }
        },

        /**
         * @private
         * @param {Event} event - The selected event
         * @return {Element} The first element in the suggestion list.
         */
        _getFirstVisibleElement: function (event) {
            return this.responseList.indexList ? this.responseList.indexList.first() : false;
        },

        /**
         * @private
         * @param {Event} event - The selected event
         * @return {Element} The last element in the suggestion list.
         */
        _getLastElement: function (event) {
            return this.responseList.indexList ? this.responseList.indexList.last() : false;
        },

        /**
         * @private
         * @param {Boolean} show - Set attribute aria-haspopup to "true/false" for element.
         */
        _updateAriaHasPopup: function (event, show) {
            if (show) {
                $(event.target).attr('aria-haspopup', 'true');
            } else {
                $(event.target).attr('aria-haspopup', 'false');
            }
        },

        /**
         * Clears the item selected from the suggestion list and resets the suggestion list.
         * @private
         * @param {Event} event - The selected event
         * @param {Boolean} all - Controls whether to clear the suggestion list.
         */
        _resetResponseList: function (event, all) {
            this.responseList.selected = null;

            if (all === true) {
                this.responseList.indexList = null;
            }
        },

        /**
         * Executes when keys are pressed in the search input field. Performs specific actions
         * depending on which keys are pressed.
         * @private
         * @param {Event} event - The key down event
         * @return {Boolean} Default return type for any unhandled keys
         */
        onKeyDown: function (item, event) {
            var keyCode = event.keyCode || event.which;
            var thisSelector = $(event.target);

            switch (keyCode) {
                case $.ui.keyCode.HOME:
                    this._getFirstVisibleElement(event).addClass(this.selectClass);
                    this.responseList.selected = this._getFirstVisibleElement(event);
                    break;

                case $.ui.keyCode.END:
                    this._getLastElement(event).addClass(this.selectClass);
                    this.responseList.selected = this._getLastElement(event);
                    break;

                case $.ui.keyCode.ESCAPE:
                    this._resetResponseList(event, true);
                    $(this.destinationSelector).hide();
                    break;

                case $.ui.keyCode.ENTER:
                    if (this.responseList.selected) {
                        thisSelector.val(this.responseList.selected.find('.qs-option-name').text());
                    }
                    this._updateAriaHasPopup(this, false);
                    break;

                case $.ui.keyCode.DOWN:
                    if (this.responseList.indexList) {
                        if (!this.responseList.selected) {  //eslint-disable-line max-depth
                            this._getFirstVisibleElement(event).addClass(this.selectClass);
                            this.responseList.selected = this._getFirstVisibleElement(event);
                        } else if (!this._getLastElement(event).hasClass(this.selectClass)) {
                            this.responseList.selected = this.responseList.selected
                                .removeClass(this.selectClass).next().addClass(this.selectClass);
                        } else {
                            this.responseList.selected.removeClass(this.selectClass);
                            this._getFirstVisibleElement(event).addClass(this.selectClass);
                            this.responseList.selected = this._getFirstVisibleElement(event);
                        }
                        thisSelector.val(this.responseList.selected.find('.qs-option-name').text());
                        $('#wk_product_id').val(
                            this.responseList.selected.attr('data-id')
                        );
                        thisSelector.attr('aria-activedescendant', this.responseList.selected.attr('id'));
                    }
                    break;

                case $.ui.keyCode.UP:
                    if (this.responseList.indexList !== null) {
                        if (!this._getFirstVisibleElement(event).hasClass(this.selectClass)) {
                            this.responseList.selected = this.responseList.selected
                                .removeClass(this.selectClass).prev().addClass(this.selectClass);
                        } else {
                            this.responseList.selected.removeClass(this.selectClass);
                            this._getLastElement(event).addClass(this.selectClass);
                            this.responseList.selected = this._getLastElement(event);
                        }
                        thisSelector.val(this.responseList.selected.find('.qs-option-name').text());
                        $('#wk_product_id').val(this.responseList.selected.attr('data-id'));
                        thisSelector.attr('aria-activedescendant', this.responseList.selected.attr('id'));
                    }
                    break;
                default:
                    return true;
            }
        },

        /**
         * Executes when the value of the search input field changes. Executes a GET request
         * to populate a suggestion list based on entered text. Handles click (select), hover,
         * and mouseout events on the populated suggestion list dropdown.
         * @private
         */
        onPropertyChange: function (item, event) {
            var searchField = $(event.target),
                clonePosition = {
                    position: 'absolute',
                    width: searchField.outerWidth()
                },
                source = this.template,
                template = mageTemplate(source),
                dropdown = $('<ul role="listbox"></ul>'),
                value = searchField.val(),
                formKey = $('#wk-qo-form-key').val();

            $('#wk_search_autocomplete').html('');

            this.responseList = {
                indexList: null,
                selected: null
            }

            if (value.length >= parseInt(this.minSearchLength, 10)) {
                this.isAjaxLoader(true);
                if (searchAjaxRequest) {
                    searchAjaxRequest.abort();
                }
                searchAjaxRequest = $.getJSON(this.url, {
                    q: value,
                    rfq: 1,
                    shop: this.shop_url
                }, $.proxy(function (data) {
                    searchAjaxRequest = null;
                    if (data.size) {
                        $.each(data.products, function (index, element) {
                            var html;
                            html = template({
                                data: element
                            });
                            dropdown.append(html);
                            dropdown.find('#qs-option-'+element.entity_id).find('.qs-option-name').html(element.name);
                            dropdown.find('#product-amount-'+element.entity_id).html(element.price_html);
                        });

                        this.isAjaxLoader(false);

                        this.responseList.indexList = $(this.destinationSelector)
                            .html(dropdown)
                            .css(clonePosition)
                            .show()
                            .find(this.responseFieldElements + ':visible');

                        this._resetResponseList(event, false);
                        $('#wk_product_id').val('');
                        searchField.removeAttr('aria-activedescendant');

                        if (this.responseList.indexList.length) {
                            this._updateAriaHasPopup(this, true);
                        } else {
                            this._updateAriaHasPopup(this, false);
                        }

                        this.responseList.indexList
                            .on('click', function (e) {
                                var productId = $(e.currentTarget).attr('data-id');
                                this.responseList.selected = $(e.currentTarget);
                                $('#wk_product_id').val(productId);
                                searchField.val(this.responseList.selected.find('.qs-option-name').text());
                            }.bind(this))
                            .on('mouseenter mouseleave', function (e) {
                                this.responseList.indexList.removeClass(this.selectClass);
                                $(e.target).addClass(this.selectClass);
                                this.responseList.selected = $(e.target);
                                $('#wk_product_id').val($(e.target).attr('data-id'));
                                searchField.attr('aria-activedescendant', $(e.target).attr('id'));
                            }.bind(this))
                            .on('mouseout', function (e) {
                                if (!this._getLastElement(event) &&
                                    this._getLastElement(event).hasClass(this.selectClass)) {
                                    $(e.target).removeClass(this.selectClass);
                                    this._resetResponseList(event, false);
                                }
                            }.bind(this));
                    } else {
                        this.isAjaxLoader(false);
                    }
                }, this));
            } else {
                this._resetResponseList(event, true);
                $(this.destinationSelector).hide();
                this._updateAriaHasPopup(event, false);
                $('#wk_product_id').val('');
                searchField.removeAttr('aria-activedescendant');
            }
        }
    });
});
