jQuery(document).ready(function ($) {
    $('#product-carousel').carousel({
        interval: false
    });
   
    $('.more-views a').on('click', function(e) {
        e.preventDefault();
        
        var large = $(this).data('large'),
            zoom = $(this).attr('href');
        
        $('.main-product-image a:first-child').attr('href', zoom);    
        $('.main-product-image a:first-child img').attr('src', large);
    });
    
    $('.main-product-image a').on('click', function(e) {
        e.preventDefault();
        
        var url = $(this).attr('href');
        
        $('.catalog-product-view .fancybox-gallery a[href="' + url + '"]').click();
    });
    
    $(".catalog-product-view .fancybox-gallery a").fancybox({
        'hideOnContentClick': true
    });

    // on page load...
    initiateProgressBar();

    function initiateProgressBar() {

        var $getPercent = $('.product-shop .progress-wrap').data('progress-percent'),
            animationLength = 1000;
        
        // .stop() used to prevent animation queueing
        $('.progress-bar').stop().animate({
            width: $getPercent + '%'
        }, animationLength);
    }
}); 