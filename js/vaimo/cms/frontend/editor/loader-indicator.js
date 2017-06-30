;(function ($) {
    "use strict";

    $.widget('vaimo.loaderIndicator', {
        options: {
            configuration: {
                lines: null, // The number of lines to draw
                length: null, // The length of each line
                width: null, // The line thickness
                radius: null, // The radius of the inner circle
                corners: null, // Corner roundness (0..1)
                rotate: null, // The rotation offset
                direction: null, // 1: clockwise, -1: counterclockwise
                color: null, // #rgb or #rrggbb or array of colors
                speed: null, // Rounds per second
                trail: null, // Afterglow percentage
                shadow: null, // Whether to render a shadow
                hwaccel: null, // Whether to use hardware acceleration
                className: null, // The CSS class to assign to the spinner
                zIndex: null, // The z-index (defaults to 2000000000)
                top: null, // Top position relative to parent
                left: null // Left position relative to parent
            },
            selectors: {
                wrapper: null,
                message: null
            }
        },
        $wrapperEl: null,
        $messageEl: null,
        _create: function() {
            if ($(this.options.selectors.wrapper).find(this.options.configuration.className).length == 0) {
                var target = $(this.options.selectors.wrapper)[0];
                var spinner = new Spinner(this.options.configuration).spin(target);
            }

            this.$wrapperEl = $(this.options.selectors.wrapper);
            this.$messageEl = $(this.options.selectors.message);
        },
        show: function(message) {
            this.$messageEl[0].innerHTML = message;
            this.$wrapperEl.addClass('show');

            return this;
        },
        hide: function() {
            this.$wrapperEl.removeClass('show');
            this.$messageEl[0].innerHTML = '';

            return this;
        }
    });
})(jQuery);