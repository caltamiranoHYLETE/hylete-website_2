(function ($) {
    "use strict";

    $.widget('vaimo.infiniteScroll', $.vaimo.infiniteScroll, {
        options: {
            addLoadMoreButtons: true,
            maxAutoloadCount: 2,
            addMoreButtonTopHtml: '<button class="load-more-pages js-load-page js-load-page--prev" data-direction="prev"></button>',
            addMoreButtonBottomHtml: '<button class="load-more-pages js-load-page js-load-page--next" data-direction="next"></button>',
            loadMoreButtonText: 'View more products',
        },

        _create: function() {
            this._super.apply(this, arguments);
        },

        _reInit: function() {
            this._super.apply(this,arguments);
            this.options.state.loadMoreButtonsAdded = false;
            this._addLoadMoreButtons();
            this._isLoadMoreButtonsVisible();
        },

        _showPage: function() {
            this._super.apply(this,arguments);
            this._addLoadMoreButtons();
            this._isLoadMoreButtonsVisible();
        },

        _addLoadMoreButtons: function() {
            var $loadMoreTop,
                $loadMoreBottom;

            if(this.options.maxAutoloadCount) {
                if(this.options.state.loadedPagesCount < this.options.maxAutoloadCount) {
                    return;
                }
            }

            if(!this.options.addLoadMoreButtons || this.options.state.loadMoreButtonsAdded) {
                return;
            }

            this.options.state.scrollLoadingDisabled = true;

            if(!this.options.isTopPaginationHidden) {
                $loadMoreTop = $(this.options.addMoreButtonTopHtml);
                $loadMoreTop.html(Translator.translate(this.options.loadMoreButtonText));

                if($(this.options.toolbarTopSelector).length) {
                    $loadMoreTop.insertAfter(this.options.toolbarTopSelector);
                } else {
                    $loadMoreTop.insertBefore(this.options.parentContainerSelector);
                }
            }

            if(!this.options.isBottomPaginationHidden) {
                $loadMoreBottom = $(this.options.addMoreButtonBottomHtml);
                $loadMoreBottom.html(Translator.translate(this.options.loadMoreButtonText));

                if($(this.options.toolbarBottomSelector).length) {
                    $loadMoreBottom.insertAfter(this.options.toolbarBottomSelector);
                } else {
                    $loadMoreBottom.insertAfter(this.options.parentContainerSelector);
                }
            }

            $(this.options.parentContainerSelector).on('click', '.js-load-page', this._onLoadNextButtonClick.bind(this));
            $(document).on('click', '.js-load-page', this._onLoadNextButtonClick.bind(this));

            this.options.state.loadMoreButtonsAdded = true;
        },

        _isLoadMoreButtonsVisible: function(isVisible) {
            isVisible = isVisible === undefined ? true : isVisible;
            $('.js-load-page--prev').toggleClass('hidden', this.options.state.pageNumberPrev <= 1 || !isVisible);
            $('.js-load-page--next').toggleClass('hidden', this.options.state.pageNumberNext >= this.options.state.pageNumberLast || !isVisible);
        },

        _onLoadNextButtonClick: function(e) {
            var appendDirection = $(e.target).data('direction');

            e.preventDefault();
            e.stopPropagation();

            if(appendDirection) {
                this._showNextPage(appendDirection);
            }
        }
    });
})(jQuery);