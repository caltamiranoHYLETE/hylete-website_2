(function ($) {
    "use strict";

    $.widget('vaimo.infiniteScroll', $.vaimo.infiniteScroll, {
        options: {

        },

        _create: function() {
            this._super.apply(this, arguments);
        },

        _prepareUrl: function(pageNumber) {
            var unpackedUrl = this._unpackUrlParameters(),
                url;

            unpackedUrl.parameters.p = [pageNumber];
            url = this._packUrlParameters(unpackedUrl);

            return url;
        },


        _unpackUrlParameters: function(url) {
            var parameters = {},
                url = url || this.options.state.url,
                urlParts = url.split('?');

            if (urlParts.length > 1) {
                var urlParameters = urlParts[1];
                $.each(urlParameters.split('&'), function() {
                    var keyValuePair = this.split('=');
                    if(keyValuePair[1]) {
                        parameters[keyValuePair[0]] = keyValuePair[1].split(',');
                    }
                });
            }

            return {
                url: urlParts[0],
                parameters: parameters
            };
        },

        _packUrlParameters: function(unpackedUrl) {
            var serializedParameters = [];

            $.each(unpackedUrl.parameters, function(key, values) {
                var serializedValues = values.join(',');

                if (serializedValues.length > 0) {
                    serializedParameters.push(key + '=' + values.join(','));
                }
            });

            var parameters = serializedParameters.join('&');

            return unpackedUrl.url + (parameters.length ? ('?' + parameters) : '');
        },

        _getPageNumberFromUrl: function(url) {
            var unpackedUrl = this._unpackUrlParameters(url),
                pageNumber = false;

            if(unpackedUrl.parameters && unpackedUrl.parameters.p) {
                pageNumber = unpackedUrl.parameters.p.pop();
            }

            return pageNumber;
        },

        _throttle: function(fn, timeout) {
            var timer,
                args,
                needInvoke,
                ctx;

            return function() {
                args = arguments;
                needInvoke = true;
                ctx = this;

                if (!timer) {
                    (function func() {
                        if (needInvoke) {
                            fn.apply(ctx, args);
                            needInvoke = false;
                            timer = setTimeout(func, timeout);
                        } else {
                            timer = null;
                        }
                    })();
                }
            };
        },

        _addHttpsSupport: function(url) {
            var protocolDef = 'http:',
                protocolSecure = 'https:';

            if(window.location.protocol === protocolSecure && url.indexOf(protocolDef) !== -1) {
                url = protocolSecure + url.substring(protocolDef.length);
            }

            return url;
        }
    });
})(jQuery);