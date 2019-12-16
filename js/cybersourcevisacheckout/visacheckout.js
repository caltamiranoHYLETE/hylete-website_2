var payloadSuccessHandle = {};

payloadSuccessHandle.visasuccessFunction = function (payment) {

    var callID = payment.callid;

    if (this.csdataserviceUrl === undefined) {
        return;
    }
    if (!callID.length) {
        return;
    }

    document.getElementById("vorderid").value = '';
    jQuery("#loading_image_visacheckout").show();
    checkout.setLoadWaiting('payment');
    jQuery("#loading_image_visacheckout").show();

    new Ajax.Request(this.csdataserviceUrl, {
        method: "post",
        parameters: {isAjax: 1, vcorderid: callID},
        onSuccess: function (transport) {
            var json = transport.responseText.evalJSON();
            jQuery("#loading_image_visacheckout").hide();
            checkout.setLoadWaiting(false);
            checkout.loadWaiting = false;
            if (json.error) {
                document.getElementById("vorderid").value = '';
                alert(json.msg);
            } else {
                document.getElementById("vorderid").value = callID;
                jQuery("#payment-buttons-container").find(".button").trigger("click");
            }
        }
    });
};

function onVisaCheckoutReady() {
    V.on("payment.success", function (payment) {
        payloadSuccessHandle.visasuccessFunction(payment);
    });

    V.on("payment.cancel", function (payment) {
        jQuery("#loading_image_visacheckout").hide();
        document.getElementById("vorderid").value = "";
    });

    V.on("payment.error", function (payment, error) {
        console.log(error);
        alert(error.message);
        jQuery("#loading_image_visacheckout").hide();
        document.getElementById("vorderid").value = "";
    });
}
