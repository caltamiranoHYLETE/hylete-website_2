;(function ($) {
    'use strict';

    var linkParser = document.createElement('a');

    $.widget('vaimo.cmsUrlUtils', {
        options: {
            baseUrls: {},
            baseUrl: ''
        },
        _create: function() {
            var baseUrls = this.options.baseUrls;

            this.urlMatchers = [];

            baseUrls.forEach(function (url) {
                this.urlMatchers.push({
                    url: url,
                    regExp: new RegExp('^' + url, 'i')
                });
            }.bind(this));

            this.internalUrlMatcher = new RegExp('^' + baseUrls.join('|'));
            this.baseUrlMatcher = new RegExp('^' + this.options.baseUrl, 'i');
        },
        stripCurrentBaseUrl: function(url) {
            return url.replace(this.baseUrlMatcher, '');
        },
        getBaseUrl: function() {
            return this.options.baseUrl;
        },
        removeParam: function(url, param) {
            return this.addOrReplaceParam(url, param, null);
        },
        addOrReplaceParam: function(url, param, value) {
            param = encodeURIComponent(param);

            var r = '([&?]|&amp;)' + param + '\\b(?:=(?:[^&#]*))*',
                regex = new RegExp(r, 'g'),
                str = '';

            if (value !== null && value !== undefined) {
                str = param + (value ? "=" + encodeURIComponent(value) : '');
            }

            linkParser.href = url;

            var q = linkParser.search.replace(regex, '');

            linkParser.search = q + (q && str && q.substr(-1) != '?' ? '&' : '') + str;

            return linkParser.href
                .replace('?&', '?')
                .replace('/?', '?');
        },
        setBaseUrl: function(url) {
            if (this.baseUrlMatcher.test(url)) {
                return url;
            }

            for (var i = 0; i < this.urlMatchers.length; i++) {
                if (!this.urlMatchers[i].regExp.test(url)) {
                    continue;
                }

                url = url.replace(url, this.options.baseUrl);

                break;
            }

            return url;
        },
        walkLinkElements: function(elements, updateCallback, excludeClass) {
            for (var i = 0; i < elements.length; i++) {
                if (excludeClass && elements[i].classList.contains(excludeClass)) {
                    continue;
                }

                var elementUrl = elements[i].href.toString();

                if (!this.internalUrlMatcher.test(elementUrl)) {
                    continue;
                }

                var updatedElementUrl = updateCallback(elementUrl);

                if (updatedElementUrl == elementUrl) {
                    continue;
                }

                elements[i].href = updatedElementUrl;
            }
        }
    });
})(jQuery);
