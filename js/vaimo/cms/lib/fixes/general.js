;(function($) {
    'use strict';

    /**
     * FIX: hide Range.detach() deprecation warning. The function no longer does anything.
     */
    $.vaimoRaptorOriginalMethods = $.vaimoRaptorOriginalMethods || {};

    $.vaimoRaptorOriginalMethods.Range = $.vaimoRaptorOriginalMethods.Range || {};
    $.vaimoRaptorOriginalMethods.Range.detach = Range.prototype.detach || {};
    Range.prototype.detach = function() {};

    /**
     * FIX: hide "Discontiguous selection is not supported." error on Chrome when Raptor detects browser capabilities.
     */
    $.vaimoRaptorOriginalMethods.window = $.vaimoRaptorOriginalMethods.window || {};
    $.vaimoRaptorOriginalMethods.getSelection = window.getSelection;

    // Source: https://github.com/timdown/rangy/blob/master/src/core/wrappedselection.js#L164
    // Doing the original feature test here in Chrome 36 (and presumably later versions) prints a
    // console error of "Discontiguous selection is not supported." that cannot be suppressed. There's
    // nothing we can do about this while retaining the feature test so we have to resort to a browser
    // sniff. I'm not happy about it. See
    window.getSelection = function() {
        var selection = $.vaimoRaptorOriginalMethods.getSelection.apply(this);

        if (!selection.addRange.vcsmOldMethod) {
            var _addRange = selection.addRange;

            selection.addRange = function() {
                var chromeMatch = window.navigator.appVersion.match(/Chrome\/(.*?) /);

                if (chromeMatch && parseInt(chromeMatch[1]) >= 36 && this.rangeCount > 0) {
                    return null;
                }

                return _addRange.apply(this, arguments);
            };

            selection.addRange.vcsmOldMethod = _addRange;
        }

        return selection;
    };

})(jQuery);