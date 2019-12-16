var cybersourceSopPost = Class.create();
cybersourceSopPost.prototype = {
    initialize : function(formId, methodCode, loadFieldsUrl, isWebAndMobile, paEnabled, initPaUrl) {

        this.formId = formId;
        this.loadFieldsUrl = loadFieldsUrl;
        this.isWebAndMobile = isWebAndMobile;
        this.code = methodCode;
        this.paEnabled = paEnabled;
        this.initPaUrl = initPaUrl;

        this.bindPlaceOrderButton();
    },

    validate : function() {
        var isValid = true;
        $$('.cs-form-list input, .cs-form-list select').each(function(e) {
            if (!e.disabled && !Validation.validate(e)) {
                isValid = false;
            }
        }, this);
                
        return isValid;
    },

    changeInputOptions : function(param, value) {
        var inputs = ['cc_type', 'cc_number', 'expiration', 'expiration_yr', 'cc_cid', 'cc_cid2', 'echeck_routing', 'echeck_act'];
        inputs.each(function(elemIndex) {
            if ($(this.code + '_' + elemIndex)) {
                $(this.code + '_' + elemIndex).writeAttribute(param, value);
            }
        }, this);
    },

    bindPlaceOrderButton : function() {
        this.changeInputOptions('autocomplete', 'off');

        var button = $('review-buttons-container').down('button');
        button.writeAttribute('onclick', '');
        button.stopObserving('click');

        button.observe('click', function() {

            if (! this.validate()) {
                return this;
            }

            this.submitPayment();

        }.bind(this));
    },

    getCurrentToken : function() {

        if (!$$('.cyber-payment-token:checked').length) {
            return false;
        }

        var token = $$('.cyber-payment-token:checked').first();
        var tokenCvn = $('cyber-payment-token-cvn' + token.readAttribute('data-index'));

        if (tokenCvn && tokenCvn.value) {
            return {
                token: token.value,
                cvv: tokenCvn.value
            };
        }

        return false;
    },

    appendSensitiveData : function() {
        if (!this.isWebAndMobile) {
            if (this.code == 'cybersourcesop') {
                var card_type = document.getElementById("cybersourcesop_cc_type").value;
                var card_number = document.getElementById("cybersourcesop_cc_number").value;
                var card_expiry_date = document.getElementById("cybersourcesop_expiration").value + "-" + document.getElementById("cybersourcesop_expiration_yr").value;
                var card_cvn = document.getElementById('cybersourcesop_cc_cid2');

                document.getElementById("card_type").value = card_type;
                document.getElementById("card_number").value = card_number;
                document.getElementById("card_expiry_date").value = card_expiry_date;

                if (card_cvn && card_cvn.value) {
                    document.getElementById("card_cvn").value = card_cvn.value;
                }
            } else if (this.code == 'cybersourceecheck') {
                if (document.getElementById("cybersourceecheck_echeck_routing")) {
                    document.getElementById("echeck_routing_number").value = document.getElementById("cybersourceecheck_echeck_routing").value;
                }
                if (document.getElementById('cybersourceecheck_echeck_act')) {
                    document.getElementById("echeck_account_number").value = document.getElementById("cybersourceecheck_echeck_act").value;
                }
            }
        }
    },

    submitPayment : function() {

        var csForm = document.getElementById(this.formId);
        var tokenObj = this.getCurrentToken();
        if (tokenObj) {

            if (this.paEnabled) {
                csForm.action = this.initPaUrl;
                csForm.submit();
                return this;
            }

            var paymentForm = $('payment_form_cybersourcesop');
            paymentForm.appendChild(this.createHiddenInput('payment[cs_token]', tokenObj.token));
            paymentForm.appendChild(this.createHiddenInput('payment[cs_token_cvv]', tokenObj.cvv));

            review.save();

            return this;
        }

        var that = this;

        var ccSaveEl = document.getElementById("cc_save");
        var formKeyEl = document.getElementsByName('form_key')[0];

        this.setLoadWaiting();

        var params = {
            code: this.code,
            form_key: formKeyEl.value,
            tokenize: ccSaveEl ? ccSaveEl.value : 0
        };

        new Ajax.Request(that.loadFieldsUrl, {
            method: 'POST',
            parameters: params,
            onComplete: function (transport) {
                var data = transport.responseText.evalJSON();
                if (data.isValid) {
                    for (var name in data.formFields) {
                        csForm.appendChild(that.createHiddenInput(name, data.formFields[name]));
                    }
                    that.appendSensitiveData();
                    that.resetLoadWaiting();

                    that.changeInputOptions('disabled', 'disabled');

                    csForm.submit();

                    return this;
                }

                alert(data.message);
                that.resetLoadWaiting();
            },
            onFailure: function (transport) {
                checkout.ajaxFailure();
            }
        });
    },

    setLoadWaiting : function() {
        checkout.setLoadWaiting('review');
    },

    resetLoadWaiting : function() {
        checkout.setLoadWaiting(false);
    },

    createHiddenInput : function(name, value) {
        var input = document.createElement('input');
        input.setAttribute('type', 'hidden');
        input.setAttribute('id', name);
        input.setAttribute('name', name);
        input.setAttribute('value', value);

        return input;
    }
};
