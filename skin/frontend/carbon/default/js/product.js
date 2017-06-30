jQuery(document).ready(function ($) {
    "use strict";

    // Review form toggle
    $('#show_review_form').click(function (e) {

        e.preventDefault();

        $(this).hide();
        $('#hide_review_form').show();
        $('#customer-reviews .form-add').show();

    });

    $('#hide_review_form').click(function (e) {

        e.preventDefault();

        $(this).hide();
        $('#show_review_form').show();
        $('#customer-reviews .form-add').hide();
    });

    $('.no-rating > a').on('click', function(e){

        e.preventDefault();

        $('#tab-container .tabs li').removeClass('active');
        $('#tab-container .tab-content > div').hide();
        $('#tab-container a[href*="review"]').parent().addClass('active');
        $('#show_review_form').hide();
        $('#hide_review_form').show();

        $('#reviews-tab').show();
        $('#customer-reviews .form-add').show();

        $('html, body').animate({
            scrollTop: $("#review-form").offset().top - 120
        }, 500);
    });

    // media gallery
    //prevents the fancybox modal from popping up on small devices
    if ($(window).width() <= 767) {
        $('.product-image a').attr('href', 'javascript: void(0)');
    }

    var $thumbnails = $('.more-views li');
    var maxIndex = $thumbnails.length -1;
    var $productImage = $('.product-essential .product-image:visible');

    if ($productImage.length) {
        var currentMainImageId = $productImage.attr('id').split('-').pop();
        var defaultMainImageId = '';
        $thumbnails.each(function() {
            var thumbnailImageId = $(this).attr('id').split('-').pop();
            var thumbnailsLength = $(getImageFullId(thumbnailImageId)).length;

            if (thumbnailsLength < 1) {
                defaultMainImageId = thumbnailImageId;
            }
        });

        if (currentMainImageId == 'default') {
            currentMainImageId = defaultMainImageId;
        }
        setThumbnailHighlight(currentMainImageId);
    }

    var currentPosition = getImageIndex(currentMainImageId);

    function getImageIndex (id) {
        var result = null;

        $thumbnails.each(function (index) {
            if (id == stripImageId(this.id) || stripImageId(this.id) == defaultMainImageId) {
                result = index;
                return false;
            }
        });
        return result;
    };

    function scrollImages (offset) {
        var nextImageIndex = currentPosition + offset;

        if (nextImageIndex in $thumbnails) {
            var imageId = getImageId(nextImageIndex);
            window.setTimeout(showImage(nextImageIndex), 20);
            setThumbnailHighlight(imageId);
        } else if (nextImageIndex > maxIndex){
            window.setTimeout(showImage(0), 20);
            setThumbnailHighlight(getImageId(0));
        } else if (nextImageIndex < 0) {
            window.setTimeout(showImage(maxIndex), 20);
            setThumbnailHighlight(getImageId(maxIndex));
        }
    };

    function showImage (position) {
        $('.product-essential .product-image').addClass('hidden').hide();

        var $imageToBeShown = $(getImageFullId(getImageId(position)));

        if ($imageToBeShown.length) {
            $imageToBeShown.removeClass('hidden').show();
            currentPosition = position;
        } else {
            $('#main-image-default').removeClass('hidden').show();
            currentPosition = position;
        }
    };

    function setThumbnailHighlight (id) {
        $('.more-views li').fadeTo(20, 0.5);
        $('#thumbnail-image-'+id).fadeTo(1, 1);
    };

    function getImageId (index) {
        return stripImageId($thumbnails[index].id);
    };

    function getImageFullId (id) {
        return '#main-image-' + id;
    };

    function stripImageId (fullId) {
        return fullId.split('-').pop();
    };

    $('.more-views').on('click', 'li', function() {
        $thumbnails = $('.more-views li');
        var clickedThumbnailIndex = $thumbnails.index(this);

        window.setTimeout(showImage(clickedThumbnailIndex), 20);
        setThumbnailHighlight(getImageId(clickedThumbnailIndex));
    });

    if ($thumbnails.length > 1) {
        var $imageOverlay = $('.product-image');

        $imageOverlay.on('swipeleft', 'img', function() {
            scrollImages(1);
        });

        $imageOverlay.on('swiperight', 'img', function() {
            scrollImages(-1);
        });

        $('.right-arrow').on('click', 'span', function() {
            scrollImages(1);
        });

        $('.left-arrow').on('click', 'span', function() {
            scrollImages(-1);
        });
    }
});