;(function($) {
    "use strict";

    $.fn.setEqualHeight = function() {
        // Init variables
        var $elements = $(this).children(),
            rowElements = [],
            currentPosition = 0,
            currentElement = 0;

        if ($elements.length === 0) {
            return;
        }

        var
            topPosition = $elements.eq(0).position().top, // Get position of first element
            talestHeight = 0;

        // Reset height for responsive resize
        $elements.height('auto');

        $elements.each(function(count, element) {
            var $element = $(element);
            // Get position of current element
            currentPosition = $element.position().top;
            // Check if new row and reset everything else push element to the array
            if (currentPosition != topPosition) {
                rowElements.length = 0;
                talestHeight = $element.outerHeight();
                topPosition = currentPosition;
                // Push first element on the new row to the array
                rowElements.push($element);
            } else {
                rowElements.push($element);
                talestHeight = (talestHeight < $element.outerHeight() ? $element.outerHeight() : talestHeight)
            }
            // Loop the array and set the height
            for (currentElement = 0; currentElement < rowElements.length; currentElement++) {
                rowElements[currentElement].height(talestHeight);
            }
        });
    };

})(jQuery);