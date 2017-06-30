var carbon = (function ($) {
    "use strict";

    var newsletterValue, currentGrid, timeOut, productsGrid, productsGridItems,
        gridColumns = [],
        responsiveEnabled = false,
        currentWindowSize = $(window).width();
    
    var config = {
        grid: '.products-grid',
        item: '.item',
        title: 'h5',
        price: '.price-box'
    };

    var init = function(options) {
        // Save config options
        for(var prop in options) {
            if(options.hasOwnProperty(prop)){
                config[prop] = options[prop];
            }
        }

        // Set current grid.
        currentGrid = getCurrentGrid();

        productsGrid = $(config.grid);

        if (productsGrid.length) {
            initProductsGrid();
            if (productsGridItems.length && gridColumns.length === 0){
                saveColumnsPerRow(productsGridItems);
            }
        }
    };

    /**
     * Get current grid system
     */
    var getCurrentGrid = function() {
        // $(window).width() incorrect on tablet Chrome
        var viewportWidth = window.innerWidth || parseInt($(window).width());

        if (responsiveEnabled === false) {
            return 'md';
        } else if (viewportWidth >= 1200) {
            //Large desktop - Desktops
            return 'lg';
        } else if (viewportWidth >= 992) {
            //Medium devices - Desktops
            return 'md';
        } else if (viewportWidth >= 768) {
            //Small devices - Tablets
            return 'sm';
        } else {
            //Extra small devices - Phones
            return 'xs';
        }
    };

    /**
     * Do things when browser window is resized.
     */
    var onWindowResize = function() {
        var gridAfterResize = getCurrentGrid(),
            sizeAfterResize = $(window).width(),
            sizeThreshold = 10,
            sizeDifference = Math.abs(currentWindowSize - sizeAfterResize);

        if (gridAfterResize !== currentGrid || (gridAfterResize == 'xs' && sizeDifference > sizeThreshold)) {
            if (typeof RP_SCW !== 'undefined') {
                RP_SCW.i();
            }
        }

        // Check if the grid has changed.
        if (gridAfterResize !== currentGrid) {
            currentGrid = gridAfterResize;

            resetInlineStyles();

            // Custom event
            $.event.trigger({
                type: "gridChanged",
                currentGrid: currentGrid
            });
        }

        currentWindowSize = sizeAfterResize;
    };


    /**
     * Replace url parameter.
     */
    var replaceUrlParam = function(url, paramName, paramValue){
        var pattern = new RegExp('('+paramName+'=).*?(&|$)')
        var newUrl=url
        if(url.search(pattern)>=0){
            newUrl = url.replace(pattern,'$1' + paramValue + '$2');
        }
        else{
            newUrl = newUrl + (newUrl.indexOf('?')>0 ? '&' : '?') + paramName + '=' + paramValue
        }
        return newUrl
    };

    /**
     * Set and unset newsletter default value.
     */
    var newsletter = function(selector) {
        var el = $(selector);

        if (el.length) {
            newsletterValue = el.val();

            el.on({
                click: function(){
                    if($(this).val() === newsletterValue){
                        el.val('');
                    }
                },
                blur: function(){
                    if($(this).val() === '') {
                        el.val( newsletterValue );
                    }
                }
            });
        }
    };

    /**
     * Adjust the height of title and price box so they are aligned per row.
     */
    var adjustHeightGrid = function(onWindowResize, reInitDom){
        return false;
    };

    var initProductsGrid = function (){
        productsGrid = $(config.grid);
        if (productsGrid.length) {
            productsGridItems = productsGrid.find(config.item);
        }
    };

    /**
     * Check if we need to change the height.
     * We will only set new height if the items has different height.
     */
    var checkHeight = function (item, i, itemsLength, colsPerRow) {
        item.height = item.item.height();

        // Check if current element height is higher than max height.
        if (item.maxHeight < item.height) {
            item.maxHeight = item.height;
            item.diff++;
        } else if (item.maxHeight !== item.height) {
            item.diff++;
        }

        item.items.push(item.item);

        // If element is the last column in the row or the last element in the list.
        if (item.colPos === colsPerRow || itemsLength === (i+1)){
            // We don't need to set height if all items has same height.
            if(item.diff > 1){
                // Set height for all items in current row.
                setHeight(item.items, item.maxHeight);
            }

            // Reset
            item.items = [];
            item.colPos = 1;
            item.maxHeight = 0;
            item.diff = 0;
        } else {
            item.colPos++;
        }

        return item;
    };

    var setHeight = function (items, height){
        for (var i = 0; i < items.length; i++) {
            $(items[i].css('height', height));
        }
    };

    var saveColumnsPerRow = function (items){
        var classes = items[0].className.split(' ');

        for (var i=0; i <classes.length; i++) {
            // Check if the class is a .col
            if (classes[i].indexOf("col-") !== -1) {
                // Get columns and divide it with 12 to get items per row.
                var cols = 12 / classes[i].substr(7,2);

                // Save items per row for each grid.
                gridColumns[classes[i].substr(4,2)] = cols;
            }
        }
    };

    var resetInlineStyles = function (){
        if (currentGrid == 'md' || currentGrid == 'lg') {
            $("#footer .heading i, #footer .content").removeAttr('style');
            $('#nav').show();
        } else {
            $('#nav').hide();
        }

        $('#search_mini_form').hide();
    };

    var copySitemapFooterMenu = function (){
        var wrapper = $('#sitemap-and-advanced-search-wrapper');
        if(wrapper.find('.bottomlinks').size() < 1) {
            wrapper.append('<div class="content">' + $('#footer-bottom-menu').html() + '</div>');
        }
    };

    return {
        init: function(options) {
            init(options);

            if (responsiveEnabled) {
                copySitemapFooterMenu();

                /**
                 * When browser window is resized.
                 * Timeout for 200ms to avoid calling functions during resizing.
                 */
                $(window).resize(function() {
                    clearTimeout(timeOut);
                    timeOut = setTimeout(function(){
                        onWindowResize();
                    }, 200);
                });
            }
        },
        getCurrentGrid: function() {
            return currentGrid;
        },
        setResponsiveEnabled: function(val) {
            responsiveEnabled = val;
        },
        getResponsiveEnabled: function() {
            return responsiveEnabled;
        },
        newsletter: newsletter,
        adjustHeightGrid: adjustHeightGrid
    };
})(jQuery);