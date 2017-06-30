(function ($) {
    "use strict";

    $.widget('vaimo.infiniteScroll', $.vaimo.ajaxProductList, {
        options: {
            debug: false,
            displayLoader: true,
            state: {}
        },

        _create: function() {
            this._super.apply(this, arguments);
            this._reInit();
        },

        _reInit: function() {
            this.options.state.scrollLoadingDisabled = false;
            this.options.state.configurationData = undefined;
            this.options.state.pageNumberLanding = undefined;
            this.options.state.pageNumberLast = undefined;
            this.options.state.pageNumberNext = undefined;
            this.options.state.pageNumberPrev = undefined;
            this.options.state.pageNumberNextSequence = undefined;
            this.options.state.pageNumberPrevSequence = undefined;
            this.options.state.nextPageDirection = undefined;
            this.options.state.replaceContent = false;
            this.options.state.loadedPagesCount = 0;
            this.options.state.url = this.options.state.url || document.location.href;

            this._getConfigurationData();
            this._setCurrentPageAndPageLimit();
        },

        _getConfigurationData: function() {
            if(!this.options.state.configurationData) {
                this.options.state.configurationData = $('.js-infinite-scroll-pager-data').data();
            }
            return this.options.state.configurationData;
        },

        _setCurrentPageAndPageLimit: function() {
            var data = this._getConfigurationData(),
                currentPage,
                lastPage;

            if(data) {
                currentPage = +data.currentpage;
                lastPage = +data.lastpage;

                if(currentPage > lastPage) {
                    currentPage = lastPage;
                } else if(currentPage < 1) {
                    currentPage = 1;
                }

                this.options.state.pageNumberLast = lastPage;
                this.options.state.pageNumberLanding = currentPage;
                this.options.state.pageNumberPrev = currentPage;
                this.options.state.pageNumberNext = currentPage;
                this.options.state.pageNumberPrevSequence = this.options.state.pageNumberPrev - 1;
                this.options.state.pageNumberNextSequence = this.options.state.pageNumberNext + 1;
            }
        },

        _isPossibleToLoadPage: function(pageNumber) {
            return !(!pageNumber || pageNumber < 1 || pageNumber > this.options.state.pageNumberLast);
        },

        _showNextPage: function(direction) {
            var pageNumber;

            switch (direction) {
                case 'next':
                    pageNumber = this.options.state.pageNumberNext + 1;
                    break;
                case 'prev':
                    pageNumber = this.options.state.pageNumberPrev - 1;
            }

            this.options.state.nextPageDirection = direction;

            this._getPage(pageNumber, this._showPage.bind(this));
            
            if(typeof this._isLoadMoreButtonsVisible  == 'function') this._isLoadMoreButtonsVisible();
        },

        _loadNextPage: function(direction) {
            var pageNumber;

            switch (direction) {
                case 'next':
                    pageNumber = this.options.state.pageNumberNextSequence;
                    this.options.state.pageNumberNextSequence++;
                    break;
                case 'prev':
                    pageNumber = this.options.state.pageNumberPrevSequence;
                    this.options.state.pageNumberPrevSequence--;
            }

            this._getPage(pageNumber);
        },

        _isFirstPage: function() {
            return this.options.state.pageNumberLanding === 1;
        },

        _getPageNumber: function(pageNumber) {
            if(!pageNumber) {
                return;
            }

            if(pageNumber === 'next') {
                pageNumber = this.options.state.pageNumberNext;
            } else if(pageNumber === 'prev') {
                pageNumber = this.options.state.pageNumberPrev;
            }

            return pageNumber;
        },

        _isInfiniteLoadingDisabled: function() {
            return this.options.isInfiniteLoadingDisabled;
        },

        _beforeSend: function() {
            if(!this.options.state.replaceContent) {
                return;
            }
            this._super();
        }
    });
})(jQuery);