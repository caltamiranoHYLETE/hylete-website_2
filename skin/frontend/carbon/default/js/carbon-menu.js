var carbonMenu = (function ($) {
    "use strict";
    var nav, phoneMenu, jsToggleNav, jsToggleSearch, searchForm, hasInitMobileMenu;

    function init() {
        nav = $('#nav');

        // For nav on tablet. To be able to use drop down properly. Use array to apply limitations on which grid should not implement this.
        nav.find('.level0.parent').doubleTapToGo(['xs']);

        //Vertical Navigation
        $('.expandlink').click( verticalNav );

        //Only init mobile menu if grid is "xs"
        if (carbon.getCurrentGrid() === 'xs') {
            initMobileMenu();
        }

        $(document).on("gridChanged", function(e){
            if (carbon.getCurrentGrid() === 'xs') {
                initMobileMenu();
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
        searchForm = $('#search_mini_form');

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
        searchForm.hide();
        itemActive( jsToggleNav );
    }

    function toggleSubMenu(e){
        e.preventDefault();

        var subMenu = $(this).siblings('.menu-vlist');

        if (subMenu.hasClass(':visible') || subMenu.is(':visible')){
            subMenu.removeClass('mobile-show').addClass('mobile-hide');
        } else {
            subMenu.addClass('mobile-show').removeClass('mobile-hide');
        }

        $(this).find('span').toggleClass('icon-plus icon-minus');
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

        if (isVisible === false) {
            el.addClass('active');
        }
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