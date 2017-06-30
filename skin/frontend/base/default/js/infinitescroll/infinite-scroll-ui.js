(function ($) {
    "use strict";

    $.widget('vaimo.infiniteScroll', $.vaimo.infiniteScroll, {
        options: {
            parentContainerSelector: '.category-products',
            productSelector: '.item',
            pagesContainerSelector: '.products-grid',
            toolbarTopSelector: '.toolbar-top',
            toolbarBottomSelector: '.toolbar-bottom',
            isTopPaginationHidden: true,
            isBottomPaginationHidden: false,
            pagesContainer: '<ol class="row products-grid"></ol>'
        },

        _create: function() {
            this._super.apply(this, arguments);

            if(this.options.state.pageNumberLanding) {
                if(!this._isFirstPage()) {
                    this._scrollToContainer(true);
                }
            }
        },

        _reInit: function() {
            this._super.apply(this, arguments);
            this._togglePagination();
        },

        _setCurrentPageAndPageLimit: function() {
            this._super.apply(this,arguments);

            if(this._getConfigurationData()) {
                this.options.state.positions = [];
                this.options.state.updateUi = false;
                this.options.state.positions.push({
                    container: $(this.options.pagesContainerSelector),
                    transport: {
                        url: this.options.state.url
                    }
                });
            }
        },

        _onAnchorClick: function(event) {
            event.stopPropagation();
            this.options.state.replaceContent = true;
            this.options.state.url = event.currentTarget.href;
            this._super.apply(this, arguments);
        },

        _onSelectChange: function(event) {
            this.options.state.url = event.currentTarget.href;
            this.options.state.replaceContent = true;
            this._super.apply(this, arguments);
        },

        _togglePagination: function(isTopHidden, isBottomHidden) {
            var $toolbarTopContainer = $(this.options.toolbarTopSelector),
                $toolbarBottomContainer = $(this.options.toolbarBottomSelector);

            isTopHidden = isTopHidden === undefined && !this.options.isTopPaginationHidden ? true : !!isTopHidden;
            isBottomHidden = isBottomHidden === undefined && !this.options.isBottomPaginationHidden ? true : !!isBottomHidden;

            $toolbarTopContainer.find('.pages').toggleClass('hidden', isTopHidden);
            $toolbarBottomContainer.find('.pages').toggleClass('hidden', isBottomHidden);
        },

        _scrollToContainer: function(isFirstLoad) {
            var $container = $(this.options.pagesContainerSelector),
                containerHeight = $container.height(),
                extraMargin = isFirstLoad ? containerHeight * 0.1 : containerHeight * 0.9;

            $('html, body').animate({
                scrollTop: $container.offset().top + extraMargin
            }, this.options.scrollAnimationDuration);
        },

        _showPage: function(transport, pageNumber) {
            if(this.options.state.updateUi) {
                return;
            }
            this.options.state.updateUi = true;

            if(this.options.displayLoader && this.loaderEntity) {
                this.loaderEntity.hide();
            }

            this._setDelay();

            this._onShowPageBefore();

            if(pageNumber > this.options.state.pageNumberLanding) {
                this.options.state.pageNumberNext = pageNumber;
            } else {
                this.options.state.pageNumberPrev = pageNumber;
            }

            $.each(transport.blocks, function(_, block) {
                var $items,
                    $itemsContainer,
                    $block;

                if (block.target.selector) {
                    if(block.target.selector.split(',').indexOf(this.options.parentContainerSelector) !== -1) {
                        $items = $(block.html).find(this.options.productSelector);

                        if(this.options.debug) {
                            $items.css({
                                'background-color': '#'+Math.floor(Math.random()*16777215).toString(16)
                            });
                        }

                        $itemsContainer = $(this.options.pagesContainer);

                        if(pageNumber > this.options.state.pageNumberLanding) {
                            $block = $(this.options.parentContainerSelector + ' ' + this.options.pagesContainerSelector).last();
                            $itemsContainer.append($items).insertAfter($block);
                        } else {
                            $block = $(this.options.parentContainerSelector + ' ' + this.options.pagesContainerSelector).first();
                            $itemsContainer.append($items).insertBefore($block);
                            this._scrollToContainer();
                        }

                        this.options.state.positions.push({
                            container: $itemsContainer,
                            transport: transport
                        });
                    } else {
                        this._replaceContent(block.selector, block.html);
                    }
                }
                else {
                    this._replaceContent(block.selector, block.html);
                }
            }.bind(this));

            this._onShowPageAfter();
            this.options.state.updateUi = false;
        },

        _onShowPageBefore: function() {
            this.options.state.loadedPagesCount++;
            this._loadNextPage(this.options.state.nextPageDirection);
        },

        _onShowPageAfter: function() {
            this._togglePagination();
        },

        _shouldLoadPage: function() {
            var result = this._super.apply(this, arguments);

            if(result) {
                if(this.options.isBottomPaginationHidden && this.options.state.scrollDirection == 'next') {
                    result = false;
                }

                if(this.options.isTopPaginationHidden && this.options.state.scrollDirection == 'prev') {
                    result = false;
                }
            }

            return result;
        },

        _intervalCheck: function() {
            if(this._shouldLoadPage()) {
                this._showNextPage(this.options.state.scrollDirection)
            }

            this._super.apply(this,arguments);
        },

        _applyResponse: function(transport) {
            if(this.options.state.replaceContent) {
                this._super.apply(this, arguments);
                this._reInit();
                this.options.state.replaceContent = false;
            } else {
                this._onPageLoad(transport);
            }
        }
    });
})(jQuery);