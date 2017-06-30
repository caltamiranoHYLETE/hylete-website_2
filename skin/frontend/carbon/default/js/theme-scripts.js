jQuery(document).ready(function ($) {
    carbon.init();

    if (carbon.getResponsiveEnabled()) {
        carbonMenu.init();

        // Toggle footer boxes on phone
        $('#footer h5.heading').click(function () {
            if (carbon.getCurrentGrid() === 'xs') {
                $(this).closest('div').find('.content').toggle();
                $(this).find('i').toggle();
            }
        });
    }
});

// Adding arrow indicator to validation advice message
Validation._vaimoCarbonCreateAdviceOrg = Validation.createAdvice;
Object.extend(Validation, {
    createAdvice: function(name, elm, title, error) {
        var advice = this._vaimoCarbonCreateAdviceOrg(name, elm, title, error);
        jQuery(advice).prepend('<span class="icon-arrow-up"></span>');
        return advice;
    }
});