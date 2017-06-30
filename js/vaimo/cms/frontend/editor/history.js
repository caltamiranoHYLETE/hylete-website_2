;(function ($) {
    "use strict";

    $.widget('vaimo.cmsEditorHistory', {
        options: {
            io: false
        },
        _create: function() {
            this.history = [];

            var that = this;

            this.options.io.addHandler('error', function() {
                var lastAction = that.history.pop();

                if (typeof lastAction !== 'function') {
                    return;
                }

                lastAction();
            });
        },
        registerAction: function(observer, action, selector, handle) {
            var that = this;

            var actionTracker = function(event) {
                that.history.push(function() {
                    handle.apply(event.target, [event]);
                });

                handle.apply(event.target, [event]);
            }.bind(this);

            $(observer).on(action, selector, actionTracker);
        }
    });
})(jQuery);