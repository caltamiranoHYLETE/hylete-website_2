jQuery( document ).ready(function() {

    var custEmail = getQueryString("email");
    var codeId = getQueryString("codeid");
    var formId = getQueryString("formId");

    if(codeId == null || custEmail == null) {
        jQuery("#wb-errorMessage").text("Some data we need is missing. Please make sure you filled out the form correctly and try again.").show();
    } else{
        var requestData = {email: custEmail, codeId: codeId, expiration: '4', formId: formId};

        jQuery.ajax({
            url: "/forms/lib/proxy.php",
            data: {requrl: urlBase + "CreateWinbackCouponCode?" + jQuery.param(requestData)},
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            cache: false,
            success: function (data) {
                console.log(data);
                if (data.errorMessage != "" && data.ErrorMessage != null) {
                    jQuery("#wb-errorMessage").text(data.ErrorMessage).show();
                } else {
                    jQuery.each(data, function() {
                        jQuery('#wb-code').text(data.CouponCode);
                    });
                }
            }
        });
    }

    function getQueryString ( field, url ) {
        var href = url ? url : window.location.href;
        var reg = new RegExp( '[?&]' + field + '=([^&#]*)', 'i' );
        var string = reg.exec(href);
        return string ? string[1] : null;
    }

});
