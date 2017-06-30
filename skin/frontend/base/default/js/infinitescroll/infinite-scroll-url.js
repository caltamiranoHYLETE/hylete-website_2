(function ($) {
    "use strict";

    $.widget('vaimo.infiniteScroll', $.vaimo.infiniteScroll, {
        options: {
            historyUpdateMode: $.vaimo.blockAjaxLoader.prototype.historyUpdateMode.NONE,
            changeUrl: true
        },

        _create: function() {
            this._super.apply(this, arguments);
        },

        _intervalCheck: function() {
            this._tryToUpdateUrl();

            this._super.apply(this,arguments);
        },

        _tryToUpdateUrl: function() {
            var containerTopPos,
                containerOffset,
                windowTopPos = $(window).scrollTop(),
                maxTopPos = 0,
                currentPos;

            this.options.state.positions.forEach(function(position) {
                containerOffset = position.container ? position.container.offset() : undefined;

                if(!containerOffset) {
                    return;
                }

                containerTopPos = containerOffset.top
                if(windowTopPos > containerTopPos && containerTopPos > maxTopPos) {
                    maxTopPos = containerTopPos;
                    currentPos = position;
                }
            });

            if(currentPos) {
                this._updateHistory(currentPos.transport);
            }
        },

        _applyResponse: function(transport) {
            if(this.options.state.replaceContent) {
                this._updateHistory(transport);
            }
            this._super.apply(this,arguments);
        },

        _updateHistory: function(transport) {
            var state;

            if (this.options.changeUrl) {
                if(transport.url === document.location.href) {
                    return;
                }

                state = {
                    url: this._addHttpsSupport(transport.url),
                    response: transport
                };

                if (typeof history.replaceState != 'undefined') {
                    history.replaceState(state, null, state.url);
                }
            }
        }
    });
})(jQuery);