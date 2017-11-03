;(function ($) {
    'use strict';

    $.widget('vaimo.cmsEditorHistory', {
        options: {
            io: false
        },
        _create: function() {
            this.history = [];

            this.options.io.addHandler('error', function() {
                var lastAction = this.history.last();

                if (typeof lastAction !== 'function') {
                    return;
                }

                setTimeout(function() {
                    lastAction(lastAction.attachedState);
                }, 150);
            }.bind(this));
        },
        attachState: function(state) {
            var lastAction = this.history.last();

            lastAction.attachedState = state;
        },
        registerNoop: function() {
            this.history.push(function() {});
        },
        registerAction: function(observer, action, selector, handle) {
            var actionTracker = function(event) {
                this.history.push(function(state) {
                    handle.apply(event.target, [event, state]);
                });

                handle.apply(event.target, [event]);
            }.bind(this);

            $(observer).on(action, selector, actionTracker);
        }
    });
})(jQuery);