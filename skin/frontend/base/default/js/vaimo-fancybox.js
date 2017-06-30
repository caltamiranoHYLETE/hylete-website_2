jQuery(document).ready(function ($) {

    $(".product-img-box a").fancybox({
        'overlayColor': '#000',
        'overlayOpacity': '0.8'
    });

    $(".catalog-product-view .product-image a").fancybox({
        'hideOnContentClick': true
    });
});