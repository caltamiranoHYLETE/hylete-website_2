(function ($) {
    "use strict";

    $.widget('vaimo.infiniteScroll', $.vaimo.infiniteScroll, {
        options: {
            defaultRequestToken: 'product_list',
            allowMultipleRequests: true
        },

        _create: function() {
            this._super.apply(this, arguments);
        },

        _reInit: function() {
            this._super.apply(this,arguments);
            this.options.state.queue = [];
            this.options.state.onPageLoadCallbacks = {};
            this.options.state.onAjaxProcessing = false;
            this.options.state.loadingPage = undefined;

        },

        _createAjaxRequest: function(url, groupName) {
            url = this._addHttpsSupport(url);
            this._super(url, groupName)
        },

        _setCurrentPageAndPageLimit: function() {
            this._super.apply(this,arguments);

            if(this._getConfigurationData()) {
                this.options.state.requested = [];
                this.options.state.requested.push(this.options.state.pageNumberLanding);
            }
        },

        _getPage: function(pageNumber, onPageLoadCallback) {
            pageNumber = this._getPageNumber(pageNumber);

            if(!this._isPossibleToLoadPage(pageNumber)) {
                return;
            }

            if(this._isAlreadyRequested(pageNumber)) {
                if(onPageLoadCallback) {
                    if(this.options.displayLoader && this.loaderEntity) {
                        this.loaderEntity.show();
                    }
                    this._addLoadCallback(pageNumber, onPageLoadCallback);
                }
                return;
            }

            if(this._isOnAjaxCall()) {
                this._addToQueue(pageNumber, onPageLoadCallback);
                this._addLoadCallback(pageNumber, onPageLoadCallback);
            } else {
                this.options.state.requested.push(pageNumber);
                this._addLoadCallback(pageNumber, onPageLoadCallback);
                this._makeAjaxCall(pageNumber);
            }
        },

        _makeAjaxCall: function(pageNumber) {
            var url = this._prepareUrl(pageNumber);
            this.options.state.loadingPage = pageNumber;
            this.options.state.onAjaxProcessing = true;

            this._createAjaxRequest(url);
        },

        _addToQueue: function(pageNumber) {
            this.options.state.queue.push(pageNumber);
        },

        _getNexPageFromQueue: function() {
            var pageNumber = this.options.state.queue.shift();
            if(pageNumber) {
                this._getPage(pageNumber);
            }
        },

        _isInQueue: function(pageNumber) {
            return this.options.state.queue.indexOf(pageNumber) !== -1;
        },

        _isAlreadyRequested: function(pageNumber) {
            return this.options.state.requested.indexOf(pageNumber) !== -1;
        },

        _isOnAjaxCall: function() {
            return this.options.state.onAjaxProcessing;
        },

        _addLoadCallback: function(pageNumber, onPageLoadCallback) {
            if(onPageLoadCallback) {
                this.options.state.onPageLoadCallbacks[pageNumber] = onPageLoadCallback;
            }
        },

        _onPageLoad: function(transport) {
            var pageNumber = this.options.state.loadingPage;
            this.options.state.onAjaxProcessing = false;

            this._runPageCallbacks(pageNumber, transport);
            this._getNexPageFromQueue();
        },

        _runPageCallbacks: function(pageNumber, response) {
            var callback = this.options.state.onPageLoadCallbacks[pageNumber];

            if(typeof callback === 'function') {
                callback.apply(this, [response, pageNumber]);
            }
        }
    });
})(jQuery);