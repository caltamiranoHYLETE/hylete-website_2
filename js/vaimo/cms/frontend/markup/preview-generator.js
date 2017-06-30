;(function ($) {
    "use strict";

    $.widget('vaimo.markupPreviewGenerator', {
        options: {
            classes: {
                previewNode: 'preview',
                calculatedStyles: 'vcms-preview-static-styles'
            }
        },

        create: function($source, completionCallback) {
            var $visibleSource = $source.filter(':visible');

            var previewItems = [];

            $visibleSource.each(function(index, $item) {
                var preview = this._createElementClone($item);

                $(preview).addClass(this.options.classes.previewNode);

                $(preview).css('margin', 0);

                previewItems.push(preview);
            }.bind(this));

            var $previewItems = $(previewItems);

            completionCallback($previewItems);
        },

        _createElementClone: function(element) {
            var clone = element.cloneNode(true),
                realElements,
                cloneElements,
                realStyles;

            realElements = this.getElementsArray(element);
            cloneElements = this.getElementsArray(clone);

            realStyles = realElements.map(function (el) {
                return getComputedStyle(el).cssText;
            });

            cloneElements.forEach(function (el, i) {
                el.style.cssText = realStyles[i];
                try {
                    el.className += " " + this.options.classes.calculatedStyles;
                } catch(e) {
                    // Intentionally not reacting to the exception to still continue generating 
                    // the preview even when fatal error is encountered with certain parts of the generation
                }
            }.bind(this));

            return clone;
        },

        getElementsArray: function (element) {
            var elementsArray = [].slice.call(element.querySelectorAll('*'));
            elementsArray.unshift(element);
            return elementsArray;
        }
    });
})(jQuery);
