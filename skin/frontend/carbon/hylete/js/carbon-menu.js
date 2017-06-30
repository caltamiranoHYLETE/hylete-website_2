var carbonMenu = (function ($) {
    "use strict";
    var nav,
        phoneMenu,
        jsToggleNav,
        jsToggleSearch,
        searchForm,
        searchIconMobile,
        hasInitMobileMenu;

    function init() {
        nav = $('#nav');

        // For nav on tablet. To be able to use drop down properly. Use array to apply limitations on which grid should not implement this.
        nav.find('.level0.parent').doubleTapToGo(['xs','sm']);

        //Vertical Navigation
        $('.expandlink').click( verticalNav );

        //Only init mobile menu if grid is "xs"
        if (carbon.getCurrentGrid() === 'xs' || carbon.getCurrentGrid() === 'sm') {
            initMobileMenu();
        }

        $(document).on("gridChanged", function(e) {
            if (carbon.getCurrentGrid() === 'xs' || carbon.getCurrentGrid() === 'sm') {
                initMobileMenu();
                $('[data-togglecontent]').hide();
            }
        });
    }

    function initMobileMenu(){
        if (hasInitMobileMenu === true) {
            return;
        }

        hasInitMobileMenu = true;
        phoneMenu = $('#js-phone-menu');
        jsToggleNav = $('#toggle-nav');
        jsToggleSearch = $('#js-toggle-search');
        searchForm = $('#header .mobile-search-wrapper');
        searchIconMobile = $('#header .mobile-search');

        if ( nav.length ) {
            FastClick.attach( document.getElementById( "nav" ) );
        }

        if ( phoneMenu.length ) {
            FastClick.attach( document.getElementById( "js-phone-menu" ) );
        }

        registerMobileListeners();
    }

    function registerMobileListeners(){
        jsToggleNav.click( toggleNav );
        $('.toggle-sub-menu').click( toggleSubMenu );
        jsToggleSearch.click( toggleSearch );
    }

    function toggleNav(e){
        e.preventDefault();

        nav.toggle();
        searchForm.removeClass('active');
        searchIconMobile.removeClass('active');
        itemActive( jsToggleNav );
    }

    function toggleSubMenu(e){
        e.preventDefault();
        
        var subMenu = $(this).siblings('.menu-vlist');
        var mainCategory = $(this).closest('li.level0');

        if (subMenu.hasClass(':visible') || subMenu.is(':visible')) {
            subMenu.removeClass('mobile-show').addClass('mobile-hide');
            subMenu.find('.icon-angle-down').toggleClass('icon-angle-down icon-angle-right');
        } else {
            //Hide all main items
            mainCategory.siblings('li.level0').children('.menu-vlist').removeClass('mobile-show').addClass('mobile-hide');
            mainCategory.siblings('li.level0').find('.icon-angle-down').toggleClass('icon-angle-down icon-angle-right');
            subMenu.closest('ul.level0.menu-vlist').find('.menu-vlist').removeClass('mobile-show');
            subMenu.closest('ul.level0.menu-vlist').find('.icon-angle-down').toggleClass('icon-angle-down icon-angle-right');
            //Hide the active category
            $('li.level1.parent.active').removeClass('active');

            //Show the chosen one
            subMenu.addClass('mobile-show').removeClass('mobile-hide');
        }

        // Toggle arrow on this click
        $(this).find('span').toggleClass('icon-angle-down icon-angle-right');
    }

    function toggleSearch(e){
        e.preventDefault();

        searchForm.toggle();
        nav.hide();
        itemActive( jsToggleSearch );
    }

    function itemActive(el){

        var isVisible = el.hasClass('active');

        phoneMenu.find('li a').removeClass('active');

        el.toggleClass('active', !isVisible);
    }

    function verticalNav(e){
        e.preventDefault();

        var parent = $(this).closest('.vertical-nav-item');

        if (parent.hasClass('closed')) {
            if (!parent.find('.vertical-nav-item').length) {
                var link = parent.find('a');
                if (link.length) {
                    link[0].click();
                }
            } else {
                parent.toggleClass('closed open')
                    .find('ul').show();
            }
        } else {
            parent.toggleClass('open closed')
                    .find('ul').hide();
        }
    }

    return {
        init: init
    };

})(jQuery);