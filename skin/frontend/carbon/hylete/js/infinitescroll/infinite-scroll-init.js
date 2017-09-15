jQuery(document).ready(function ($) {

    // this scrollLimit should be height from the bottom of the page until the end of the current items block
    var itemsBottom = $(document).height() - $('.js-products').offset().top - $('.js-products').height();

    $.vaimo.infiniteScroll({ scrollLimit: itemsBottom, intervalFrequency: 600, changeUrl: false, productSelector: '.item, .grid-cms-block' });
});