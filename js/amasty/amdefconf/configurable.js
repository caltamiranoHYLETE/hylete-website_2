var amDefConf = new Class.create();

amDefConf.prototype = {
    initialize: function()
    {
    },
    
    select: function()
    {
        var separatorIndex = window.location.href.indexOf('#');
        /* disabled on cart edit page or preselected from category*/
        if ($$('body')[0].hasClassName('checkout-cart-configure') || separatorIndex != -1) {
            return;
        }

        var args = $A(arguments);
        $$('.product-options .super-attribute-select').each(function(select, i) {
            if (args[i]) {
                select.value = args[i];
                spConfig.configureElement(select);

                /*compatibility with magento swatches*/
                var swatchImage = jQuery("#swatch" + args[i] + ' span.swatch-label');
                if (swatchImage) {
                    swatchImage.trigger("click");
                }
            }
        });
    },

    preselectFirst: function()
    {
        /* disabled on cart edit page or preselected from category*/
        if ('undefined' !== typeof(spConfig)
            && !spConfig.values
            && !$$('body')[0].hasClassName('checkout-cart-configure')
        ) {
            spConfig.settings.each(function(select){
                if ( select.options.length > 1 && !select.options[1].selected) {
                    select.options[1].selected = true;
                    spConfig.configureElement(select);

                    /*compatibility with magento swatches*/
                    var swatchImage = jQuery("#swatch" + select.value + ' span.swatch-label');
                    if(swatchImage) swatchImage.trigger("click");
                }
            });
        }
    }
};
