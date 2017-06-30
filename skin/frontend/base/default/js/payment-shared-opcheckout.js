/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

var Checkout = Class.create();
Checkout.prototype = {
    initialize: function(accordion, urls){
        this.accordion = accordion;
        this.progressUrl = urls.progress;
        this.reviewUrl = urls.review;
        this.saveMethodUrl = urls.saveMethod;
        this.failureUrl = urls.failure;
        this.billingForm = false;
        this.shippingForm= false;
        this.syncBillingShipping = false;
        this.method = '';
        this.payment = '';
        this.loadWaiting = false;
        this.steps = ['login', 'billing', 'shipping', 'shipping_method', 'payment', 'review'];

        //this.onSetMethod = this.nextStep.bindAsEventListener(this);

        this.accordion.disallowAccessToNextSections = true;
    },

    ajaxFailure: function(){
        location.href = this.failureUrl;
    },

    reloadProgressBlock: function(toStep){
		if (typeof toStep == 'undefined') {
			toStep = null;
		}
		var updater;
		if($$('.col-right') && $$('.col-right').size() > 0){
			updater = new Ajax.Updater($$('.col-right')[0], this.progressUrl, {method: 'get', onFailure: this.ajaxFailure.bind(this), parameters: toStep ? {toStep: toStep} : null});
		}
		else if ($('checkout-progress-wrapper')) {
			// EE standard checkout
			updater = new Ajax.Updater('checkout-progress-wrapper', this.progressUrl, {method: 'get', onFailure: this.ajaxFailure.bind(this), parameters: toStep ? {toStep: toStep} : null});
		}
    },

    reloadReviewBlock: function(){
        var updater = new Ajax.Updater('checkout-review-load', this.reviewUrl, {method: 'get', onFailure: this.ajaxFailure.bind(this)});
    },

    _disableEnableAll: function(element, isDisabled) {
        var descendants = element.descendants();
        for (var k in descendants) {
            descendants[k].disabled = isDisabled;
        }
        element.disabled = isDisabled;
    },

    setLoadWaiting: function(step, keepDisabled) {
        if (step) {
            if (this.loadWaiting) {
                this.setLoadWaiting(false);
            }
            var container = $(step+'-buttons-container');
            container.addClassName('disabled');
            container.setStyle({opacity:.5});
            this._disableEnableAll(container, true);
            Element.show(step+'-please-wait');
        } else {
            if (this.loadWaiting) {
                var container = $(this.loadWaiting+'-buttons-container');
                var isDisabled = (keepDisabled ? true : false);
                if (!isDisabled) {
                    container.removeClassName('disabled');
                    container.setStyle({opacity:1});
                }
                this._disableEnableAll(container, isDisabled);
                Element.hide(this.loadWaiting+'-please-wait');
            }
        }
        this.loadWaiting = step;
    },

    gotoSection: function(section)
    {
		section = $('opc-'+section);
		if (section) {
			section.addClassName('allow');
			this.accordion.openSection(section);
		}
		this.reloadProgressBlock(section);
    },

    setMethod: function(){
        if ($('login:guest') && $('login:guest').checked) {
            this.method = 'guest';
            var request = new Ajax.Request(
                this.saveMethodUrl,
                {method: 'post', onFailure: this.ajaxFailure.bind(this), parameters: {method:'guest'}}
            );
            Element.hide('register-customer-password');
            this.gotoSection('billing');
        }
        else if($('login:register') && ($('login:register').checked || $('login:register').type == 'hidden')) {
            this.method = 'register';
            var request = new Ajax.Request(
                this.saveMethodUrl,
                {method: 'post', onFailure: this.ajaxFailure.bind(this), parameters: {method:'register'}}
            );
            Element.show('register-customer-password');
            this.gotoSection('billing');
        }
        else{
            alert(Translator.translate('Please choose to register or to checkout as a guest'));
            return false;
        }
    },

    setBilling: function() {
        if (($('billing:use_for_shipping_yes')) && ($('billing:use_for_shipping_yes').checked)) {
            shipping.syncWithBilling();
            $('opc-shipping').addClassName('allow');
            this.gotoSection('shipping_method');
        } else if (($('billing:use_for_shipping_no')) && ($('billing:use_for_shipping_no').checked)) {
            $('shipping:same_as_billing').checked = false;
            this.gotoSection('shipping');
        } else {
            $('shipping:same_as_billing').checked = true;
            this.gotoSection('shipping');
        }

        // this refreshes the checkout progress column
        this.reloadProgressBlock();

//        if ($('billing:use_for_shipping') && $('billing:use_for_shipping').checked){
//            shipping.syncWithBilling();
//            //this.setShipping();
//            //shipping.save();
//	        $('opc-shipping').addClassName('allow');
//	        this.gotoSection('shipping_method');
//        } else {
//            $('shipping:same_as_billing').checked = false;
//	        this.gotoSection('shipping');
//        }
//        this.reloadProgressBlock();
//        //this.accordion.openNextSection(true);
    },

    setShipping: function() {
        this.reloadProgressBlock();
        //this.nextStep();
        this.gotoSection('shipping_method');
        //this.accordion.openNextSection(true);
    },

    setShippingMethod: function() {
        this.reloadProgressBlock();
        //this.nextStep();
        this.gotoSection('payment');
        //this.accordion.openNextSection(true);
    },

    setPayment: function() {
        this.reloadProgressBlock();
        //this.nextStep();
        this.gotoSection('review');
        //this.accordion.openNextSection(true);
    },

    setReview: function() {
        this.reloadProgressBlock();
        //this.nextStep();
        //this.accordion.openNextSection(true);
    },

    back: function(){
        if (this.loadWaiting) return;
        this.accordion.openPrevSection(true);
    },

    setStepResponse: function(response){
        if (response.update_section) {
            $('checkout-'+response.update_section.name+'-load').update(response.update_section.html);
        }
        if (response.allow_sections) {
            response.allow_sections.each(function(e){
                $('opc-'+e).addClassName('allow');
            });
        }

        if(response.duplicateBillingInfo)
        {
            shipping.setSameAsBilling(true);
        }
        if (response.goto_section) {
            this.reloadProgressBlock();
            this.gotoSection(response.goto_section);
            return true;
        }
        if (response.redirect) {
            location.href = response.redirect;
            return true;
        }
	 	return false;
    }
}

