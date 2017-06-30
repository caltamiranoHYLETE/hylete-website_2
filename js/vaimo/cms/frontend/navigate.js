;(function ($) {
    "use strict";

    $.widget('vaimo.navigateInFrontend', {
        options: {
            urlUtils: false,
            editModeParams: {},
            storeIdParam: null,
            baseWebUrl: null,
            isStoreCodeEnabled: null,
            currentStoreCode: null,
            stores: null
        },
        editModeValue: null,
        storeIdValue: null,
        _create: function() {
            var triggerUrlUpdating = debounce(function() {
                if (document.body.classList.contains('vcms-editing-active')) {
                    return;
                }

                this.init();
            }.bind(this), 50);

            var observer = new MutationObserver(triggerUrlUpdating);

            observer.observe(document.body, {
                subtree: true,
                childList: true
            });

            $(document).ready(triggerUrlUpdating);
        },
        init: function() {
            this.options.urlUtils.walkLinkElements(
                document.getElementsByTagName('a'),
                this._buildUrl.bind(this),
                'vcms-navigation-exclude'
            );

            this._changeStoreSwitcherUrls();
        },
        _changeStoreSwitcherUrls: function() {
            var $storeSwitcher = $('#select-language');
            var that = this;

            if ($storeSwitcher.length) {
                var options = $storeSwitcher.find('option');

                options.each(function() {
                    this.value = that._buildStoreSwitcherUrl(this.value);
                });
            }
        },
        _addEditModeParams: function(url, custom) {
            return Object.keys(this.options.editModeParams).reduce(function(url, paramName) {
                return this.options.urlUtils.addOrReplaceParam(
                    url,
                    paramName,
                    (custom && custom[paramName]) || this.options.editModeParams[paramName]
                );
            }.bind(this), url);
        },
        _buildUrl: function(url) {
            return this._addEditModeParams(
                this.options.urlUtils.setBaseUrl(url)
            );
        },
        _buildStoreSwitcherUrl: function(optionValue) {
            var storeCode = this._getStoreCodeFromUrl(optionValue);
            var storeId = this.options.stores[storeCode];

            var url = this.options.baseWebUrl;

            if (this.options.isStoreCodeEnabled) {
                url += storeCode + '/';
            }

            var custom = {};

            custom[this.options.storeIdParam] = storeId;

            return this._addEditModeParams(url, custom);
        },
        _getUrlParam: function (url, name) {
            return (new RegExp('[?|&]' + name + '=' + '(.+?)(&|#|$)').exec(url) || [null, null])[1];
        },
        _getStoreCodeFromUrl: function(url) {
            var storeCode;

            if (this.options.isStoreCodeEnabled) {
                url = url.replace(this.options.baseWebUrl, '');
                url = this._stripParametersFromUrl(url);

                storeCode = this._getStringBeforeFirstSlash(url);
            } else {
                storeCode = this._getUrlParam(url, '___store');
            }

            return storeCode;
        },
        _stripParametersFromUrl: function(url) {
            var matches = url.match(/[^?]+/);
            return matches[0];
        },
        _getStringBeforeFirstSlash: function(url) {
            var matches = url.match(/([^\/]*)/);
            return matches[0];
        }
    });
})(jQuery);