// Icommerce QuickCheckout
var QuickCheckout = Class.create();
QuickCheckout.prototype = {

    initialize:function ()
    {

        this.reloadPaymentMethodsFlag = true;

        this.place_order_button_disabled = 0;
        this.lastSuccefulShipping = null;
        // Save checkout method as register if customer is not logged in
        if ($('register-customer-password')) {
            checkout.method = 'register';
            this.saveCheckoutMethod('register');
        }

        this.klarnaLookupElement = $$('.ssn');
        if (this.klarnaLookupElement.length != 0) {
            //Hide Klarna lookup from the beginning and show it later if it is selected as payment method.
            this.klarnaLookupElement.each(Element.hide);
        }

        this.addEventListenersToShowAndHideLoginLinks();

        // Add event listener to country select
        if ($('billing:country_id')) {
            $('billing:country_id').observe('change', this.onBillingCountryChange.bindAsEventListener(this));

            if (!$F('billing:country_id')) {
                this.selectFirstCountry();
            }
        }
        if ($('shipping:country_id')) {
            $('shipping:country_id').observe('change', this.onShippingCountryChange.bindAsEventListener(this));

            if (!$F('shipping:country_id')) {
                this.selectFirstCountry();
            }
        }

        // Add event listener to region select
        if ($('billing:region_id')) {
            $('billing:region_id').observe('change', this.onBillingRegionChange.bindAsEventListener(this));
        }
        if ($('shipping:region_id')) {
            $('shipping:region_id').observe('change', this.onShippingRegionChange.bindAsEventListener(this));
        }

        // Add event listener to postcode-field
        if ($('billing:postcode')) {
            $('billing:postcode').observe('blur', this.onPostcodeBlur.bindAsEventListener(this));
        }
        if ($('shipping:postcode')) {
            $('shipping:postcode').observe('blur', this.onPostcodeBlur.bindAsEventListener(this));
        }

        // Add event listener to eventual preloaded addresses
        if ($('billing-address-select')) {
            $('billing-address-select').observe('change', this.onPreloadedBillingAdressChange.bindAsEventListener(this));
        }
        if ($('shipping-address-select')) {
            $('shipping-address-select').observe('change', this.onPreloadedShippingAdressChange.bindAsEventListener(this));
        }

        this.addEventListenersToShippingChoiceRadios();
        this.onShippingSameAsBillingClick();

        // Start by reloading shipping methods
        this.reloadShippingMethods();

        // JS hook available for other js to reload QuickCheckout
        document.observe('quickcheckout:reload', this.reloadShippingMethods.bind(this));
        document.observe('quickcheckout:shippingload_after', this.afterShippingLoad.bind(this));
        document.observe('payment-method:switched', this.onPaymentSwitchFire.bind(this));

        // EE customer balance specific
        if ($('use_customer_balance') && Payment.prototype.switchCustomerBalanceCheckbox) {
            document.observe('quickcheckout:paymentload_after', function ()
            {
                payment.switchCustomerBalanceCheckbox();
            });
        }
        try {
            if (qcDefaultBillingAddress != false && qcDefaultShippingAddress != false) {
                this.autoFillAddressFields(qcDefaultBillingAddress, qcDefaultShippingAddress);
            }
        }
        catch (e) {
            response = {};
        }

        this.removeMagentoHtmlElements();
    },

    enableRegionShipPayReload:function ()
    {
        // Add event listener to country select
        if ($('billing:region_id')) {
            $('billing:region_id').observe('change', this.onBillingCountryChange.bindAsEventListener(this));

            /*if(!$F('billing:country_id')){
             this.selectFirstCountry();
             }*/
        }
        if ($('shipping:region_id')) {
            $('shipping:region_id').observe('change', this.onShippingCountryChange.bindAsEventListener(this));

            /*if(!$F('shipping:country_id')){
             this.selectFirstCountry();
             }*/
        }
    },

    onPaymentSwitchFire:function (e)
    {
        //var use_customer_balance = $('use_customer_balance');
        var selectedInputs = $$('#checkout-payment-method-load input[name="payment[method]"]:checked').size();
        if (selectedInputs == 0 && !this.selectFirstPaymentMethod()) {
            return;
        } else {
            this.toggleKlarnaLookupByPaymentMethod(e);
            this.showReviewLoader();
            this.savePaymentMethod();
        }
    },

    autoFillAddressFields:function (billAddress, shipAddress)
    {
        billAddress = eval('(' + billAddress + ')');
        shipAddress = eval('(' + shipAddress + ')');

        // Billing
        if ($('billing:firstname')) {
            $('billing:firstname').value = billAddress.firstname;
        }
        if ($('billing:lastname')) {
            $('billing:lastname').value = billAddress.lastname;
        }
        if ($('billing:company')) {
            $('billing:company').value = billAddress.company;
        }
        for (var key in billAddress) {
            if (key.substr(0, 6) == 'street') {
                if ($('billing:' + key)) {
                    $('billing:' + key).value = billAddress[key];
                }
            }
        }

        if ($('billing:postcode')) {
            $('billing:postcode').value = billAddress.postcode;
        }
        if ($('billing:city')) {
            $('billing:city').value = billAddress.city;
        }
        if ($('billing:telephone')) {
            $('billing:telephone').value = billAddress.telephone;
        }

        if ($('billing:country_id')) {
            var select = $('billing:country_id');
            var optionLength = select.options.length;
            for (var i = 0; i < optionLength; i++) {
                if (select.options[i].value == billAddress.country_id) {
                    select.options[i].selected = true;
                }
            }
        }

        billingRegionUpdater.update();

        if ($('billing:region')) {
            // Input
            $('billing:region').value = billAddress.region;
        }

        if ($('billing:region_id')) {
            var billRegionSelect = $('billing:region_id');
            var optionLength = billRegionSelect.options.length;
            for (var i = 0; i < optionLength; i++) {
                if (billRegionSelect.options[i].text == billAddress.region) {
                    billRegionSelect.options[i].selected = true;
                }
            }
        }

        // Shipping
        if ($('shipping:firstname')) {
            $('shipping:firstname').value = shipAddress.firstname;
        }
        if ($('shipping:lastname')) {
            $('shipping:lastname').value = shipAddress.lastname;
        }
        if ($('shipping:company')) {
            $('shipping:company').value = shipAddress.company;
        }
        for (var key in shipAddress) {
            if (key.substr(0, 6) == 'street') {
                if ($('shipping:' + key)) {
                    $('shipping:' + key).value = shipAddress[key];
                }
            }
        }
        if ($('shipping:postcode')) {
            $('shipping:postcode').value = shipAddress.postcode;
        }
        if ($('shipping:city')) {
            $('shipping:city').value = shipAddress.city;
        }
        if ($('shipping:telephone')) {
            $('shipping:telephone').value = shipAddress.telephone;
        }

        if ($('shipping:country_id')) {
            var select = $('shipping:country_id');
            var optionLength = select.options.length;
            for (var i = 0; i < optionLength; i++) {
                if (select.options[i].value == shipAddress.country_id) {
                    select.options[i].selected = true;
                }
            }
        }

        shippingRegionUpdater.update();

        if ($('shipping:region')) {
            // Input
            $('shipping:region').value = shipAddress.region;
        }

        if ($('shipping:region_id')) {
            var shipRegionSelect = $('shipping:region_id');
            var optionLength = shipRegionSelect.options.length;
            for (var i = 0; i < optionLength; i++) {
                if (shipRegionSelect.options[i].text == shipAddress.region) {
                    shipRegionSelect.options[i].selected = true;
                }
            }
        }
    },

    onShippingSameAsBillingClick:function ()
    {
        if ($('shipping:same_as_billing')) {
            $('shipping:same_as_billing').observe('click', function (e)
            {

                if ($('shipping:same_as_billing').checked) {
                    if ($('shipping-address-select') && $('shipping-address-select').visible() && $F('shipping-address-select')) {
                        this.onPreloadedShippingAdressChange();
                    }
                    else {
                        this.reloadShippingMethods();
                    }
                }

                shipping.setSameAsBilling($('shipping:same_as_billing').checked);
            }.bindAsEventListener(this));
        }
    },

    saveCheckoutMethod:function (methodStr)
    {
        var request = new Ajax.Request(
                checkout.saveMethodUrl,
                {
                    method:'post',
                    onFailure:checkout.ajaxFailure.bind(checkout),
                    parameters:{method:methodStr}
                }
        );
    },

    onBillingCountryChange:function ()
    {
        if ($F('billing:country_id')) {
            if ($('billing:use_for_shipping_yes') && $('billing:use_for_shipping_yes').checked) {
                this.reloadShippingMethods();
            }
            else {
                this.reloadPaymentMethods();
            }
        }
    },

    onBillingRegionChange:function ()
    {
        if ($F('billing:region_id')) {
            if ($('billing:use_for_shipping_yes') && $('billing:use_for_shipping_yes').checked) {
                this.reloadShippingMethods();
            }
            else {
                this.reloadPaymentMethods();
            }
        }
    },

    onPostcodeBlur:function ()
    {
        this.reloadShippingMethods();
    },

    onShippingCountryChange:function ()
    {
        if ($('billing:use_for_shipping_no') && $('billing:use_for_shipping_no').checked) {
            if ($F('shipping:country_id')) {
                this.reloadPaymentMethodsFlag = false;
                this.reloadShippingMethods();
            }
        }
    },

    onShippingRegionChange:function ()
    {
        if ($('billing:use_for_shipping_no') && $('billing:use_for_shipping_no').checked) {
            if ($F('shipping:region_id')) {
                this.reloadPaymentMethodsFlag = false;
                this.reloadShippingMethods();

            }
        }
    },

    onPreloadedBillingAdressChange:function ()
    {
        if ($F('billing-address-select')) {
            if ($('billing:use_for_shipping_yes') && $('billing:use_for_shipping_yes').checked) {
                this.reloadShippingMethods();
            }
            else {
                this.reloadPaymentMethods();
            }
        }
        else {
            if ($('billing:use_for_shipping_yes') && $('billing:use_for_shipping_yes').checked) {
                this.reloadShippingMethods();
            }
            else {
                this.reloadPaymentMethods();
            }
        }
    },

    onPreloadedShippingAdressChange:function ()
    {
        this.reloadPaymentMethodsFlag = false;
        this.reloadShippingMethods();
    },

    selectFirstCountry:function ()
    {
        var selects = $$('form#co-billing-form select option');
        var select = selects[1];
        if (select) {
            select.selected = true;
        }
    },

    selectFirstRegion:function(){
   		var selects = $$('form#co-billing-form select option');
   		var select = selects[2];
   		if (select) {
   			select.selected = true;
   		}
   	},

    reloadShippingMethods:function()
    {
        //console.log('reloadshippingMethods');

        // Some products don't have shipping methods, if so skip this step (else)
        if ($('checkout-shipping-method-load')) {
            this.showShippingLoader();

            // We don't always want to reload the payment methods...
            if (this.reloadPaymentMethodsFlag) {
                this.showPaymentLoader();
            }

            this.showReviewLoader();

            var request = new Ajax.Request(urlToGetShippingMethodsHtml, {
                method:'post',
                onComplete:this.onComplete,
                onSuccess:this.onShippingLoad.bindAsEventListener(this),
                onFailure:this.ajaxFailure,
                parameters:Form.serialize(this.getBillingOrShippingForm())
            });
        } else {
            this.reloadPaymentMethods();
        }
    },

    // This function decides if we want to serialize shipping or billing form
    getBillingOrShippingForm:function ()
    {
        if ($('billing:use_for_shipping_no') && $('billing:use_for_shipping_no').checked && $('shipping:same_as_billing') && !$('shipping:same_as_billing').checked && ($F('shipping:country_id') || $F('shipping-address-select'))) {
            return shipping.form;
        }
        else {
            return billing.form;
        }
    },

    onShippingLoad:function (transport)
    {

        //console.log('onShippingLoad');

        if (transport && transport.responseText) {
            try {
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }

        if (response.error) {
            checkout.setLoadWaiting(false);
            this.errorMessage(response.message);
            return false;
        }

        if (response.update_section) {
            $('checkout-' + response.update_section.name + '-load').update(response.update_section.html);
            $('checkout-' + response.update_section.name + '-load').fire("quickcheckout:shippingload");
        }

        this.hideShippingLoader();
        this.addEventListenersToAllShippingMethods();
    },
    lastShippingMethod:null,
    // fire on quickcheckout:shippingload_after
    afterShippingLoad:function ()
    {
        var inputs = $$('#checkout-shipping-method-load input[type=radio]');
        var numInputs = $$('#checkout-shipping-method-load input[type=radio]').size();

        for (var i = 0; i < numInputs; i++) {
            $(inputs[i]).observe('click', this.onShippingMethodClick.bindAsEventListener(this));
        }
        var selectedInputs = $$('#checkout-shipping-method-load input[type=radio]:checked');
        if (selectedInputs.size() == 0 || this.lastShippingMethod != selectedInputs[0].value) {
            this.reloadPaymentMethodsFlag = false;
            this.lastShippingMethod = selectedInputs[0].value;
            this.selectFirstShippingMethod();
        }
    },
    /**
     * Bind shipping method event listeners
     */
    addEventListenersToAllShippingMethods:function (containerId)
    {
        if (containerId == undefined) {
            containerId = 'checkout-shipping-method-load';
        }

        var selector = '#' + containerId + ' input[type=radio]';

        Prototype.Browser.IE6 = Prototype.Browser.IE &&
            parseInt(navigator.userAgent.substring(navigator.userAgent.indexOf("MSIE")+5)) == 6;
        Prototype.Browser.IE7 = Prototype.Browser.IE &&
            parseInt(navigator.userAgent.substring(navigator.userAgent.indexOf("MSIE")+5)) == 7;
        Prototype.Browser.IE8 = Prototype.Browser.IE &&
            parseInt(navigator.userAgent.substring(navigator.userAgent.indexOf("MSIE")+5)) == 8;

        $$(selector).each(function(input) {
            if(Prototype.Browser.IE6 || Prototype.Browser.IE7 || Prototype.Browser.IE8) {
                $(input).style.display = "block";
            }
            $(input).observe('click', this.onShippingMethodClick.bindAsEventListener(this));
        }.bind(this));

        this.selectFirstShippingMethod();
    },
    /**
     * Reload the order review
     * @param evt
     */
    onShippingMethodClick:function (evt)
    {
        if (window.jQuery && jQuery(evt.srcElement).attr('data-processing-onchange') == 1) {
            return;
        }

        this.showReviewLoader();

        this.reloadPaymentMethodsFlag = false;
        this.saveShippingMethod();
    },
    /**
     * Select first shipping method if no methods is selected
     */
    selectFirstShippingMethod:function()
    {
        var selectedInputs = $$('#checkout-shipping-method-load input[type=radio]:checked').size();

        if (selectedInputs < 1) {
            var inputs = $$('#checkout-shipping-method-load input[type=radio]');
            var numInputs = $$('#checkout-shipping-method-load input[type=radio]').size();
            if (numInputs > 0) {
                inputs[0].checked = true;
            }
        }
        this.saveShippingMethod();
    },
    /**
     * Update shipping method information on order
     */
    saveShippingMethod:function ()
    {
        if ($('checkout-shipping-method-load')) {
            $('checkout-shipping-method-load').fire('quickcheckout:saveShippingMethod');
        }

        var request = new Ajax.Request(
                shippingMethod.saveUrl,
                {
                    method:'post',
                    onComplete:this.onComplete,
                    onSuccess:this.whenShippingMethodHasBeenSaved.bindAsEventListener(this),
                    onFailure:this.ajaxFailure,
                    parameters:Form.serialize(shippingMethod.form)
                }
        );
    },
    errorMessage: function(message) {
        alert(message);
    },
    whenShippingMethodHasBeenSaved:function (transport)
    {

        //console.log('whenShippingMethodHasBeenSaved');
        //console.log(this.reloadPaymentMethodsFlag);

        var response;
        var lastSuccefulShipping;
        if (transport && transport.responseText) {
            try {
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }

        if (response.error) {
            checkout.setLoadWaiting(false);
            document.fire('quickcheckoutcart:hideLoader');
            this.hidePaymentLoader();
            this.hideReviewLoader();
            this.errorMessage(response.message);
            lastSuccefulShipping = $$('#checkout-shipping-method-load input[type=radio]:checked');
            if (lastSuccefulShipping.size() > 0) {
                jQuery('#' + lastSuccefulShipping[0].id).change();
            }
            return false;
        }

        $('checkoutSteps').fire('quickcheckout:shippingSave');

        this.shippingMethodHasBeenSaved = true;

        // When we use Enterprise_Reward then we need to update methods section what is got from this call
        if ($('use_reward_points') != null || $('use_customer_balance') != null) {
            this.reloadPaymentMethodsFlag = true;
        }

        this.lastSuccefulShipping = null;
        if ($$('#checkout-shipping-method-load input[type=radio]').size() > 0) {
            lastSuccefulShipping = $$('#checkout-shipping-method-load input[type=radio]:checked');
            this.lastSuccefulShipping = lastSuccefulShipping.size() > 0 ? lastSuccefulShipping[0].id : null;

            // We don't always want to reload the payment methods...
            if (this.reloadPaymentMethodsFlag) {
                this.reloadPaymentMethods();
            }
            else {
                this.reloadPaymentMethodsFlag = true;
                this.savePaymentMethod();
            }
        }
        else {
            this.hidePaymentLoader();
            this.hideReviewLoader();
        }
    },

    reloadPaymentMethods:function ()
    {
        this.showPaymentLoader();
        this.showReviewLoader();

        var request = new Ajax.Request(urlToGetPaymentMethodsHtml, {
            method:'post',
            onComplete:this.onComplete,
            onSuccess:this.onPaymentLoad.bindAsEventListener(this),
            onFailure:this.ajaxFailure,
            parameters:Form.serialize(billing.form)
        });
    },

    onPaymentLoad:function (transport)
    {
        //console.log('onPaymentLoad');

        if (transport && transport.responseText) {
            try {
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }

        if (response.error) {
            checkout.setLoadWaiting(false);
            this.errorMessage(response.message);
            return false;
        }

        if (response.update_section) {

            if ($('checkout-' + response.update_section.name + '-load')) {
                $('checkout-' + response.update_section.name + '-load').replace(response.update_section.html);
                $('checkout-' + response.update_section.name + '-load').fire('quickcheckout:paymentload');
                document.fire('quickcheckout:paymentload_after');

                if (typeof jQuery !== 'undefined') {
                    jQuery(document).trigger('quickcheckout:paymentload_after');
                }
            }
        }

        this.hidePaymentLoader();
        this.addEventListenersToAllPaymentMethods();
    },

    addEventListenersToAllPaymentMethods:function ()
    {
        Prototype.Browser.IE6 = Prototype.Browser.IE &&
            parseInt(navigator.userAgent.substring(navigator.userAgent.indexOf("MSIE")+5)) == 6;
        Prototype.Browser.IE7 = Prototype.Browser.IE &&
            parseInt(navigator.userAgent.substring(navigator.userAgent.indexOf("MSIE")+5)) == 7;
        Prototype.Browser.IE8 = Prototype.Browser.IE &&
            parseInt(navigator.userAgent.substring(navigator.userAgent.indexOf("MSIE")+5)) == 8;

        if(Prototype.Browser.IE6 || Prototype.Browser.IE7 || Prototype.Browser.IE8) {
            var inputs = $$('#checkout-payment-method-load input[type=radio]');
            var numInputs = $$('#checkout-payment-method-load input[type=radio]').size();

            for(var i = 0; i < numInputs; i++){
                $(inputs[i]).style.display = "block";
                $(inputs[i]).observe('click', this.onPaymentMethodClick.bindAsEventListener(this));
            }

            this.selectFirstPaymentMethod();
        } else {
            this.onPaymentSwitchFire();
        }
        //$(input).observe('click', this.onShippingMethodClick.bindAsEventListener(this));

        // no need to bind click-event for radios, as they call switchMethod() which executes event: payment-method:switched
        // Icommerce_PaymentShared >= 0.1.86

        // Edit (Jevgeni Bogatyrjov) 21.08.2013 : returned this method for <=IE8
        /*
         var inputs = $$('#checkout-payment-method-load input[type=radio]');
         var numInputs = $$('#checkout-payment-method-load input[type=radio]').size();

         for(var i = 0; i < numInputs; i++){
         $(inputs[i]).observe('click', this.onPaymentMethodClick.bindAsEventListener(this));
         }

         this.selectFirstPaymentMethod();
         */
    },

    onPaymentMethodClick:function ()
    {
        this.showReviewLoader();
        this.savePaymentMethod();
    },

    selectFirstPaymentMethod:function ()
    {
        var inputs = jQuery('#checkout-payment-method-load input[name="payment[method]"]').filter('[type=radio]');

        if (!inputs.is(':checked')) {
            var numInputs = inputs.size();
            var disabledInputs = inputs.filter(':disabled').size();
            if (disabledInputs == numInputs) {
                return false;
            }
            if (numInputs > 0) {
                var methods = [payment.currentMethod, payment.lastUsedMethod, inputs.filter(':first').val()];

                jQuery(methods).each(function(id, method) {
                    var entity = jQuery('#p_method_' + method);
                    if (entity.length) {
                        jQuery(entity).attr('checked', true).click();
                        return false;
                    }
                });
            }
        } else {
            inputs.filter(':checked').click();
        }

        return true;
    },

    savePaymentMethod:function ()
    {
        if (typeof(this._executingSavePayment) != 'undefined' && this._executingSavePayment) {
            return;
        }
        this._executingSavePayment = true;

        $('checkout-payment-method-load').fire('quickcheckout:savePaymentMethod');

        // avoiding situations when payment methods are being saved before the form has been populated
        var serialized = Form.serialize(payment.form);
        if (serialized.length == 0) {
            this._executingSavePayment = false;
            return;
        }

        var request = new Ajax.Request(
                payment.saveUrl,
                {
                    method:'post',
                    onComplete:this.onComplete,
                    onSuccess:this.reloadReviewBlock.bindAsEventListener(this),
                    onFailure:this.ajaxFailure,
                    parameters:serialized
                }
        );
    },

    reloadReviewBlock:function (transport)
    {

        //console.log('reloadReviewBlock');
        this._executingSavePayment = false;

        if (transport && transport.responseText) {
            try {
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }

        if (response.error) {
            checkout.setLoadWaiting(false);
            this.errorMessage(response.error);
            return false;
        }

        if (response.update_section) {
            $('checkout-' + response.update_section.name + '-load').update(response.update_section.html);
            $('checkout-' + response.update_section.name + '-load').fire('quickcheckout:reviewUpdated');
            this.hideReviewLoader();
        }
    },

    applyCoupon:function ()
    {
        this.showReviewLoader();
        var request = new Ajax.Request(
                urlToAddCoupon,
                {
                    method:'post',
                    onComplete:this.onComplete,
                    onSuccess:this.onCouponOrGiftCardApply.bindAsEventListener(this),
                    onFailure:this.ajaxFailure,
                    parameters:{
                        coupon_code:$('coupon_code').value,
                        remove:$('remove-coupone').value
                    }
                }
        );
        return false;
    },

    applyGiftCard:function ()
    {
        this.showReviewLoader();
        var request = new Ajax.Request(
                urlToAddGiftCard,
                {
                    method:'post',
                    onComplete:this.onComplete,
                    onSuccess:this.onCouponOrGiftCardApply.bindAsEventListener(this),
                    onFailure:this.ajaxFailure,
                    parameters:{
                        giftcard_code:$('giftcard_code').value
                    }
                }
        );
        return false;
    },

    removeGiftCard:function (code)
    {
        this.showReviewLoader();
        var request = new Ajax.Request(
                urlToRemoveGiftCard,
                {
                    method:'post',
                    onComplete:this.onComplete,
                    onSuccess:this.onCouponOrGiftCardApply.bindAsEventListener(this),
                    onFailure:this.ajaxFailure,
                    parameters:{
                        code:code
                    }
                }
        );
        return false;
    },

    onCouponOrGiftCardApply:function (transport)
    {
        if (transport && transport.responseText) {
            try {
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }

        if (response.error) {
            this.hideReviewLoader();
            this.errorMessage(response.error);
            return false;
        }

        if (response.update_section) {
            $('checkout-' + response.update_section.name + '-load').update(response.update_section.html);
        }
        this.savePaymentMethod();
    },

    checkGiftCardStatus:function ()
    {

        var request = new Ajax.Request(
                urlToCheckGiftCardStatus,
                {
                    method:'post',
                    onComplete:this.onComplete,
                    onSuccess:this.onGiftCardStatusCheck.bindAsEventListener(this),
                    onFailure:this.ajaxFailure,
                    parameters:{
                        giftcard_code:$('giftcard_code').value
                    }
                }
        );
        return false;

    },

    onGiftCardStatusCheck:function (transport)
    {

        if (transport && transport.responseText) {
            try {
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }

        if (response.error) {
            this.errorMessage(response.error);
            return false;
        }

        if (response.update_section) {
            $(response.update_section.name).update(response.update_section.html);
        }
    },

    disablePlaceOrder:function ()
    {
        if (this.place_order_button_disabled == 0) {
            $('place-order-button').disabled = true;
            $('place-order-button').setOpacity(0.4);
        }
        this.place_order_button_disabled++;
    },

    enablePlaceOrder:function ()
    {
        this.place_order_button_disabled--;
        if (this.place_order_button_disabled == 0) {
            $('place-order-button').disabled = false;
            $('place-order-button').setOpacity(1.0);
        }
    },

    isOrderSaveInProgress: function() {
        return $('place-order-button').disabled || this.place_order_button_disabled > 0;
    },
    saveOrder:function (event)
    {
        //console.log('saveOrder');
        if (this.isOrderSaveInProgress()) {
            console.log('Error: Trying to save order while order is already saving.');
            return;
        }

        checkout.setLoadWaiting('review');
        var shippingIsValid = true;

        if ($('billing:use_for_shipping_no') && $('billing:use_for_shipping_no').checked) {
            var shippingValidator = new Validation(shipping.form);
            if (!shippingValidator.validate()) {
                shippingIsValid = false;
            }
        }

        var billingValidator = new Validation(billing.form);

        if (billingValidator.validate() && shippingIsValid) {
            if (checkout.method == 'register' && $('billing:customer_password').value != $('billing:confirm_password').value) {
                alert(Translator.translate('Error: Passwords do not match'));
                return;
            }

            var request = new Ajax.Request(
                    billing.saveUrl,
                    {
                        method:'post',
                        onComplete:this.onComplete,
                        onSuccess:this.onBillingSave.bindAsEventListener(this),
                        onFailure:this.ajaxFailure,
                        parameters:Form.serialize(billing.form)
                    }
            );
        } else {
            checkout.setLoadWaiting(false);
        }
    },

    onComplete:function (response)
    {
        // We do nothing here, debugging purpose only
        // console.log(response.status);
    },

    shouldSaveShipping: function()
    {
        return $('billing:use_for_shipping_no') && $('billing:use_for_shipping_no').checked;
    },
    onBillingSave:function (transport)
    {

        //console.log('onBillingSave');

        if (transport && transport.responseText) {
            try {
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }

        if (response.error) {
            checkout.setLoadWaiting(false);
            this.errorMessage(response.message);
            return false;
        }

        if (this.shouldSaveShipping()) {
            var request = new Ajax.Request(
                    shipping.saveUrl,
                    {
                        method:'post',
                        onComplete:this.onComplete,
                        onSuccess:this.onShippingSave.bindAsEventListener(this),
                        onFailure:this.ajaxFailure,
                        parameters:Form.serialize(shipping.form)
                    }
            );
        }
        else {
            this.onShippingSave();
        }
    },

    onShippingSave:function (transport)
    {

        //console.log('onShippingSave');

        if (transport && transport.responseText) {
            try {
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }

        if (response.error) {
            checkout.setLoadWaiting(false);
            this.errorMessage(response.message);
            return false;
        }

        // If less than one, probably virtual product
        var numShippingInputs = $$('#checkout-shipping-method-load input[type=radio]').size();
        if ((numShippingInputs > 0) && shippingMethod.validate()) {
            var request = new Ajax.Request(
                    shippingMethod.saveUrl,
                    {
                        method:'post',
                        onComplete:this.onComplete,
                        onSuccess:this.onShippingMethodSave.bindAsEventListener(this),
                        onFailure:this.ajaxFailure,
                        parameters:Form.serialize(shippingMethod.form)
                    }
            );
        }
        else if (numShippingInputs < 1) {
            var validator = new Validation(payment.form);
            if (payment.validate() && validator.validate()) {
                var request = new Ajax.Request(
                        payment.saveUrl,
                        {
                            method:'post',
                            onComplete:this.onComplete,
                            onSuccess:this.onPaymentSave.bindAsEventListener(this),
                            onFailure:this.ajaxFailure,
                            parameters:Form.serialize(payment.form)
                        }
                );
            }
            else {
                checkout.setLoadWaiting(false);
            }
        }
        else {
            checkout.setLoadWaiting(false);
        }
    },

    onShippingMethodSave:function (transport)
    {
        if (transport && transport.responseText) {
            try {
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }

        if (response.error) {
            checkout.setLoadWaiting(false);
            this.errorMessage(response.message);
            return false;
        }

        var validator = new Validation(payment.form);
        if (payment.validate() && validator.validate()) {
            var request = new Ajax.Request(
                    payment.saveUrl,
                    {
                        method:'post',
                        onComplete:this.onComplete,
                        onSuccess:this.onPaymentSave.bindAsEventListener(this),
                        onFailure:this.ajaxFailure,
                        parameters:Form.serialize(payment.form)
                    }
            );
        } else {
            checkout.setLoadWaiting(false);
        }
    },

    onPaymentSaveDone: function(transport) {
        this.onComplete(transport);
    },
    onPaymentSave:function (transport)
    {
        if (transport && transport.responseText) {
            try {
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }

        if (response.error) {
            checkout.setLoadWaiting(false);
            this.errorMessage(response.error);
            return false;
        }

        var params = Form.serialize(payment.form);
        if (review.agreementsForm) {
            params += '&' + Form.serialize(review.agreementsForm);
        }

        // Addon for Icommerce_CustomerCheckoutComment-module
        var ccNode = $('customer_comment');
        if (ccNode) {
            params += "&customer_comment=" + encodeURIComponent(ccNode.value);
        }
        // End addon

        // Check if Authorize.net DPM is used
        if ($('co-directpost-form')) {
            if (directPostModel.validate()) {
                directPostModel.saveOnepageOrder();
            } else {
                this.ajaxFailure();
            }
        } else {
            params.save = true;
            var request = new Ajax.Request(
                    review.saveUrl,
                    {
                        method:'post',
                        parameters:params,
                        onComplete:this.onPaymentSaveDone.bindAsEventListener(this),
                        onSuccess:review.onSave,
                        onFailure:this.ajaxFailure.bind(checkout)
                    }
            );
        }

    },

    addEventListenersToShowAndHideLoginLinks:function ()
    {
        // Add event listeners to show login-link
        if ($('click-here-to-show-login')) {
            $('click-here-to-show-login').observe('click', function (e)
            {
                if ($('qc-loginform')) {
                    $('qc-loginform').show();

                    if ($('click-here-to-hide-login')) {
                        $('click-here-to-hide-login').show();
                        $('click-here-to-show-login').hide();
                    }

                    Event.stop(e);
                }
            });
        }
        // Add event listeners to hide login-link
        if ($('click-here-to-hide-login')) {
            $('click-here-to-hide-login').observe('click', function (e)
            {
                if ($('qc-loginform')) {
                    $('qc-loginform').hide();

                    if ($('click-here-to-show-login')) {
                        $('click-here-to-show-login').show();
                        $('click-here-to-hide-login').hide();
                    }

                    Event.stop(e);
                }
            });
        }
    },

    addEventListenersToShippingChoiceRadios:function ()
    {
        // Add listener to shipping-choice
        if ($$('.qc-shipping-choice')) {
            $$('.qc-shipping-choice input[type=radio]').each(function (node) {
                 $(node).observe('click', function (event)
                 {
                     var node = event.findElement();
                     if (node.value == "1") { // Same as billing
                         if ($('opc-shipping')) {
                             $('opc-shipping').hide();
                         }

                         this.reloadShippingMethods();
                     }
                     else {
                         if ($('opc-shipping')) {
                             $('opc-shipping').show();
                         }

                         this.reloadPaymentMethodsFlag = false;
                         this.reloadShippingMethods();
                     }
                 }.bindAsEventListener(this));
             }.bindAsEventListener(this));
        }
    },

    showShippingLoader:function ()
    {
        $('checkout-shipping-method-load').hide();
        $('checkout-shipping-method-loader').show();
    },

    hideShippingLoader:function ()
    {
        $('checkout-shipping-method-loader').hide();
        $('checkout-shipping-method-load').show();
    },

    showPaymentLoader:function ()
    {
        $('checkout-payment-method-load').hide();
        $('checkout-payment-method-loader').show();
    },

    hidePaymentLoader:function ()
    {
        $('checkout-payment-method-load').show();
        $('checkout-payment-method-loader').hide();
    },

    showReviewLoader:function ()
    {
        $('checkout-review-load').hide();
        $('checkout-review-loader').show();
    },

    hideReviewLoader:function ()
    {
        $('checkout-review-loader').hide();
        $('checkout-review-load').show();
    },

    ajaxFailure:function (response)
    {
        //console.log(response);
        checkout.setLoadWaiting(false);
        this.errorMessage('An error occurred. Please try again or contact us.');
    },

    addEventListenerToSaveOrderButton:function ()
    {
        $('place-order-button').observe('click', this.saveOrder.bindAsEventListener(this));
    },
    removeMagentoHtmlElements:function ()
    {
        $$('.name-firstname br', '.name-lastname br', '.name-prefix br', '.name-suffix br', '.name-middlename br').each(function (node){
            node.remove();
        });
    },

    setLogControllerUrl:function (url)
    {
        this.logControllerUrl = url;

        // Replace normal Alert
        var oldAlert = window.alert;
        this.showAlert = function (msg)
        {
            oldAlert(msg);
        };

        window.alert = function (msg)
        {
            this.alertWithRemoteLogging(msg);
        }.bind(this);
    },

    alertWithRemoteLogging:function (msg)
    {
        this.showAlert(msg);

        new Ajax.Request(
                this.logControllerUrl,
                {
                    method:'post',
                    parameters:{msg:msg}
                }
        );

        return;
    },

    /**
     * Only show Klarna lookup if payment method is Klarna.
     */
    toggleKlarnaLookupByPaymentMethod: function (e)
    {
        var currentMethod;

        if (this.klarnaLookupElement.length == 0) {
            //Break if SSN lookup container is not there.
            return;
        }

        if (e !== 'undefined') {
            currentMethod = e.memo.method_code;
        } else {
            currentMethod = payment.currentMethod;
        }

        if (this.isPaymentMethodKlarna(currentMethod)) {
            this.klarnaLookupElement.each(Element.show);
        } else {
            this.klarnaLookupElement.each(Element.hide);

            //Reset fields if last method was Klarna, but new one isn't.
            if (this.isPaymentMethodKlarna(payment.lastUsedMethod)) {
                var textFields = $('co-billing-form').getInputs('text');
                var telFields = $('co-billing-form').getInputs('tel');

                var fields = textFields.concat(telFields);

                fields.each(function(item) {
                    item.value = '';
                });

                //Remove readonly when it's not Klarna.
                if($('billing:company')) {
                    $('billing:company').readOnly = '';
                }
            }
        }
    },

    isPaymentMethodKlarna: function(method) {
        if ( method == 'kreditor_partpayment' || method == 'kreditor_invoice') {
            return true;
        } else {
            return false;
        }
    },

    /**
     * Helper method to make multi-level sub-classing possible
     * @return Correct parent of a subclass
     * @private
     */
    _parent: function() {
        if (!this.__hierarchy) {
            this.__hierarchy = [this.constructor.superclass.prototype];
        }

        if (isNaN(this.__level)) {
            this.__level = 0;
        } else {
            this.__level++;
        }

        if (!this.__hierarchy[this.__level]) {
            if (this.__hierarchy[this.__level - 1].constructor.superclass) {
                this.__hierarchy[this.__level] = this.__hierarchy[this.__level - 1].constructor.superclass.prototype;
            } else {
                this.__level--;
            }
        }

        var parent = this.__hierarchy[this.__level];
        if (!parent.constructor.superclass) {
            this.__level = 0;
        }

        return parent;
    }
}
