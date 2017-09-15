(function ($) {
    "use strict";

    $.widget('vaimo.mofSelector', {
        isTouchDevice: false,
        options: {
            sequence: {},
            selectors: {
                layerView: '#narrow-by-list',
                filterBlock: 'ol',
                filterOption: 'li'
            },
            classes: {
                optionMarkerUnchecked: 'icon-unchecked',
                optionMarkerChecked: 'icon-check',
                optionChecked: 'option-checked'
            }
        },
        _create: function () {
            this._generateSelectorHelpers();
            this._setIsTouchDevice();
            this.initiate();
        },
        _generateSelectorHelpers: function () {
            var classes = this.options.classes;
            var selectors = this.options.selectors;

            $.extend(this.options.selectors, {
                optionMarkerUnchecked: '.' + classes.optionMarkerUnchecked,
                optionMarkerChecked: '.' + classes.optionMarkerChecked,
                filterBlocks: [selectors.layerView, selectors.filterBlock].join(' ')
            });

            $.extend(this.options.selectors, {
                optionMarkerAny: [selectors.optionMarkerUnchecked, selectors.optionMarkerChecked].join(',')
            });
        },
        getAllFilterEntities: function () {
            return $(this.options.selectors.filterBlocks);
        },
        initiate: function () {},
        sequence: function (sequence) {
            if (typeof sequence == 'undefined') {
                return this.options.sequence;
            }

            this.options.sequence = sequence;

            return this;
        },
        apply: function (activeFilters) {
            $.each(activeFilters, function (filterCode, selectedOptionIndexes) {
                this._markSelectedItemsForFilter(filterCode, selectedOptionIndexes);
            }.bind(this));
        },
        getFilterEntity: function (filterCode) {
            var filterEntities = this.getAllFilterEntities();
            var filterIndex = this.options.sequence.indexOf(filterCode);

            return filterEntities[filterIndex];
        },
        _markSelectedItemsForFilter: function (filterCode, selectedOptionIndexes) {
            var filter = this.getFilterEntity(filterCode);
            var options = $(filter).find(this.options.selectors.filterOption);

            $.each(selectedOptionIndexes, function (key, value) {
                this._markAsSelected(options[value]);
            }.bind(this));
        },
        _replaceClass: function (optionMarkerEntity, add, remove) {
            var $markerEntity = $(optionMarkerEntity);
            $markerEntity.removeClass(remove);
            $markerEntity.addClass(add);
        },
        _markAsSelected: function (optionEntity) {
            var classes = this.options.classes;

            $(optionEntity).addClass(classes.optionChecked);

            var $selectionMarker = $(optionEntity).find(this.options.selectors.optionMarkerAny);
            this._replaceClass($selectionMarker, classes.optionMarkerChecked, classes.optionMarkerUnchecked);
        },
        _markAsNotSelected: function (optionEntity) {
            var classes = this.options.classes;

            $(optionEntity).removeClass(classes.optionChecked);

            var $selectionMarker = $(optionEntity).find(this.options.selectors.optionMarkerAny);
            this._replaceClass($selectionMarker,classes.optionMarkerUnchecked, classes.optionMarkerChecked);
        },
        _isOptionSelected: function ($optionItem) {
            return $optionItem.find(this.options.selectors.optionMarkerChecked).length;
        },
        _toggleOptionMarkerForItem: function ($optionItem) {
            if (this._isOptionSelected($optionItem)) {
                this._markAsNotSelected($optionItem);
            } else {
                this._markAsSelected($optionItem);
            }
        },
        _setIsTouchDevice: function () {
            if (typeof(jQuery.support.touch) !== 'undefined' && jQuery.support.touch === true){
                this.isTouchDevice = true;
            }
        }
    });
})(jQuery);