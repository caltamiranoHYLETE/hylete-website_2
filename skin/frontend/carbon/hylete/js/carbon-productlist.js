(function ($) {
    "use strict";

    if ("ajaxProductList" in $.vaimo) {
        $.widget("vaimo.ajaxProductList", $.vaimo.ajaxProductList, {
            _applyResponse: function(transport) {
                this._super(transport);

                carbon.adjustHeightGrid(false, true);
                this._scrollToTop();
            },
            _scrollToTop: function () {
                var topPosition = 0;
                try {
                    this._getEnabledSelectors(this.options.eventSelectors.anchors).each(function (element) {
                        if ($(element)) {
                            var position = $(element).position();
                            if (position.top > topPosition) {
                                topPosition = position.top;
                            }
                        }
                    });
                } catch( e ) {
                    console.log('Cannot find the top position: ' + e.message)
                }

                $('html, body').animate({
                    scrollTop: topPosition
                }, 1000);
            }
        });
    }
})(jQuery);
