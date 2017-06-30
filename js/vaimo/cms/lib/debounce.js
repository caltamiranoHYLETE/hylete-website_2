;(function (namespace) {
    'use strict';

    if (typeof namespace.debounce === 'function') {
        return;
    }

    namespace.debounce = function (fn, delay) {
        var timer = null;

        return function () {
            var context = this, args = arguments;

            clearTimeout(timer);

            timer = setTimeout(function () {
                fn.apply(context, args);
            }, delay);
        };
    }
})(window);