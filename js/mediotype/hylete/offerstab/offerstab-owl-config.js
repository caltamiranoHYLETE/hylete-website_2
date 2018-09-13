jQuery.noConflict();

jQuery(document).ready(function ($) {
    jQuery('#offerstab').owlCarousel({
        loop: true,
        margin: 40,
        responsiveClass: true,
        responsive: {
            0: {
                items: 1,
                nav: false
            },

            600: {
                items: 2,
                nav: false
            },

            1000: {
                items: 4,
                nav: true,
                loop: false
            }
        }
    })
});
