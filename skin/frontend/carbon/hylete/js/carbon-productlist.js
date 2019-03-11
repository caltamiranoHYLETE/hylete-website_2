(function ($) {
    "use strict";

    if ("ajaxProductList" in $.vaimo) {
        $.widget("vaimo.ajaxProductList", $.vaimo.ajaxProductList, {
            options: {
                eventSelectors: {
                    anchors: {
                        '.block-layered-nav-wrapper .remove-item-list a': true,
                        '.toolbar .sort-by a' : true,
                        '.block-layered-nav a': true,
                        '.category-products .toolbar a': true
                    }
                }
            },
            _applyResponse: function(transport) {
                this._super(transport);

                carbon.adjustHeightGrid(false, true);
                this._scrollToTop();
            },
            _scrollToTop: function () {
                $('html, body').animate({
                    scrollTop: 0
                }, 1000);
            },
            _processBlockData: function(block) {
                if (block.target) {
                    if (block.target.script) {
                        $('body').append(block.html)
                    } else {
                        this._replaceContent(block.target.selector, block.html);
                    }
                }
            }
        });
    }
})(jQuery);
