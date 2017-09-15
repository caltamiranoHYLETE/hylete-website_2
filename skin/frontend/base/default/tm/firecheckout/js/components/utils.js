var FC = FC || {};
FC.Utils = {
    fireEvent: function(el, eventName) {
        if ('createEvent' in document) {
            var evt = document.createEvent('HTMLEvents');
            evt.initEvent(eventName, true, true);
            el.dispatchEvent(evt);
        } else {
            el.fireEvent('on' + eventName);
        }
    },

    debounce: function(func, wait, immediate) {
        var timeout;
        return function() {
            var context = this,
                args = arguments,
                callNow = (immediate && !timeout);

            clearTimeout(timeout);

            timeout = setTimeout(function() {
                timeout = null;
                if (!immediate) {
                    func.apply(context, args);
                }
            }, wait);

            if (callNow) {
                func.apply(context, args);
            }
        };
    },

    shake: function(el) {
        if (!el) {
            return;
        }

        el.addClassName('fc-shake');

        'webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend'
            .split(' ')
            .each(function(eventName) {
                el.observe(eventName, function() {
                    $(this).removeClassName('fc-shake');
                });
            });
    }
};
