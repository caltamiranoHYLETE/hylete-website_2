jQuery(function() {
    if (!Mage.Cookies.get('bw_klaviyoextend_off')) {
        fireKlaviyoCall();
    }
});

function fireKlaviyoCall(){
    if (jQuery('#klaviyoExtendAction').length) {
        var klaviyoData = _learnq.identify();
        if (klaviyoData.$email) {
            jQuery.ajax({
                url: jQuery('#klaviyoExtendAction').val(),
                type: "POST",
                data: {'isAjax':1,'email':klaviyoData.$email},
                success: function(data) {
                    console.log('Email from klaviyo was saved!');
                    Mage.Cookies.set('bw_klaviyoextend_off', true);
                },
                error:function(data) {
                    console.log('Error saving email from Klaviyo');
                }
            });
        } else {
            Mage.Cookies.set('bw_klaviyoextend_off', true);
        }
    }
}

