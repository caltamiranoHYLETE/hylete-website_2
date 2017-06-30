jQuery(document).ready(function ($) {
    "use strict";

    var isTouch = (('ontouchstart' in window) || (navigator.msMaxTouchPoints > 0));

    if (!isTouch) {
        $('body').addClass('not-touch');
    }

    $('select').not('#region_id, #country, .one-page-checkout select').selectOrDie();
    $(document).vaimoPrevNextLocal();
    $(document).vaimoToggle();

    $(document).on('click', '.show-mobile-filter', function() {
        $(this).toggleClass('active');
        $('.filter').slideToggle();
    });

    $(document).on('click', '.account-login .toggle-login-container .toggle-login-form', function() {
        $('.account-login .registered-users').stop().slideToggle();
    });

    $('.cms-page-view .toggle h3').click(function() {
        $(this).toggleClass('active');
        $(this).closest('.toggle').find('.content-data').slideToggle('fast');
    });

    var menuSearch = $('.menu-search'),
        $mobileSearch = $('.mobile-search'),
        $mobileSearchWrapper = $('.mobile-search-wrapper'),
        searchLink = menuSearch.find('>a'),
        searchWrapper = menuSearch.find('.search-wrapper'),
        searchInput = searchWrapper.find('input'),
        activeClass = 'active';

    var menuNav = $('.nav-container #nav'),
        menuNavLink = $('.mobile-nav');

    $mobileSearch.on('click', function() {
        menuNav.hide();
        menuNavLink.removeClass('active');

        $(this).toggleClass(activeClass);
        $mobileSearchWrapper.toggleClass(activeClass);
    });

    searchLink.on('click', function(e) {
        e.stopPropagation();
        e.preventDefault();

        var searchPanelWidth = $('.right', '#header').width() - 18;
        searchWrapper.css('width', searchPanelWidth);

        var searchVisibility = !searchWrapper.hasClass(activeClass);

        menuSearch.toggleClass(activeClass, searchVisibility);

        if (searchVisibility) {
            searchInput.focus();
        }
    });

    searchInput.on('focusout', function() {
        setTimeout(function() {
            menuSearch.removeClass(activeClass);
        }, 200);
    });

    if ($.isFunction($({}).swipeleft) && $.isFunction($({}).swiperight)) {
        $('.carousel').swiperight(function() {
            $(this).carousel('prev');
        }).swipeleft(function() {
            $(this).carousel('next');
        });
    }

    var notifyForm = new VarienForm('notify_data');
    $('#notify_data').on('submit', function(e) {
        e.preventDefault();

        var msg = $(this).find('.msg'),
            url = $(this).attr('action'),
            data = $(this).serialize();

        msg.hide();

        if (notifyForm.validator.validate()) {
            $.ajax({
                method: "POST",
                url: url,
                data: data,
                dataType: 'json'

            }).done(function(response) {
                msg.html(response.message).show();
            });
        }
    });

    $(document).on('click', '.sort-by a', function() {
        if (carbon.getCurrentGrid() == 'xs' || carbon.getCurrentGrid() == 'sm') {
            activeToggle = 'sort-by';
        }
    });


    //Cart increase/decrease qty for product
    $('.cart-table .qty-control').click(function() {
        var el = $(this),
            form = el.closest('form'),
            input = el.closest('tr').find('input.qty'),
            qty = input.val();

        if (el.hasClass('qty-minus')) {
            qty--;
        } else {
            qty++;
        }

        input.val(qty);
        form.submit();
    });

    $('input.qty').on('keyup blur', function(e) {
        if(!$(this).parents('#bss_configurablegridview').length) {
            if (e.keyCode == 13 || e.type == 'blur') {
                $(this).closest('form').submit();
            } else {
                $(this).closest('tr').find('input.qty').val($(this).val());
            }
        }
    });

    $(document).on('click', '.cvv-what-is-this', function(e) {
        e.preventDefault();
        $.fancybox('#payment-tool-tip');
    });

    /* Append sweet tooth points value in header if available. Issue caused by fpc */
    var headerWelcomeText = $('.welcomeTextNormalHeader'),
            mobileWelcomeText = $('.welcomeTextMobileHeader'),
            sweetToothPoints = $('#sweet-tooth-points-value');
    if (sweetToothPoints.val() != '') {
        headerWelcomeText.text(headerWelcomeText.text() + ' ' + sweetToothPoints.val());
        mobileWelcomeText.text(mobileWelcomeText.text() + ' ' + sweetToothPoints.val());
    }

    /* Set grid height on category page when all content has loaded */
    $(window).on('load', function() {
        $('.products-grid', '.category-products').setEqualHeight();
    });
    $(window).on('resize', function() {
        updateEqualHeight();
    });

    var updateEqualHeight = debounce(function() {
        $('.products-grid', '.category-products').setEqualHeight();
    }, 200);

    $(document).on('quickcheckout:paymentload_after', function() {
        var $contentContainer = $('.content-container');

        if (!$('#p_method_free').length) {
            $contentContainer.addClass('show-store-credit');
        } else if ($contentContainer.hasClass('show-store-credit')) {
            $contentContainer.removeClass('show-store-credit');
        }
    });

    /*  Debounce function
     Returns a function, that, as long as it continues to be invoked, will not
     be triggered. The function will be called after it stops being called for
     N milliseconds. If `immediate` is passed, trigger the function on the
     leading edge, instead of the trailing.
     */
    function debounce(func, wait, immediate) {
        var timeout;
        return function() {
            var context = this, args = arguments;
            var later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            var callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    }
});

(function($, window, undefined) {
    "use strict";
    if ($.fn.ajaxProductList !== undefined) {
        $.widget('vaimo.ajaxProductList', $.vaimo.ajaxProductList, {
            _historyUpdateEnabled: function () {
                return this._super.apply(this, arguments) && !this.customRender;
            },
            _complete: function (groupName) {
                this._super.apply(this, arguments);
                if (activeToggle) {
                    $('.filter').show();
                    $('.show-mobile-filter').addClass('active');
                    $('[data-togglelink="' + activeToggle + '"]').addClass('active');
                    $('[data-togglecontent="' + activeToggle + '"]').show().addClass('active');
                }
            }
        });
    }
})(jQuery, window, undefined);