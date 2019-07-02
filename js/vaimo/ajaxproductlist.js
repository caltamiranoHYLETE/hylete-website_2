(function ($) {
    "use strict";

    $.widget('vaimo.ajaxProductList', $.vaimo.blockAjaxLoader, {
        options: {
            historyUpdateMode: $.vaimo.blockAjaxLoader.prototype.historyUpdateMode.PUSH,
            allowMultipleRequests: true,
            defaultRequestToken: 'product_list',
            eventSelectors: {
                anchors: {
                    '.block-layered-nav a': true,
                    '.category-products .toolbar a': true,
                    '.vaimo-cms .sort_buttons a': true
                },
                selects: {
                    '.category-products .toolbar select': true
                }
            }
        },
        _create: function() {
            this._super();
            this._removeOnchangeEvent();
            this._registerEventHandlers();
        },
        _removeOnchangeEvent: function() {
            $(this._getSelects()).removeAttr('onchange');
        },
        _getEnabledSelectors: function(selectorUsageFlags) {
            var usedSelectors = [];

            if (Object.prototype.toString.call(selectorUsageFlags) == '[object Array]') {
                return selectorUsageFlags;
            }

            $.each(selectorUsageFlags, function(selector, enabled) {
                if (enabled) {
                    usedSelectors.push(selector);
                }
            });

            return usedSelectors;
        },
        _getAnchors: function() {
            var usedAnchors = this._getEnabledSelectors(this.options.eventSelectors.anchors);
            return usedAnchors.join();
        },
        _getSelects: function() {
            var usedSelects = this._getEnabledSelectors(this.options.eventSelectors.selects);
            return usedSelects.join();
        },
        _registerEventHandlers: function() {
            var $body = $(document.body);

            $body.on('click', this._getAnchors(), $.proxy(this._onAnchorClick, this));
            $body.on('change', this._getSelects(), $.proxy(this._onSelectChange, this));
        },
        _onAnchorClick: function(event) {
            event.preventDefault();
            var requestUrl = event.currentTarget.href;
            this._createAjaxRequest(requestUrl);
        },
        _onSelectChange: function(event) {
            var requestUrl = $(event.currentTarget).val();
            this._createAjaxRequest(requestUrl);
        },
        _complete: function() {
            this._removeOnchangeEvent();
            this._super();
        }
    });
})(jQuery);
