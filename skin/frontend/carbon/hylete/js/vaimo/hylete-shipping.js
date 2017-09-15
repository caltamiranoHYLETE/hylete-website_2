;(function ($) {
    var shippingToggleCmsBlock = function(countryId) {

        var checkedValue = $('input[name="billing[use_for_shipping]"]:checked').val();
        if (!countryId) {
            if ($('#billing-address-select').length) {
                var addressFieldSelector = (checkedValue == 1) ? "#billing-address-select" : "#shipping-address-select";
                countryId = $(addressFieldSelector).find(':selected').data('country-id');
            }
            if (!countryId) {
                var countryFieldSelector = (checkedValue == 1) ? "billing[country_id]" : "shipping[country_id]";
                countryId = $('select[name="' + countryFieldSelector + '"]').val();
            }
        }

        $('#block-shipping-domestic').addClass('hide');
        $('#block-shipping-international').addClass('hide');

        if (countryId === 'US') {
            $('#block-shipping-domestic').removeClass('hide');
        } else if (countryId !== '') {
            $('#block-shipping-international').removeClass('hide');
        }
        return false;
    };

    $(function() {

        shippingToggleCmsBlock();

        $('.address-section').on('change', 'select[name="billing[country_id]"], select[name="shipping[country_id]"], input[name="billing[use_for_shipping]"]', function(event) {
            shippingToggleCmsBlock();
        });

        $('.address-section').on('change', '#billing-address-select,#shipping-address-select', function(event) {
            var checkedValue = $('input[name="billing[use_for_shipping]"]:checked').val();
            if (checkedValue == 1 && $(this).attr('id') !== 'billing-address-select') {
                return false;
            }
            if (checkedValue == 0 && $(this).attr('id') !== 'shipping-address-select') {
                return false;
            }
            shippingToggleCmsBlock($(this.options[this.selectedIndex]).data('country-id'));
        });
    });
})(jQuery);