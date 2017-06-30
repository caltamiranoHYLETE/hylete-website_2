;(function ($) {
    "use strict";

    $(document).ready(function() {
        // Helper that returns direction if set breakpoint is passed
        var breakPointHelper = function(passedBreakPoint) {
            var breakPoint = passedBreakPoint,
                currentWidth = $(window).width();

            this.getDirection = function() {
                var newWidth = $(window).width();

                if (newWidth > breakPoint && currentWidth < breakPoint) {
                    currentWidth = newWidth;
                    return 'up';
                } else if (newWidth < breakPoint && currentWidth > breakPoint) {
                    currentWidth = newWidth;
                    return 'down';
                }
            };
        };

        var breakPoint = 992,
            windowWidth = $(window).width(),
            breakPointPassed = new breakPointHelper(breakPoint); // Initiate helper with breakpoint

        // Only initiate the function when on desktop
        if (windowWidth > breakPoint) {
            addCartEvents();

            document.observe("addtocartajax:update", function() {
                addCartEvents();
            });
        }

        $(window).on('resize', function () {
            // Only run the event when passing the breakpoint
            var resizeDirection = breakPointPassed.getDirection();

            if (resizeDirection == 'up') {
                addCartEvents();
            } else if (resizeDirection == 'down') {
                removeCartEvents();
            }
        })
    });

    function addCartEvents() {
        var $cartFilter = $('.headercart-filter', '.headercart'),
            $cartPanel = $('#recently-added-container'),
            $cartPanelInner = $('.headercart-inner', '.headercart'),
            $cartFooter = $('.minicart-footer', '.headercart'),
            $cartButton = $('.js-cart-open', '.headercart'),
            $cartClose = $('.js-cart-close', '.headercart'),
            activeClass = 'minicart-is-open';

        $cartButton.on('click', function (e) {
            e.preventDefault();

            //Don't set panel width and height on tablet
            if(carbon.getCurrentGrid() != 'sm') {
                var panelWidth = ($(window).width() - $('.right').offset().left),
                    cartFooterHeight = $cartFooter.outerHeight() + 46; // Add bottom margin

                $cartPanelInner.css('height', 'calc(100% - ' + cartFooterHeight + 'px)');
                $cartPanel.css('width', panelWidth);
            }

            $('body').toggleClass(activeClass);
        });

        $cartClose.on('click', function () {
            $('body').toggleClass(activeClass);
        });

        $cartFilter.on('click', function (e) {
            e.preventDefault();
            $('body').toggleClass(activeClass);
        });
    }

    function removeCartEvents() {
        // Unbind all events
        var $cartFilter = $('.headercart-filter', '.headercart'),
            $cartButton = $('.js-cart-open', '.headercart'),
            $cartClose = $('.js-cart-close', '.headercart');

        $cartButton.off('click');
        $cartClose.off('click');
        $cartFilter.off('click');
    }

})(jQuery);