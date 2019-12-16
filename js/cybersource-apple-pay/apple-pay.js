var CompleteApplePay = Class.create();

CompleteApplePay.prototype = {
    submitButtonSelector: '.btn-checkout', // Used to find the existing submit buttons and add the Apple Pay button right after them
    applePayButtonSelector: null, // The selector to locate the Apple Pay buttons
    applePayDisplayValue: 'block', // The display mode for the individual Apple Pay buttons (block, inline, etc.)
    templateId: null, // The ID of the template for the buttons.  Should correlate to cybersourceapplepay/cc.phtml
    selectorId: null, // The payment type selector.  For example, selecting Apple Pay or Cash on Delivery.  Attaches a listener to the parent node of this element
    ingestionUrl: null, // The URL used to create the merchant request.  Initiates the transaction (called after paymentRequestUrl)
    paymentTokenId: null, // The ID of the element that stores the payment token.  Should correlate to cybersourceapplepay/cc.phtml
    paymentRequestUrl: null, // Retrieves the payment request data.  This data is passed to a new apple pay session
    buttonSelector: null, // The selector to find all of the apple pay buttons.  Used to bind a click observer to the apple pay button
    originalCallback: false, // Used to store the original callback function for JavaScript onSave callbacks when an order is placed.  Used to complete the apple pay transaction
    applePaySession: null, // The current apple pay session.  Populated with the data from this.paymentRequestUrl
    initialized: false, // Whether or not the object has been initialized.  Protects against accidentally duplication
    initialize: function (configurationObject) {
        if (this.initialized) return;
        this.initialized = true;

        // Configure the object
        for (var i in configurationObject) {
            this[i] = configurationObject[i];
        }

        // Show the Apple Pay option if the customer's device supports it.
        if (this.applePaySupported()) {
            if (!this.handlePaymentSelection) {
                return;
            }

            // Bind this.handlePaymentSelection to the payment method items (apple pay, cash on delivery, etc)
            var bound = this.handlePaymentSelection.bind(this);
            $$('input[name="payment[method]"]').each(function (item) {
                var p = item.up();
                if (p) {
                    p.observe('click', bound);
                }
            });
            // Render the Apple Pay buttons on the page
            this.renderPaymentButton();
            // Show/hide the buttons based on the current state of the page.
            this.managePaymentButtons();
        }

    },
    renderPaymentButton: function () {
        // Find each existing submit button and append the Apple Pay button template after them.
        $$(this.submitButtonSelector).each(function (value) {
            value.setAttribute('data-default-display', value.style.display);
            var template = $(this.templateId).innerHTML;
            $(value).insert({after: template});
        }, this);

        // Then iterate over the newly created buttons and attach the button press listener
        $$(this.buttonSelector).each(function (value) {
            $(value).observe('click', this.handleApplePayButtonPress.bind(this));
            value.removeAttribute('disabled');
            value.style.display = 'none';
        }, this);

    },
    applePaySupported: function () {
        // Apple pay is supported if the ApplePaySession object exists and it can make payments. Hide the Apple Pay
        // UI elements if Apple Pay is not supported
        if (!window.ApplePaySession || !ApplePaySession.canMakePayments()) {
            // Hide even the possibility of using Apple Pay if it's not supported by the customer's device
            var element = document.getElementById(this.selectorId);
            element.parentElement.style.display = 'none';
            return false;
        }
        return true;
    },
    managePaymentButtons: function () {
        // Checks to see if the current payment method is Apple Pay and if it is checked.  If Apple Pay is checked
        // hide the existing submit buttons and show the Apple Pay buttons immediately following them in the DOM.
        $$('input[name="payment[method]"]').each(function (item) {
            if (item.getAttribute('id') === this.selectorId && item.checked) {
                this.hidePaymentButtons();
                this.showApplePayButtons();
            }
        }, this);

    },
    postSuccessObserver: function (event) {
        // Did the transaction complete successfully?  This will make the ding noise if it did and then execute the
        // previously hijacked function call (such as redirecting to the payment succeeded page
        completeApplePay.applePaySession.completePayment(ApplePaySession.STATUS_SUCCESS);
        if (completeApplePay.originalCallback) {
            completeApplePay.originalCallback(event);
        }
    },
    handleApplePayButtonPress: function (e) {
        // If validation is available on the current step, validate the payment request...
        if (checkout && checkout.validateReview) {
            var valid = (checkout && checkout.validateReview(true));
            if (!valid) {
                return false;
            }
        }

        // And then get the payment request JSON from the server.  It is synchronous because Apple Pay refuses to
        // initiate a payment session on any event EXCEPT a user interaction.  An Ajax callback does not satisfy that
        // requirement and so we execute it synchronously to maintain the user-interaction state.
        var result = new Ajax.Request(this.paymentRequestUrl, {
            asynchronous: false
        });
        var response = JSON.parse(result.transport.responseText);
        this.initiateMerchantRequest(response);
        return false;
    },
    initiateMerchantRequest: function (request) {
        // Create a new Apple Pay session.  The source of the data is the payment request that comes from the ingestion URL.
        this.applePaySession = new ApplePaySession(3, request);

        // Triggered once Apple Pay is ready to proceed
        this.applePaySession.onvalidatemerchant = function (e) {
            new Ajax.Request(this.ingestionUrl, {
                method: 'post',
                parameters: {validationURL: e.validationURL},
                onSuccess: function (transport) {
                    // Complete the validation and initiate the actual payment
                    this.applePaySession.completeMerchantValidation(transport.responseJSON);
                }.bind(this),
                onFailure: function (e) {
                    this.applePaySession.abort();
                    alert(e.responseJSON);
                    return false;
                }.bind(this)
            });
        }.bind(this);

        // triggered once a finger print or PIN has been presented
        this.applePaySession.onpaymentauthorized = function (e) {
            $(this.paymentTokenId).value = JSON.stringify(e.payment);

            // This if statement handles differences in checkout between native Magento and Cybersource OPC
            if (review.submit) {
                this.originalCallback = review.onSuccess;
                review.onSuccess = this.postSuccessObserver;
                review.submit();
            } else {
                this.originalCallback = review.onSave;
                review.onSave = this.postSuccessObserver;
                review.save();
            }
        }.bind(this);

        // Everything set up.  let's go!
        completeApplePay.applePaySession.begin();
    },
    handlePaymentSelection: function (e) {
        // Handles whether or not to display the default submission buttons or apple pay
        var target = e.target;
        this.hideApplePayButtons();
        this.hidePaymentButtons();
        // If the customer clicked on the Apple Pay method, show the Apple Pay buttons, hide the rest.
        if (target.getAttribute('id') === this.selectorId) {
            this.showApplePayButtons();
        } else {
            this.showPaymentButtons();
        }
    },
    setApplePayButtons: function (displayValue) {
        // Set the display value for the Apple Pay buttons
        var items = $$(this.applePayButtonSelector);
        items.each(function (value) {
            value.style.display = displayValue;
        });
    },
    setPaymentButtons: function (displayValue) {
        // Sets the display CSS either to its default (getAttribute()) or to the specified value.  Used to hide
        // the default submit button
        var items = $$(this.submitButtonSelector);
        items.each(function (value) {
            if (displayValue === 'none') {
                value.style.display = displayValue;
            } else {
                value.style.display = value.getAttribute('data-default-display');
            }
        });
    },
    showPaymentButtons: function () {
        this.setPaymentButtons();
    },
    hidePaymentButtons: function () {
        this.setPaymentButtons('none');
    },
    showApplePayButtons: function () {
        this.setApplePayButtons(this.applePayDisplayValue);
    },
    hideApplePayButtons: function () {
        this.setApplePayButtons('none');
    }
};

