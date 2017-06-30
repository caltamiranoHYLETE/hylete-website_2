(function ($) {
    "use strict";

    $.widget('vaimo.ajaxProductList', $.vaimo.ajaxProductList, {
        customRender: false,
        options: {
            parentContainerSelector: '.category-products,.results-view',
            productSelector: '.item',
            pagesContainerSelector: '.products-grid',
            toolbarTopSelector: '.toolbar-top',
            toolbarBottomSelector: '.toolbar-bottom',
            showMoreButtonSelector: '.show-more',
            state: {}
        },

        _create: function() {
            this._super.apply(this, arguments);
        },

        _registerEventHandlers: function() {
            this._super.apply(this, arguments);
            $(document).on('click', this.options.showMoreButtonSelector, this._showNextPage.bind(this));
        },

        _prepareUrl: function(pageNumber) {
            var unpackedUrl = this._unpackUrlParameters(document.location.href),
                url;

            unpackedUrl.parameters.p = [pageNumber];
            url = this._packUrlParameters(unpackedUrl);

            return url;
        },

        _getConfigurationData: function() {
            this.options.state.configurationData = $('.js-infinite-scroll-pager-data').data();
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
                this.options.state.pageNumberNextSequence = this.options.state.pageNumberLanding + 1;
            }
        },

        _unpackUrlParameters: function(url) {
            var parameters = {},
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

        _addHttpsSupport: function(url) {
            var protocolDef = 'http:',
                protocolSecure = 'https:';

            if(window.location.protocol === protocolSecure && url.indexOf(protocolDef) !== -1) {
                url = protocolSecure + url.substring(protocolDef.length);
            }

            return url;
        },

        _showNextPage: function(event) {
            this._setCurrentPageAndPageLimit();
            event.preventDefault();
            this.customRender = true;
            var requestUrl = this._prepareUrl(this.options.state.pageNumberNextSequence);
            this._createAjaxRequest(requestUrl);
        },

        _createAjaxRequest: function(url, groupName) {
            url = this._addHttpsSupport(url);
            this._super(url, groupName)
        },

        _applyResponse: function(transport) {
            if(!this.customRender) {
                this._super.apply(this, arguments);
            } else {
                this.customRender = false;
                this._showPage(transport);
            }
        },

        _showPage: function(transport) {
            $.each(transport.blocks, function(_, block) {
                var $items,
                    $itemsContainer,
                    $block,
                    $toolbarBottom,
                    targets,
                    containers = this.options.parentContainerSelector.split(','),
                    parentContainerSelector = false,
                    html;

                if (block.target.selector) {
                    targets = block.target.selector.split(',');
                    targets.forEach(function(selector) {
                        if(containers.indexOf(selector) !== -1) {
                            parentContainerSelector = selector;
                        }
                    });
                }

                if(parentContainerSelector) {
                    html = $(block.html);
                    $items = html.find(this.options.productSelector);
                    $toolbarBottom = html.find(this.options.toolbarBottomSelector);
                    $block = $(parentContainerSelector + ' ' + this.options.pagesContainerSelector).last();
                    $block.append($items);
                    $(this.options.toolbarBottomSelector).replaceWith($toolbarBottom)
                } else {
                    this._replaceContent(block.target.selector, block.html);
                }
            }.bind(this));

            this._updateLoadMoreButton();
        },

        _updateLoadMoreButton: function() {
            var isLastPage = this.options.state.pageNumberNextSequence >= this.options.state.pageNumberLast;
            $(this.options.showMoreButtonSelector).toggleClass('hidden', isLastPage);
            
        }
    });
})(jQuery);