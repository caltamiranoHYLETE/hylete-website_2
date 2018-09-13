jQuery(document).ready(function ($) {
    "use strict";
    /* Top announcement bar */
    jQuery('#top-announcement').owlCarousel({
        loop: true,
        margin: 40,
        responsiveClass: true,
        dots: false,
        items: 1,
        singleItem: true,
        nav: true,
        navText: [
            '<i class="fa fa-angle-left"></i>',
            '<i class="fa fa-angle-right"></i>'
        ]
    });

});