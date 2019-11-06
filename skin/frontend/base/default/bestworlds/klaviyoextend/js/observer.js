jQuery(function() {
    if (getUrlParameter('kl_id')) {
        var interval = setInterval(fireKlaviyoCall,500);
    }

    function getUrlParameter(sParam) {
        var sPageURL = window.location.search.substring(1),
            sURLVariables = sPageURL.split('&'),
            sParameterName,
            i;
        for (i = 0; i < sURLVariables.length; i++) {
            sParameterName = sURLVariables[i].split('=');
            if (sParameterName[0] === sParam) {
                return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
            }
        }
        return false;
    }

    function fireKlaviyoCall(){
        if (jQuery('#klaviyoExtendAction').length && jQuery.isFunction(_learnq.identify)) {
            clearInterval(interval);
            var klaviyoData = _learnq.identify();
            if (klaviyoData.$email) {
                jQuery.ajax({
                    url: jQuery('#klaviyoExtendAction').val(),
                    type: "POST",
                    data: {'isAjax':1,'email':klaviyoData.$email},
                    success: function(data) {
                        console.log('Email from klaviyo was saved!');
                    },
                    error:function(data) {
                        console.log('Error saving email from Klaviyo');
                    }
                });
            }
        }
    }
});



