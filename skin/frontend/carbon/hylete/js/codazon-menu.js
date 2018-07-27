(function ($) {
    $(document).ready(function() {
        $('.item.level0').hover(function() {
            $('.item.level0').not(this).find('a').addClass('disabled-link');
        }, function() {
            $('.item.level0').not(this).find('a').removeClass('disabled-link');
        });
    })
})(jQuery);