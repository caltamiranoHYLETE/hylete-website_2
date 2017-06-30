(function ($) {
    "use strict";

    $.vaimo.blockAjaxLoader.prototype.historyUpdateMode = {
        NONE: 1,
        REPLACE: 2,
        PUSH: 3
    };

    $.widget('vaimo.blockAjaxLoader', $.vaimo.blockAjaxLoader, {
        initialUrl: null,
        options: {
            historyUpdateMode: $.vaimo.blockAjaxLoader.prototype.historyUpdateMode.NONE
        },
        _create: function() {
            this._super();

            if (this.options.historyUpdateMode == $.vaimo.blockAjaxLoader.prototype.historyUpdateMode.PUSH) {
                var loader = this;

                loader.initialUrl = location.href;
                $(window).bind('popstate', function(event) {
                    if (loader.initialUrl && loader.initialUrl === location.href) {
                        loader.initialUrl = null;
                        return;
                    } else {
                        loader.initialUrl = null;
                    }

                    var fallbackUrl = document.location.href;
                    if (event.originalEvent.state) {
                        var response = event.originalEvent.state.response;
                        if (response && this._isValidResponse(response)) {
                            this._applyResponse(response);
                            return;
                        } else if (event.originalEvent.state.url) {
                            fallbackUrl = event.originalEvent.state.url;
                        }
                    }

                    window.location = fallbackUrl;
                }.bind(this));
            }
        },
        _historyUpdateEnabled: function() {
            return this.options.historyUpdateMode != $.vaimo.blockAjaxLoader.prototype.historyUpdateMode.NONE
        },
        _send: function(url, data) {
            this.initialUrl = null;

            if (this._historyUpdateEnabled() && typeof history.pushState != 'undefined') {
                var historyState = {
                    url: url
                };

                if (this.options.historyUpdateMode == $.vaimo.blockAjaxLoader.prototype.historyUpdateMode.REPLACE) {
                    history.replaceState(historyState, null, url);
                }

                if (this.options.historyUpdateMode == $.vaimo.blockAjaxLoader.prototype.historyUpdateMode.PUSH) {
                    history.pushState(historyState, null, url);
                }
            }

            this._super(url, data);
        },
        _applyResponse: function(transport) {
            if (this._historyUpdateEnabled()) {
                var state = {
                    url: transport.url
                };

                state.response = transport;

                if (typeof history.replaceState != 'undefined') {
                    if (state.response.size > 320000) {
                        try {
                            history.replaceState(state, null, state.url);
                        } catch (Exception) {
                            /* If the response is bigger than 320KB Firefox (40.0.3) and IE10/11 will throw an Exception,
                                but it will still work as intended!
                                - See: https://github.com/devote/HTML5-History-API/issues/16
                               So why not always try-catch this? Simple, try-catch is really bad for performance in js.
                             */
                        }
                    } else {
                        history.replaceState(state, null, state.url);
                    }
                }
            }

            this._super(transport);
        }
    });
})(jQuery);