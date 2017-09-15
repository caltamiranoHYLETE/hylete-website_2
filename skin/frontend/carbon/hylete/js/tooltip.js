;(function ($)
{
    "use strict";

    $.widget('vaimo.tooltip', {
        options: {
            tooltipContainer: '.js-tooltip-container',
            tooltip: '.js-tooltip',
            tooltipLink: '.js-tooltip-link',
            tooltipActiveClass: 'tooltip--active',
            tooltipClose: '.js-tooltip-close'
        },
        _create: function ()
        {
            var that = this;

            $(this.options.tooltipLink).on('mouseenter mouseleave', function(e) {
                e.preventDefault();
                $(this)
                    .closest(that.options.tooltipContainer)
                    .find(that.options.tooltip)
                    .toggleClass(that.options.tooltipActiveClass);
            });

            $(this.options.tooltipLink).on('touchstart', function(e) {
                e.preventDefault();

                var $currentTooltip = $(this).closest(that.options.tooltipContainer).find(that.options.tooltip);
                var $currentTooltipLink = $(this);
                $('.tooltip').not($currentTooltip).removeClass(that.options.tooltipActiveClass);
                $(that.options.tooltipLink).not($currentTooltipLink).removeClass('is-touched');

                if($(this).hasClass('is-touched')) {
                    $(this)
                        .removeClass('is-touched')
                        .closest(that.options.tooltipContainer)
                        .find(that.options.tooltip)
                        .removeClass(that.options.tooltipActiveClass);
                }
                else {
                    $(this)
                        .addClass('is-touched')
                        .closest(that.options.tooltipContainer)
                        .find(that.options.tooltip)
                        .addClass(that.options.tooltipActiveClass);
                }
            });

            $(this.options.tooltipClose).on('touchstart', function(e) {
                e.preventDefault();

                $(this)
                    .closest(that.options.tooltipContainer)
                    .find(that.options.tooltip)
                    .removeClass(that.options.tooltipActiveClass)
                    .prev().removeClass('is-touched');
            });

        }
    });

    return $.vaimo.tooltip;
})(jQuery);