jQuery(document).ready(function ($) {
    $('#product-carousel').carousel({
        interval: false
    });

    $('[data-fancybox-trigger="sizeguide"]').fancybox({
        'overlayShow': true,
        'closeBtn' : false
    });
    $('#fancybox-sizeguide a.close-btn').click(function(){
        $.fancybox.close();
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

    /** store resize status */
    var resizeStatus;

    /**
     * Refresh MagicScroll plugin
     *
     * @return void
     */
    function resizeThumbnails(){
        var elem = $('.product-essential .MagicScroll');
        if (elem.length) {
            var data = elem.data('options'),
                options = getOptions(data),
                newOrientation = getNewOrientation();

            if (options.orientation !== newOrientation) {
                var mergedData = $.extend(options, {'orientation': newOrientation});
                var strings = [];
                $.each(mergedData, function(index,value) {
                    var str = index + ":" + value;
                    strings.push(str);
                });
                var result = strings.join(";") + ';';
                elem.attr('data-options',result);
                elem.removeData('options');
                MagicScroll.refresh();
            }
        }
    }

    /**
     * Retrieve new Orientation according to breakpoint
     *
     * @return {string}
     */
    function getNewOrientation() {
        // @media only screen and (max-width: 767px) = xs
        if (window.innerWidth > 767) {
            return 'vertical';
        }
        return 'horizontal';
    }

    /**
     *  Retrieve options as object
     *
     * @param {string} data
     * @returns {{}}
     */
    function getOptions(data) {

        return data.split(";").reduce(function(obj, str, index) {
            let strParts = str.split(":");
            if (strParts[0] && strParts[1]) {
                obj[strParts[0].replace(/\s+/g, '')] = strParts[1].trim();
            }
            return obj;
        }, {});

    }

    window.onresize = function() {
        clearTimeout(resizeStatus);
        resizeStatus = setTimeout(function() {
            resizeThumbnails();
        }, 100);
    };

}); 