if (!window.completeApplePay) {
    var completeApplePay;
    document.observe('dom:loaded', function () {
        var buildApplePayObject = function () {
            var configElement = document.getElementById('cybersourceapplepay_template');
            if (configElement && !completeApplePay) {
                var configText = configElement.getAttribute('data-component-configuration');
                var config = JSON.parse(configText);
                completeApplePay = new CompleteApplePay(config);

            }
        };
        if (checkout.gotoSection) {
            // Magento checkout uses a different structure than Cybersource
            checkout.previousGoto = checkout.gotoSection;
            // Override the default method
            checkout.gotoSection = function (section, reloadProgressBlock) {
                var result = this.previousGoto(section, reloadProgressBlock);
                if (section === 'payment') {
                    buildApplePayObject();
                } else if (section === 'review') {
                    if (completeApplePay) {
                        completeApplePay.renderPaymentButton();
                        completeApplePay.managePaymentButtons();
                    }
                }
                return result;
            };

            checkout.previousOnSave = checkout.onSave;
            checkout.onSave = function(transport) {
                return checkout.previousOnSave(transport);
            }.bind(completeApplePay);
        } else {
            // Cybersource checkout
            try {
                buildApplePayObject();
            } catch (error) {
                console.error(error);
            }

        }
    });
}
