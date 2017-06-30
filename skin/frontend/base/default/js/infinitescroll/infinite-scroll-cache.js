(function ($) {
    "use strict";

    $.widget('vaimo.infiniteScroll', $.vaimo.infiniteScroll, {
        _create: function() {
            this.options.state.cache = {};
            
            this._super.apply(this, arguments);
        },

        _reInit: function() {
            this._super.apply(this, arguments);

            this._loadNextPage('next');
            this._loadNextPage('prev');
        },

        _getPage: function(pageNumber, onPageLoadCallback) {
            pageNumber = this._getPageNumber(pageNumber);

            if(!this._isPossibleToLoadPage(pageNumber)) {
                return;
            }

            if(this._isCached(pageNumber)) {
                if(onPageLoadCallback) {
                    return onPageLoadCallback(this._getPageFromCache(pageNumber), pageNumber);
                }

                return;
            }

            this._super.apply(this, arguments);
        },

        _isCached: function(pageNumber) {
            var cacheKey = this._prepareUrl(pageNumber);
            return !!this.options.state.cache[cacheKey];
        },

        _getPageFromCache: function(pageNumber) {
            var cacheKey = this._prepareUrl(pageNumber);
            return this.options.state.cache[cacheKey];
        },


        _onPageLoad: function(transport) {
            var pageNumber = this.options.state.loadingPage;

            this._addToCache(pageNumber, transport);

            this._super.apply(this, arguments);
        },

        _addToCache: function(pageNumber, response) {
            var cacheKey = this._prepareUrl(pageNumber);
            this.options.state.cache[cacheKey] = response;
        },

        _onAnchorClick: function(event) {
            event.preventDefault();
            var pageNumber = this._getPageNumberFromUrl(this.options.state.url),
                cachedPage = this._getPageFromCache(pageNumber);

            if (cachedPage) {
                this._applyResponse(cachedPage);
            } else {
                this._super.apply(this, arguments);
            }
        }
    });
})(jQuery);