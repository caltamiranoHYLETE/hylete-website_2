(function ($) {
    "use strict";

    $.widget('vaimo.mofSelector', $.vaimo.mofSelector, {
        _create: function () {
            this._super();

            var $document = $(document);
            $document.unbind('click.mof');
            $document.on('click.mof', this._getOptionLinkSelector(), this._onFilterItemClick.bindAsEventListener(this));
        },
        _getOptionLinkSelector: function () {
            var selectors = this.options.selectors;

            return [selectors.filterBlocks, selectors.filterOption, 'a'].join(' ');
        },
        _onFilterItemClick: function (event) {
            if (!$.active) {
                return;
            }

            var target = event.target || event.srcElement;

            if (target) {
                var $closestOptionItem = $(target).closest(this.options.selectors.filterOption);
                var $optionItem = $($closestOptionItem[0]);

                this._toggleOptionMarkerForItem($optionItem);
            }
        }
    });

    $.widget('vaimo.mofSelector', $.vaimo.mofSelector, {
        filterState: {},
        _create: function () {
            this._super();
            this.filterState = {};
        },
        _unpackUrlParameters: function (url) {
            var parameters = {};
            var urlParts = url.split('?');

            if (urlParts.length > 1) {
                var urlParameters = urlParts[1];

                urlParameters.split('&').forEach(function (item) {
                    var keyValuePair = item.split('=');

                    parameters[keyValuePair[0]] = keyValuePair[1] ? keyValuePair[1].split(',') : [];
                });
            }

            return {
                url: urlParts[0],
                parameters: parameters
            };
        },
        _packUrlParameters: function (unpackedUrl) {
            var serializedParameters = [];

            $.each(unpackedUrl.parameters, function (key, values) {
                var serializedValues = values.join(',');

                if (serializedValues.length > 0) {
                    serializedParameters.push(key + '=' + values.join(','));
                }
            });

            var parameters = serializedParameters.join('&');

            return unpackedUrl.url + (parameters.length ? ('?' + parameters) : '');
        },
        _getFilterItemUrlValues: function (item) {
            var $link = $(item).find('a');
            var href = $link.attr('href');

            var unpackedUrl = this._unpackUrlParameters(href);

            return unpackedUrl.parameters;
        },
        _markSelectedItemsForFilter: function (filterCode, selectedOptionIndexes) {
            this._super(filterCode, selectedOptionIndexes);
            this.filterState[filterCode] = Object.keys(selectedOptionIndexes);
        },
        _getNextClickConsequenceForItem: function ($optionItem) {
            var params = this._getFilterItemUrlValues($optionItem);
            var filterStates = {};
            var filterParams = {};

            $.each(this.options.sequence, function () {
                if (this in params) {
                    filterParams[this] = params[this];
                }
            });

            $.each(this.filterState, function (filterCode, state) {
                $.each(state, function () {
                    var valueIndex = -1;

                    if (filterCode in filterParams) {
                        valueIndex = filterParams[filterCode].indexOf(this);
                    }

                    if (valueIndex != -1) {
                        filterParams[filterCode].splice(valueIndex, 1);
                        if (!filterParams[filterCode].length) {
                            delete filterParams[filterCode];
                        }
                    } else {
                        if (!(filterCode in filterStates)) {
                            filterStates[filterCode] = [];
                        }

                        filterStates[filterCode].push(this);
                    }
                });
            });

            var filterCodes = Object.keys(filterParams);
            filterCodes = filterCodes.concat(Object.keys(filterStates));
            var filterCode = filterCodes.pop();

            return {
                filterCode: filterCode,
                optionValue: filterCode in filterParams
                    ? filterParams[filterCode].pop()
                    : filterStates[filterCode].pop(),
                removal: Object.keys(filterStates).length > 0
            };
        },
        _updateFilterState: function (filterCode, optionValue, remove) {
            if (remove) {
                var index = this.filterState[filterCode].indexOf(optionValue);
                this.filterState[filterCode].splice(index, 1);

                if (!this.filterState[filterCode].length) {
                    delete this.filterState[filterCode];
                }
            } else {
                if (!(filterCode in this.filterState)) {
                    this.filterState[filterCode] = [];
                }

                this.filterState[filterCode].push(optionValue);
            }
        },
        _toggleOptionMarkerForItem: function ($optionItem) {
            this._super($optionItem);

            var consequence = this._getNextClickConsequenceForItem($optionItem);

            this._updateFilterState(consequence.filterCode, consequence.optionValue, consequence.removal);

            $.each($(this._getOptionLinkSelector()), function (i, link) {
                var href = link.href;
                var unpackedUrl = this._unpackUrlParameters(href);

                if (!(consequence.filterCode in unpackedUrl.parameters)) {
                    unpackedUrl.parameters[consequence.filterCode] = [];
                }

                var index = unpackedUrl.parameters[consequence.filterCode].indexOf(consequence.optionValue);

                if (index == -1) {
                    unpackedUrl.parameters[consequence.filterCode].push(consequence.optionValue);
                } else {
                    unpackedUrl.parameters[consequence.filterCode].splice(index, 1);
                }

                href = this._packUrlParameters(unpackedUrl);
                link.href = href;
            }.bind(this));
        }
    });
})(jQuery);