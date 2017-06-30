(function ($) {
    "use strict";

    if ("ajaxProductList" in $.vaimo) {
        $.widget("vaimo.ajaxProductList", $.vaimo.ajaxProductList, {
            _applyResponse: function(transport) {
                this._super(transport);
                
                carbon.adjustHeightGrid(false, true);
            }
        });
    }
})(jQuery);