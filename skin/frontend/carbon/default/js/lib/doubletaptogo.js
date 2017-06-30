/*
 By Osvaldas Valutis, www.osvaldas.info
 Available for use under the MIT License
 */
window._dblTapItem = false;
window._dblTapItemParents = [];

(function($, window) {
    $.fn.doubleTapToGo = function(skipForGrids, onlyForGrids, customSkipLogic) {
        var isDoubleTapEvent = function(element) {
            var currentGrid = carbon.getCurrentGrid();

            if (skipForGrids && skipForGrids.indexOf(currentGrid) >= 0)
                return false;

            if (onlyForGrids && onlyForGrids.length && onlyForGrids.indexOf(currentGrid) < 0)
                return false;

            return !customSkipLogic || !customSkipLogic(element);
        };

        var userAgent = navigator.userAgent.toLowerCase();

        if (typeof window.Touch !== "object" && !navigator.msMaxTouchPoints && !userAgent.match(/windows phone os 7/i))
            return false;

        this.each(function() {
            $(this).on('click', function(e) {
                if (!isDoubleTapEvent(this))
                    return true;

                if (this != window._dblTapItem) {
                    e.preventDefault();
                    window._dblTapItem = this;
                    window._dblTapItemParents = $(this).parents();
                }

                return true;
            });
        });
        return this;
    };
})(jQuery, window);

$(document).on( 'click touchstart MSPointerDown', function(e) {
    if (window._dblTapItem && window._dblTapItemParents[e.target] < 0)
        window._dblTapItem = false;
    return true;
});
