document.observe('dom:loaded', function() {
    FireCheckout.prototype.setResponse = FireCheckout.prototype.setResponse.wrap(function(o, transport) {
        var reponseUrl = transport.transport.responseURL;
        try {
            response = transport.responseText.evalJSON();
        } catch (err) {
            return o(transport);
        }

        if (reponseUrl !== checkout.urls.save ||
            !response.success ||
            payment.currentMethod != 'foomandpsprofusion') {

            return o(transport);
        }

        checkout.setLoadWaiting(false);
        checkout.setLoadingButton($$('.btn-checkout')[0], false);

        review.successUrl = checkout.urls.success;
        review.nextStep(transport); // @see app/design/frontend/base/default/template/fooman/dpspro/pxfusion/iframe.phtml
    });
});