// billing
var Billing = Class.create();
Billing.prototype = {
    initialize: function(form, addressUrl, saveUrl){
        this.form = form;
        if ($(this.form)) {
            $(this.form).observe('submit', function(event){this.save();Event.stop(event);}.bind(this));
        }
        this.addressUrl = addressUrl;
        this.saveUrl = saveUrl;
        this.onAddressLoad = this.fillForm.bindAsEventListener(this);
        this.onSave = this.nextStep.bindAsEventListener(this);
        this.onComplete = this.resetLoadWaiting.bindAsEventListener(this);

// Kjell
        this.onCreditcheckDataLoad = this.onCreditcheckDataLoad.bindAsEventListener(this);
        this.onCreditcheckFailure = this.onCreditcheckFailure.bindAsEventListener(this);
		this.creditcheckURL = "";
		this.creditcheckSuccess = false;
		this.identifyWaiting = false;

		//-----------------
		// Icommerce addon
		//-----------------

		this.getAddressUrl = "";

		//---------------------
		// Icommerce addon end
		//---------------------
    },

	//-----------------
	// Icommerce addon
	//-----------------
	getAddressData: function(pno)
	{
		if (pno)
		{
            Element.show('identifyme-please-wait');
			var request = new Ajax.Request(this.getAddressUrl + '?pno=' + pno, {method:'get', onSuccess: this.onAddressDataLoad, onFailure: this.onAddressDataFailure});

			if($('kreditor_pno')){
				$('kreditor_pno').value = pno;
			}
			if($('kreditor_pno_pp')){
				$('kreditor_pno_pp').value = pno;
			}
		}
	},

	//-----------------
	// Icommerce addon
	//-----------------
	getAddressData14: function(pno)
	{
        //This prevents non numeric characters to be transferred to klarna_invoice_personalnumber when label is written inside text field.
        var test = pno.replace('-', '');
        if (isNaN(test)){
            pno = 0;
        }

		if (pno)
		{
            Element.show('identifyme-please-wait');
			var request = new Ajax.Request(this.getAddress14Url + '?pno=' + pno, {method:'get', onSuccess: this.onAddressDataLoad, onFailure: this.onAddressDataFailure});

			if($('klarna_invoice_personalnumber')){
				$('klarna_invoice_personalnumber').value = pno;
			}
			if($('klarna_partpayment_personalnumber')){
				$('klarna_partpayment_personalnumber').value = pno;
			}
		}
	},

	onAddressDataLoad: function(transport)
	{
        Element.hide('identifyme-please-wait');
		var data = eval(transport.responseText);
		if (data[0]!="-9999") {
		if (data[0]=="" && data[1]!="") {
			if($('billing:company')){
				$('billing:company').value = data[1];
				$('billing:company').readOnly = '';
			}
		} else {
			if($('billing:firstname')){
				$('billing:firstname').value = data[0];
			}
			if($('billing:lastname')){
				$('billing:lastname').value = data[1];
			}
			if($('billing:company')){
				$('billing:company').value = '';
				$('billing:company').readOnly = 'true';
			}
		}
		if($('billing:street1')){
			$('billing:street1').value = data[2];
		}
		if($('billing:postcode')){
			$('billing:postcode').value = data[3];
		}
		if($('billing:city')){
			$('billing:city').value = data[4];
		}

		if (data[5]==209) {
			if($('billing:country_id')){
				$('billing:country_id').value = 'SE';
			}
		}
		}
	},

	onAddressDataFailure: function()
	{
        Element.hide('identifyme-please-wait');
	},

	//---------------------
	// Icommerce addon end
	//---------------------

// Kjell
    setIdentifyWaiting: function(setto) {
        if (setto) {
            if (this.identifyWaiting) {
                this.setIdentifyWaiting(false);
            }
            var container = $('billing-buttons-container');
            container.setStyle({opacity:.5});
            checkout._disableEnableAll(container, true);
            Element.show('identifyme-please-wait');
        } else {
            if (this.identifyWaiting) {
                var container = $('billing-buttons-container');
                container.setStyle({opacity:1});
                checkout._disableEnableAll(container, false);
                Element.hide('identifyme-please-wait');
            }
        }
        this.identifyWaiting = setto;
    },

// Kjell
	getCreditcheckData: function(isperson,idnr)
	{
		if (idnr) {
	        if (this.identifyWaiting!=false) return;
            this.setIdentifyWaiting('billing');
			if (isperson) {
				var request = new Ajax.Request(this.creditcheckURL + '?idnr=' + idnr, {method:'get', onSuccess: this.onCreditcheckDataLoad, onFailure: this.onCreditcheckFailure});
			} else {
				var request = new Ajax.Request(this.creditcheckURL + '?orgnr=' + idnr, {method:'get', onSuccess: this.onCreditcheckDataLoad, onFailure: this.onCreditcheckFailure});
			}
		}
	},

// Kjell
	onCreditcheckDataLoad: function(transport)
	{
		var data = eval(transport.responseText);
		if (data[0]==1 || (data[0]==2 && data[1]=="")) {
			this.creditcheckSuccess = true;
			if (data[3]=="person") {
				// Person
				if($('billing:firstname')){
					$('billing:firstname').value = data[4];
				}
				if($('billing:lastname')){
					$('billing:lastname').value = data[5];
				}
				if($('billing:street1')){
					$('billing:street1').value = data[6];
				}
				if($('billing:postcode')){
					$('billing:postcode').value = data[7];
				}
				if($('billing:city')){
					$('billing:city').value = data[8];
				}
				if($('billing:country_id')){
					$('billing:country_id').value = data[9];
				}
				if($('billing:ccidnr')){
					$('billing:ccidnr').value = data[10];
				}
				if($('billing:company')){
					$('billing:company').value = data[12]; // ?
				}

			} else {
				// Company
				if($('billing:company')){
					$('billing:company').value = data[4];
				}
				if($('billing:street1')){
					$('billing:street1').value = data[5];
				}
				if($('billing:postcode')){
					$('billing:postcode').value = data[6];
				}
				if($('billing:city')){
					$('billing:city').value = data[7];
				}
				if($('billing:country_id')){
					$('billing:country_id').value = data[8];
				}
				if($('billing:ccidnr')){
					$('billing:ccidnr').value = data[9];
				}
				if($('billing:firstname')){
					$('billing:firstname').value = '';
				}
				if($('billing:lastname')){
					$('billing:lastname').value = '';
				}
			}
            billingRegionUpdater.update();
		} else if (data[0]==2) {
			alert(data[2]);
			if($('billing:idnr')){
				$('billing:idnr').value = '';
			}
			if($('billing:ccidnr')){
				$('billing:ccidnr').value = '';
			}
		} else {
			if($('billing:idnr')){
				$('billing:idnr').value = '';
			}
			if($('billing:ccidnr')){
				$('billing:ccidnr').value = '';
			}
		}
	    this.setIdentifyWaiting(false);
	    var quickCheckout = new QuickCheckout();
	},

// Kjell
// This is never called, either it is difficult to get to this point or I have done something wrong...
	onCreditcheckFailure: function()
	{
		alert('Identification failed, please try later');
	    this.setIdentifyWaiting(false);
	    var quickCheckout = new QuickCheckout();
	},

    setAddress: function(addressId){
        if (addressId) {
            request = new Ajax.Request(
                this.addressUrl+addressId,
                {method:'get', onSuccess: this.onAddressLoad, onFailure: checkout.ajaxFailure.bind(checkout)}
            );
        }
        else {
            this.fillForm(false);
        }
    },

    newAddress: function(isNew){
        if (isNew) {
            this.resetSelectedAddress();
            Element.show('billing-new-address-form');
        } else {
            Element.hide('billing-new-address-form');
        }
    },

    resetSelectedAddress: function(){
        var selectElement = $('billing-address-select')
        if (selectElement) {
            selectElement.value='';
        }
    },

    fillForm: function(transport){
        var elementValues = {};
        if (transport && transport.responseText){
            try{
                elementValues = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                elementValues = {};
            }
        }
        else{
            this.resetSelectedAddress();
        }
        arrElements = Form.getElements(this.form);
        for (var elemIndex in arrElements) {
            if (arrElements[elemIndex].id) {
                var fieldName = arrElements[elemIndex].id.replace(/^billing:/, '');
                arrElements[elemIndex].value = elementValues[fieldName] ? elementValues[fieldName] : '';
                if (fieldName == 'country_id' && billingForm){
                    billingForm.elementChildLoad(arrElements[elemIndex]);
                }
            }
        }
    },

    setUseForShipping: function(flag) {
        $('shipping:same_as_billing').checked = flag;
    },

    save: function(){

        if (checkout.loadWaiting!=false) return;

        var validator = new Validation(this.form);
        if (validator.validate()) {
            if (checkout.method=='register' && $('billing:customer_password').value != $('billing:confirm_password').value) {
                alert(Translator.translate('Error: Passwords do not match'));
                return;
            }
            checkout.setLoadWaiting('billing');

//            if ($('billing:use_for_shipping') && $('billing:use_for_shipping').checked) {
//                $('billing:use_for_shipping').value=1;
//            }

            var request = new Ajax.Request(
                this.saveUrl,
                {
                    method: 'post',
                    onComplete: this.onComplete,
                    onSuccess: this.onSave,
                    onFailure: checkout.ajaxFailure.bind(checkout),
                    parameters: Form.serialize(this.form)
                }
            );
        }
    },

    resetLoadWaiting: function(transport){
        checkout.setLoadWaiting(false);
    },

    /**
        This method recieves the AJAX response on success.
        There are 3 options: error, redirect or html with shipping options.
    */
    nextStep: function(transport){

        if (transport && transport.responseText){
            try{
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }

        if (response.error){
            if ((typeof response.message) == 'string') {
                alert(response.message);
            } else {
                if (window.billingRegionUpdater) {
                    billingRegionUpdater.update();
                }

                alert(response.message.join("\n"));
            }

            return false;
        }

        checkout.setStepResponse(response);

        // DELETE
        //alert('error: ' + response.error + ' / redirect: ' + response.redirect + ' / shipping_methods_html: ' + response.shipping_methods_html);
        // This moves the accordion panels of one page checkout and updates the checkout progress
        //checkout.setBilling();
    }
}

// shipping
var Shipping = Class.create();
Shipping.prototype = {
    initialize: function(form, addressUrl, saveUrl, methodsUrl){
        this.form = form;
        if ($(this.form)) {
            $(this.form).observe('submit', function(event){this.save();Event.stop(event);}.bind(this));
        }
        this.addressUrl = addressUrl;
        this.saveUrl = saveUrl;
        this.methodsUrl = methodsUrl;
        this.onAddressLoad = this.fillForm.bindAsEventListener(this);
        this.onSave = this.nextStep.bindAsEventListener(this);
        this.onComplete = this.resetLoadWaiting.bindAsEventListener(this);

		//-----------------
		// Icommerce addon
		//-----------------

		this.getAddressUrl = "";

		//---------------------
		// Icommerce addon end
		//---------------------
    },


	//-----------------
	// Icommerce addon
	//-----------------
	getAddressData: function(pno)
	{
		if (pno)
		{
			var request = new Ajax.Request(this.getAddressUrl + '?pno=' + pno, {method:'get', onSuccess: this.onAddressDataLoad});
		}
	},

	onAddressDataLoad: function(transport)
	{
		var data = eval(transport.responseText);
		//alert(data[0]);
		document.getElementById("shipping:firstname").value = data[0];
		document.getElementById("shipping:lastname").value = data[1];
		document.getElementById("shipping:street1").value = data[2];
		document.getElementById("shipping:postcode").value = data[3];
		document.getElementById("shipping:city").value = data[4];
		if(data[5] == 209)
		{
				document.getElementById("shipping:country_id").value = "SE";
		}
	},

	//---------------------
	// Icommerce addon end
	//---------------------

    setAddress: function(addressId){
        if (addressId) {
            request = new Ajax.Request(
                this.addressUrl+addressId,
                {method:'get', onSuccess: this.onAddressLoad, onFailure: checkout.ajaxFailure.bind(checkout)}
            );
        }
        else {
            this.fillForm(false);
        }
    },

    newAddress: function(isNew){
        if (isNew) {
            this.resetSelectedAddress();
            Element.show('shipping-new-address-form');
        } else {
            Element.hide('shipping-new-address-form');
        }
        shipping.setSameAsBilling(false);
    },

    resetSelectedAddress: function(){
        var selectElement = $('shipping-address-select')
        if (selectElement) {
            selectElement.value='';
        }
    },

    fillForm: function(transport){
        var elementValues = {};
        if (transport && transport.responseText){
            try{
                elementValues = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                elementValues = {};
            }
        }
        else{
            this.resetSelectedAddress();
        }
        arrElements = Form.getElements(this.form);
        for (var elemIndex in arrElements) {
            if (arrElements[elemIndex].id) {
                var fieldName = arrElements[elemIndex].id.replace(/^shipping:/, '');
                arrElements[elemIndex].value = elementValues[fieldName] ? elementValues[fieldName] : '';
                if (fieldName == 'country_id' && shippingForm){
                    shippingForm.elementChildLoad(arrElements[elemIndex]);
                }
            }
        }
    },

    setSameAsBilling: function(flag) {
        $('shipping:same_as_billing').checked = flag;
// #5599. Also it hangs up, if the flag is not false
//        $('billing:use_for_shipping_yes').checked = flag;
        if (flag) {
            this.syncWithBilling();
        }
    },

    syncWithBilling: function () {
        $('billing-address-select') && this.newAddress(!$('billing-address-select').value);
        $('shipping:same_as_billing').checked = true;
        if (!$('billing-address-select') || !$('billing-address-select').value) {
            arrElements = Form.getElements(this.form);
            for (var elemIndex in arrElements) {
                if (arrElements[elemIndex].id) {
                    var sourceField = $(arrElements[elemIndex].id.replace(/^shipping:/, 'billing:'));
                    if (sourceField){
                        arrElements[elemIndex].value = sourceField.value;
                    }
                }
            }
            //$('shipping:country_id').value = $('billing:country_id').value;
            shippingRegionUpdater.update();
            $('shipping:region_id').value = $('billing:region_id').value;
            $('shipping:region').value = $('billing:region').value;
            //shippingForm.elementChildLoad($('shipping:country_id'), this.setRegionValue.bind(this));
        } else {
            $('shipping-address-select').value = $('billing-address-select').value;
        }
    },

    setRegionValue: function(){
        $('shipping:region').value = $('billing:region').value;
    },

    save: function(){
        if (checkout.loadWaiting!=false) return;
        var validator = new Validation(this.form);
        if (validator.validate()) {
            checkout.setLoadWaiting('shipping');
            var request = new Ajax.Request(
                this.saveUrl,
                {
                    method:'post',
                    onComplete: this.onComplete,
                    onSuccess: this.onSave,
                    onFailure: checkout.ajaxFailure.bind(checkout),
                    parameters: Form.serialize(this.form)
                }
            );
        }
    },

    resetLoadWaiting: function(transport){
        checkout.setLoadWaiting(false);
    },

    nextStep: function(transport){
        if (transport && transport.responseText){
            try{
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }
        if (response.error){
            if ((typeof response.message) == 'string') {
                alert(response.message);
            } else {
                if (window.shippingRegionUpdater) {
                    shippingRegionUpdater.update();
                }
                alert(response.message.join("\n"));
            }

            return false;
        }

        checkout.setStepResponse(response);

        /*
        var updater = new Ajax.Updater(
            'checkout-shipping-method-load',
            this.methodsUrl,
            {method:'get', onSuccess: checkout.setShipping.bind(checkout)}
        );
        */
        //checkout.setShipping();
    }
}

// shipping method
var ShippingMethod = Class.create();
ShippingMethod.prototype = {
    initialize: function(form, saveUrl){
        this.form = form;
        if ($(this.form)) {
            $(this.form).observe('submit', function(event){this.save();Event.stop(event);}.bind(this));
        }
        this.saveUrl = saveUrl;
        this.validator = new Validation(this.form);
        this.onSave = this.nextStep.bindAsEventListener(this);
        this.onComplete = this.resetLoadWaiting.bindAsEventListener(this);
    },

    validate: function() {
        var methods = document.getElementsByName('shipping_method');
        if (methods.length==0) {
            alert(Translator.translate('Your order can not be completed at this time as there is no shipping methods available for it. Please make neccessary changes in your shipping address.'));
            return false;
        }

        if(!this.validator.validate()) {
            return false;
        }

        for (var i=0; i<methods.length; i++) {
            if (methods[i].checked) {
                return true;
            }
        }
        alert(Translator.translate('Please specify shipping method.'));
        return false;
    },

    save: function(){

        if (checkout.loadWaiting!=false) return;
        if (this.validate()) {
            checkout.setLoadWaiting('shipping-method');
            var request = new Ajax.Request(
                this.saveUrl,
                {
                    method:'post',
                    onComplete: this.onComplete,
                    onSuccess: this.onSave,
                    onFailure: checkout.ajaxFailure.bind(checkout),
                    parameters: Form.serialize(this.form)
                }
            );
        }
    },

    resetLoadWaiting: function(transport){
        checkout.setLoadWaiting(false);
    },

    nextStep: function(transport){

        if (transport && transport.responseText){
            try{
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }

        if (response.error) {
            alert(response.message);
            return false;
        }

        if (response.update_section) {
        	$('checkout-'+response.update_section.name+'-load').update(response.update_section.html);
            response.update_section.html.evalScripts();
        }

        $$('.cvv-what-is-this').each(function(element){
            Event.observe(element, 'click', toggleToolTip);
        });

        if (response.goto_section) {
            checkout.gotoSection(response.goto_section);
            checkout.reloadProgressBlock();
            return;
        }

        if (response.payment_methods_html) {
            $('checkout-payment-method-load').update(response.payment_methods_html);
        }

        checkout.setShippingMethod();
    }
}


// payment
var Payment = Class.create();
Payment.prototype = {
    beforeInitFunc:$H({}),
    afterInitFunc:$H({}),
    beforeValidateFunc:$H({}),
    afterValidateFunc:$H({}),
    initialize: function(form, saveUrl){
        this.form = form;
        this.saveUrl = saveUrl;
        this.onSave = this.nextStep.bindAsEventListener(this);
        this.onComplete = this.resetLoadWaiting.bindAsEventListener(this);
    },

    addBeforeInitFunction : function(code, func) {
        this.beforeInitFunc.set(code, func);
    },

    beforeInit : function() {
        (this.beforeInitFunc).each(function(init){
            (init.value)();
        });
    },

    init : function () {
        this.beforeInit();
        var elements = Form.getElements(this.form);
        if ($(this.form)) {
            $(this.form).observe('submit', function(event){this.save();Event.stop(event);}.bind(this));
        }
        this.forceRedirect = false;
        var method = null;
        var dibs_redirect = false;
        for (var i=0; i<elements.length; i++) {
            if (elements[i].name=='payment[method]') {
                if (elements[i].checked) {
                    method = elements[i].value;
                }
            } else {
            	if (elements[i].name=='payment[dibs_force_redirect]' || elements[i].name == 'form_key') {
            		dibs_redirect = true;
            	} else {
	                elements[i].disabled = true;
	            }
            }
            elements[i].setAttribute('autocomplete','off');
        }
        if (method) {
        	this.switchMethod(method);
        	if (method=="dibs") {
		        this.forceRedirect = dibs_redirect;
		    }
	    }
        this.afterInit();
    },

    addAfterInitFunction : function(code, func) {
        this.afterInitFunc.set(code, func);
    },

    afterInit : function() {
        (this.afterInitFunc).each(function(init){
            (init.value)();
        });
    },

    switchMethod: function(method){
        if (this.currentMethod && $('payment_form_'+this.currentMethod)) {
            this.changeVisible(this.currentMethod, true);
            $('payment_form_'+this.currentMethod).fire('payment-method:switched-off', {method_code : this.currentMethod});
        }
        if ($('payment_form_'+method)){
            this.changeVisible(method, false);
            $('payment_form_'+method).fire('payment-method:switched', {method_code : method});
        } else {
            //Event fix for payment methods without form like "Check / Money order"
            document.body.fire('payment-method:switched', {method_code : method});
        }
        if (method) {
            this.lastUsedMethod = method;
        }
        this.currentMethod = method;
    },

    changeVisible: function(method, mode) {
        var block = 'payment_form_' + method;
        [block + '_before', block, block + '_after'].each(function(el) {
            var element = $(el);
            if (element) {
                element.style.display = (mode) ? 'none' : '';
                // IE doesn't like element.select() so we use following way:
                ['input', 'select', 'textarea', 'button'].each(function(el2) {
                    var nodes = element.getElementsByTagName(el2);
                    if (nodes.length > 0) {
                        for (var i=0; i < nodes.length; i++) {
                            nodes[i].disabled = mode;
                        }
                    }
                });
            }
        });
    },

    addBeforeValidateFunction : function(code, func) {
        this.beforeValidateFunc.set(code, func);
    },

    beforeValidate : function() {
        var validateResult = true;
        var hasValidation = false;
        (this.beforeValidateFunc).each(function(validate){
            hasValidation = true;
            if ((validate.value)() == false) {
                validateResult = false;
            }
        }.bind(this));
        if (!hasValidation) {
            validateResult = false;
        }
        return validateResult;
    },

    validate: function() {
        var result = this.beforeValidate();
        if (result) {
            return true;
        }
        var methods = document.getElementsByName('payment[method]');
        if (methods.length==0) {
            alert(Translator.translate('Your order can not be completed at this time as there is no payment methods available for it.'));
            return false;
        }
        for (var i=0; i<methods.length; i++) {
            if (methods[i].checked) {
                return true;
            }
        }
        // QuickCheckout addon
	    var myquickcheckout = false;
		try{myquickcheckout = isQuickCheckoutEnabled;}catch(err){}

	    if(myquickcheckout){
	    	checkout.setLoadWaiting(false);
	    }
        // End of QuickCheckout addon

        result = this.afterValidate();
        if (result) {
            return true;
        }
        alert(Translator.translate('Please specify payment method.'));
        return false;
    },

    addAfterValidateFunction : function(code, func) {
        this.afterValidateFunc.set(code, func);
    },

    afterValidate : function() {
        var validateResult = true;
        var hasValidation = false;
        (this.afterValidateFunc).each(function(validate){
            hasValidation = true;
            if ((validate.value)() == false) {
                validateResult = false;
            }
        }.bind(this));
        if (!hasValidation) {
            validateResult = false;
        }
        return validateResult;
    },

    save: function(){
        if (checkout.loadWaiting!=false) return;
        var validator = new Validation(this.form);
        if (this.validate() && validator.validate()) {
            checkout.setLoadWaiting('payment');
            var request = new Ajax.Request(
                this.saveUrl,
                {
                    method:'post',
                    onComplete: this.onComplete,
                    onSuccess: this.onSave,
                    onFailure: checkout.ajaxFailure.bind(checkout),
                    parameters: Form.serialize(this.form)
                }
            );
        }
    },

    resetLoadWaiting: function(){
        checkout.setLoadWaiting(false);
    },

    nextStep: function(transport){
        if (transport && transport.responseText){
            try{
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }
        /*
        * if there is an error in payment, need to show error message
        */
        if (response.error) {
            if (response.fields) {
                var fields = response.fields.split(',');
                for (var i=0;i<fields.length;i++) {
                    var field = null;
                    if (field = $(fields[i])) {
                        Validation.ajaxError(field, response.error);
                    }
                }
                return;
            }
            alert(response.error);
            return;
        }

        checkout.setStepResponse(response);

        //checkout.setPayment();
    }
}

var Review = Class.create();
Review.prototype = {
    initialize: function(saveUrl, successUrl, agreementsForm){
        this.saveUrl = saveUrl;
        this.successUrl = successUrl;
        this.agreementsForm = agreementsForm;
        this.onSave = this.nextStep.bindAsEventListener(this);
        this.onComplete = this.resetLoadWaiting.bindAsEventListener(this);
    },

    save: function(){
        if (checkout.loadWaiting!=false) return;
        checkout.setLoadWaiting('review');
        var params = Form.serialize(payment.form);
        if (this.agreementsForm) {
            params += '&'+Form.serialize(this.agreementsForm);
        }
        params.save = true;
        var request = new Ajax.Request(
            this.saveUrl,
            {
                method:'post',
                parameters:params,
                onComplete: this.onComplete,
                onSuccess: this.onSave,
                onFailure: checkout.ajaxFailure.bind(checkout)
            }
        );
    },

    resetLoadWaiting: function(transport){
        checkout.setLoadWaiting(false, this.isSuccess);
    },

    nextStep: function(transport){
        if (transport && transport.responseText) {
            var caught_except = false;
            try{
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
                caught_except = true;
            }
            if (response.redirect ) {
                // Changed by Icommerce for dibs lightbox.
                // Adapted for Payson - ATS
                // Adapted to work responsively
                var has_tpc_support = 0;
                var use_payment_lightbox = true;
                try { has_tpc_support=tpc_query_response;  } catch( e ){ }
                try { use_payment_lightbox = payment_lightbox; } catch( e ) { }

                if ( ( ( (payment.currentMethod == 'payson' ||
                      payment.currentMethod == 'auriga' ||
                      payment.currentMethod == 'dibs'   ||
		      payment.currentMethod == 'handelsbankfaktura' ||
		      payment.currentMethod == 'handelsbankenpartpayment') &&
                     has_tpc_support=="1" ) ) && use_payment_lightbox && payment.forceRedirect==0) {
                    // Sizes for windows, Dibs is slightly wider - ATS
                    var w, h, t;
                    if(payment.currentMethod == 'payson')
                        { w = 650; h=600; t="Payson"; }
                    else if(payment.currentMethod == 'auriga')
                        { w = 700; h=750; t="Auriga"; }
		            else if(payment.currentMethod == 'handelsbankfaktura')
                        { w = 700; h=750; t="Handelsbank faktura"; }
		            else if(payment.currentMethod == 'handelsbankenpartpayment')
                        { w = 700; h=750; t="Handelsbank Delbetalning"; }
                    else if(payment.currentMethod == 'resursinvoice')
                        { w = 800; h=500; t="Resurbank Invoice"; }
                    else
                        { w = 800; h=600; t="DIBS"; }

                    // For mobile devices (requires meta tag width=device-width for android)
                    var windowWidth = (window.innerWidth > 0) ? window.innerWidth : screen.width;
                    var windowHeight = (window.innerHeight > 0) ? window.innerHeight : screen.height;
                    if (windowWidth > 0 && windowHeight > 0 && windowWidth < 650) {
                        // landscape
                        if (windowHeight < windowWidth) {
                            w = parseInt(windowWidth * 0.7);
                            h = parseInt(windowHeight * 0.9);
                        } else {
                            w = parseInt(windowWidth * 0.9);
                            h = parseInt(windowHeight * 0.7);
                        }
                    }

                    payWindow = new Window({
                            className: "alphacube",
                            title: t,
                            url: response.redirect,
                            minimizable: false,
                            maximizable: false,
                            draggable: false,
                            closable: false,
                            showEffect: Element.show,

                            // Set this to taste.
                            width:w,
                            height:h,

                            destroyOnClose: true,
                            recenterAuto:true
                            });
                    payWindow.showCenter(true);
                } else {
                    this.isSuccess = true;
                    location.href = response.redirect;
                }
                return;
            }
            if (response.success) {
                this.isSuccess = true;
                window.location=this.successUrl;
            }
            //AR: paypal iframe handling
            else if($('payment_form_hosted_pro') && response.update_section.name && response.update_section.name == 'paypaliframe'){
                checkout.setLoadWaiting(false);
                $('payment_form_hosted_pro').update(response.update_section.html);
            }
            // AR: end of paypal iframe handling
            else{
                var msg = response.error_messages;
                if (typeof(msg)=='object') {
                    msg = msg.join("\n");
                } else if( caught_except ) {
                    msg = "Exception evaluating response: \n\n";
                    //msg += transport.responseText;
                    msg = "Problem saving your order with this payment method. Please contact support for this. You may also use other payment methods (If available).";
                } else if(!msg && response.error) {
                    msg = "An error occurred. Please try again or contact us."
                    if(typeof Translator === "object") {
                        msg = Translator.translate(msg);
                    }
                }

                // QuickCheckout addon
		    	var qce = false;
				try{qce = isQuickCheckoutEnabled;}catch(err){}

		    	if(qce){
		    		checkout.setLoadWaiting(false);
		    	}
		    	// End of QuickCheckout addon

                if (msg) alert(msg);

                if (response.update_section) {
                    $('checkout-'+response.update_section.name+'-load').update(response.update_section.html);
                }

                if (response.goto_section) {
                    checkout.gotoSection(response.goto_section);
                }
            }
        }
    },

    isSuccess: false
}

jQuery(document).ready(function(){
	var updateSSN = setInterval(function(){
		if (jQuery('.ssn input').length > 0 && jQuery('.ssn input').css('display') != 'none'){
			var $ssnVal = null;
			if (document.getElementById('billing:personid') != null && jQuery(document.getElementById('billing:personid')).is(':visible')) {
				$ssnVal = document.getElementById('billing:personid').value;
			} else if (document.getElementById('billing[personid]') != null && jQuery(document.getElementById('billing[personid]')).is(':visible')) {
				$ssnVal = document.getElementById('billing[personid]').value;//jQuery('.ssn input').val();
			}

			if ($ssnVal != null && $ssnVal != null != '' && !(typeof $ssnVal === undefined)) {
				jQuery('#klarna_invoice_personalnumber').val($ssnVal);
				jQuery('#klarna_partpayment_personalnumber').val($ssnVal);
			}
		} else {
			clearInterval(updateSSN);
		}
	}, 8000);
});
