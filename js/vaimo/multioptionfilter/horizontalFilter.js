(function ($) {
    "use strict";

    /**
     * Base functionality for the drop-downs
     */
    $.widget('vaimo.mofSelector', $.vaimo.mofSelector, {
        options: {
            classes: {
                shownOptions: 'multioptionfilter-show'
            },
            eventNames: {
                filterTitleToggleOptions: 'click',
                filterTitleOpenOptions: 'mouseover',
                filterTitleCloseOptions: 'mouseleave'
            }
        },
        _generateSelectorHelpers: function() {
            var selectors = this.options.selectors;
            var classes = this.options.classes;

            $.extend(this.options.selectors, {
                filterTitles: [selectors.layerView, 'dt'].join(' '),
                shownOptions: [selectors.layerView, '.' + classes.shownOptions].join(' ')
            });

            this._super();
        },
        initiate: function() {
            if (this.isTouchDevice === true) {
                this.initEventHandlersForTouch();
            } else {
                this.initEventHandlersForNonTouch();
            }

            this._super();
        },
        initEventHandlersForTouch: function() {
            var $document = $(document);

            $document.off(this.options.eventNames.filterTitleToggleOptions, this.options.selectors.filterTitles, $.proxy(this.onFilterTitleToggleOptions, this));
            $document.on(this.options.eventNames.filterTitleToggleOptions, this.options.selectors.filterTitles, $.proxy(this.onFilterTitleToggleOptions, this));
        },
        initEventHandlersForNonTouch: function() {
            var $document = $(document);

            $document.on(this.options.eventNames.filterTitleOpenOptions, this.options.selectors.filterTitles, $.proxy(this.onFilterTitleOpenOptions, this));
            $document.on(this.options.eventNames.filterTitleCloseOptions, this.options.selectors.shownOptions, $.proxy(this.onFilterTitleCloseOptions, this));
        },
        _getOptionWrapper: function(filterTitle) {
            return $(filterTitle).next();
        },
        _showFilterOptions: function($filterTitle, $optionsWrapper) {
            var classes = this.options.classes;

            $filterTitle.addClass(classes.shownOptions);
            $optionsWrapper.addClass(classes.shownOptions);
            $optionsWrapper.find(this.options.selectors.filterBlock).addClass(classes.shownOptions);
        },
        _openFilter: function(filterTitle) {
            var $filterTitle = $(filterTitle);
            var $optionsWrapper = this._getOptionWrapper(filterTitle);
            var titlePosition = $filterTitle.position();

            $optionsWrapper.css({
                left: titlePosition.left,
                top: titlePosition.top + $filterTitle.height()
            });

            this._showFilterOptions($filterTitle, $optionsWrapper);
        },
        _closeAllFilters: function() {
            var selectors = this.options.selectors;
            $(selectors.shownOptions).removeClass(this.options.classes.shownOptions);
        },
        _closeFilterByNode: function($node) {
            var selectors = this.options.selectors;
            var classes = this.options.classes;

            if ($node.hasClass(classes.shownOptions) || $node.parents(selectors.shownOptions).length) {
                return false;
            }

            $(selectors.shownOptions).removeClass(classes.shownOptions);

            return true;
        },
        onFilterTitleOpenOptions: function(event) {
            this._closeAllFilters();
            this._openFilter($(event.currentTarget));
        },
        onFilterTitleCloseOptions: function(event) {
            var $toElement = $(event.toElement);
            if (!$toElement.length) {
                $toElement = $(event.relatedTarget);
            }

            this._closeFilterByNode($toElement);
        },
        onFilterTitleToggleOptions: function(event) {
            var selectors = this.options.selectors;
            var showOptionClass = this.options.classes.shownOptions;
            var $eventTarget = $(event.currentTarget);

            if ($eventTarget.hasClass(showOptionClass)) {
                $eventTarget.removeClass(showOptionClass);
                $(selectors.shownOptions).removeClass(showOptionClass);
            } else {
                $(selectors.shownOptions).removeClass(showOptionClass);
                this._openFilter($eventTarget);
            }
        }
    });

    /**
     * Restoring the visual state of the drop-downs
     */
    $.widget('vaimo.mofSelector', $.vaimo.mofSelector, {
        _closeFilterByNode: function($node) {
            if (this._super($node)) {
                $.vaimo.mofSelector.shownFilters = [];
            }
        },
        _closeAllFilters: function() {
            this._super();

            $.vaimo.mofSelector.shownFilters = [];
        },
        _openFilter: function(filterTitle) {
            this._super(filterTitle);

            var shownFilters = [];
            var sequence = this.sequence();
            var shownOptionsClass = this.options.classes.shownOptions;

            $(this.options.selectors.filterTitles).each(function(i, titleItem) {
                if (!$(titleItem).hasClass(shownOptionsClass)) {
                    return;
                }

                shownFilters.push(sequence[i]);
            });

            $.vaimo.mofSelector.shownFilters = shownFilters;
        },
        apply: function(activeFilters) {
            this._super(activeFilters);

            var sequence = this.sequence();
            var shownFilters = $.vaimo.mofSelector.shownFilters;

            if (!$.vaimo.mofSelector.shownFilters) {
                shownFilters = [];
            }

            $(this.options.selectors.filterTitles).each(function(i, titleItem) {
                var filterCode = sequence[i];

                if (shownFilters.indexOf(filterCode) < 0) {
                    return;
                }

                this._openFilter(titleItem);
            }.bind(this));
        }
    });
})(jQuery);