jQuery(document).ready(function () {
    "use strict";
    var currency_symbol = jQuery('#bss-currency-symbol').val();
if (jQuery('.product-shop .price-box .special-price:first').length < 1) {
    var price_old = jQuery('.product-shop .price-box .price:first').text();
    jQuery('#bss_configurablegridview .qty_att_product').change(function () {
        var total = 0;
        var error = 0;
        jQuery('#bss_configurablegridview .qty_att_product').each(function () {
            var number = jQuery(this).val();
            if (number != 0) {
                error = 1;
            }
            var qty = jQuery(this).next().val();
            total = total * 1 + qty * number * 1;
            jQuery(this).parent().prev().find('span').text(jQuery.number(qty * number, 2, '.', ','));
        });
        var price = total * 1;
        var currency = currency_symbol + jQuery.number(price, 2, '.', ',');
        jQuery('.product-shop .price-box .price').text(currency);
        if (error === 1) {
            jQuery('#bss_configurablegridview .check-configuable-product').val(1);
        } else {
            jQuery('#bss_configurablegridview .check-configuable-product').val('');
        }
    });

    jQuery('.reset-configurablegridview').click(function () {
        jQuery('#bss_configurablegridview .qty_att_product').each(function () {
            jQuery(this).val(0);
            jQuery(this).parent().prev().find('span').text('0.00');
            jQuery('.product-shop .price-box .price').text(price_old);
        })
        jQuery('#bss_configurablegridview .check-configuable-product').val('');
    });

} else {
    var price_old = jQuery('.product-shop .price-box .old-price:first .price').text();
    var special_price_old = jQuery('.product-shop .price-box .special-price:first .price').text();
    jQuery('#bss_configurablegridview .qty_att_product').change(function () {
        var total = 0;
        var total2 = 0;
        var error = 0;
        jQuery('#bss_configurablegridview .qty_att_product').each(function () {
            var number = jQuery(this).val();
            if (number != 0) {
                error = 1;
            }
            var qty = jQuery(this).next().val();
            var qty2 = jQuery(this).next().next().val();
            total = total * 1 + qty * number * 1;
            total2 = total2 * 1 + qty2 * number * 1;
            jQuery(this).parent().prev().find('span').text(jQuery.number(qty * number, 2, '.', ','));
        });
        var price = total * 1;
        var price2 = total2 * 1;
        var currency = currency_symbol + jQuery.number(price, 2, '.', ',');
        var currency2 = currency_symbol + jQuery.number(price2, 2, '.', ',');
        jQuery('.product-shop .price-box .special-price .price').text(currency);
        jQuery('.product-shop .price-box .old-price .price').text(currency2);
        if (error === 1) {
            jQuery('#bss_configurablegridview .check-configuable-product').val(1);
        } else {
            jQuery('#bss_configurablegridview .check-configuable-product').val('');
        }
    });

    jQuery('.reset-configurablegridview').click(function () {
        jQuery('#bss_configurablegridview .qty_att_product').each(function () {
            jQuery(this).val(0);
            jQuery(this).parent().prev().find('span').text('0.00');
            jQuery('.product-shop .price-box .old-price .price').text(price_old);
            jQuery('.product-shop .price-box .special-price .price').text(special_price_old);
        });
        jQuery('#bss_configurablegridview .check-configuable-product').val('');
    });

}
});