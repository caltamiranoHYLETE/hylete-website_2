/**
 * Subscribe Pro - Subscriptions Management Extension
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to commercial source code license of SUBSCRIBE PRO INC.
 *
 * @category  SubscribePro
 * @package   SubscribePro_Autoship
 * @author    Subscribe Pro Support <support@subscribepro.com>
 * @copyright 2009-2020 SUBSCRIBE PRO INC. All Rights Reserved.
 * @license   http://www.subscribepro.com/terms-of-service/ Subscribe Pro Terms of Service
 * @link      http://www.subscribepro.com/
 *
 */
var subscribeProApplePay = {

    /**
     * Default config
     */
    config: {
        isCustomerLoggedIn: false,
        cartHasProductsToCreateNewSubscriptions: false,
        onshippingcontactselectedUrl: "",
        onshippingmethodselectedUrl: "",
        onpaymentauthorizedUrl: "",
        createSessionUrl: "",
        apiAccessToken: "",
        paymentRequest: {}
    },

    /** Save merchant display name */
    displayName: "",

    /**
     * Init Apple Pay on this page
     */
    init: function (config) {
        // Save config
        this.config = config;
        // Show button(s)
        this.showApplePayButtons();
    },

    /**
     * Show the Apple Pay button(s) on page
     */
    showApplePayButtons: function () {
        // If guest - Only show button if no subscriptions in cart
        // Show button when customer logged whether or not subscriptions in cart
        if (this.config.isCustomerLoggedIn || !this.config.cartHasProductsToCreateNewSubscriptions) {
            // Check if user has Apple Pay and canMakePayments
            if (window.ApplePaySession) {
                if (ApplePaySession.canMakePayments) {
                    jQuery(".apple-pay-button-container")
                      .click(this.onApplePayButtonClicked.bind(this))
                      .show();
                }
            }
        }
    },

    /**
     * Apple Pay Logic
     * Our entry point for Apple Pay interactions.
     * Triggered when the Apple Pay button is pressed
     */
    onApplePayButtonClicked: function () {
        var self = this;

        // Pre-configured paymentRequest
        const paymentRequest = self.config.paymentRequest;
        // Set merchant display name
        paymentRequest.total = self.replaceTotalLabel(paymentRequest.total, self.displayName);

        // Create session object
        const session = new ApplePaySession(1, paymentRequest);

        // Call Merchant Validation
        session.onvalidatemerchant = function (event) {
            // Requests an Apple Pay merchant session from Subscribe Pro platform and returns a promise.
            jQuery.ajax({
                url: self.config.createSessionUrl,
                type: "POST",
                dataType: "json",
                contentType: "application/json; charset=utf-8",
                crossDomain: true,
                headers: {
                    'Authorization': 'Bearer ' + self.config.apiAccessToken
                },
                data: JSON.stringify({
                    url: event.validationURL
                }),
                success: function (data, textStatus, jqXHR) {
                    // Save display name
                    self.displayName = data.displayName;
                    // Complete validation
                    session.completeMerchantValidation(data);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    session.abort();
                }
            });
        };

        // New shipping contact was selected for payment sheet is init'd the first time
        session.onshippingcontactselected = function (event) {
            // Fetch shipping methods when sheet shown and when new contact chosen
            jQuery.ajax({
                url: self.config.onshippingcontactselectedUrl,
                type: "POST",
                dataType: "json",
                contentType: "application/json; charset=utf-8",
                data: JSON.stringify({
                    shippingContact: event.shippingContact
                }),
                success: function (data, textStatus, jqXHR) {
                    session.completeShippingContactSelection(
                        ApplePaySession.STATUS_SUCCESS,
                        data.newShippingMethods,
                        self.replaceTotalLabel(data.newTotal, self.displayName),
                        data.newLineItems);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    session.abort();
                }
            });
        };

        /**
         * Shipping Method Selection
         * If the user changes their chosen shipping method we need to recalculate
         * the total price. We can use the shipping method identifier to determine
         * which method was selected.
         */
        session.onshippingmethodselected = function (event) {
            // Fetch shipping methods when sheet shown and when new contact chosen
            jQuery.ajax({
                url: self.config.onshippingmethodselectedUrl,
                type: "POST",
                dataType: "json",
                contentType: "application/json; charset=utf-8",
                data: JSON.stringify({
                    shippingMethod: event.shippingMethod
                }),
                success: function (data, textStatus, jqXHR) {
                    session.completeShippingMethodSelection(
                        ApplePaySession.STATUS_SUCCESS,
                        self.replaceTotalLabel(data.newTotal, self.displayName),
                        data.newLineItems);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    session.abort();
                }
            });
        };

        /**
         * Payment Authorization
         * Here you receive the encrypted payment data. You would then send it
         * on to your payment provider for processing, and return an appropriate
         * status in session.completePayment()
         */
        session.onpaymentauthorized = function (event) {
            jQuery.ajax({
                url: self.config.onpaymentauthorizedUrl,
                type: "POST",
                dataType: "json",
                contentType: "application/json; charset=utf-8",
                data: JSON.stringify({
                    payment: event.payment
                }),
                success: function (data, textStatus, jqXHR) {
                    // Complete payment
                    session.completePayment(ApplePaySession.STATUS_SUCCESS);
                    // Redirect to success page
                    window.location.href = data.redirectUrl;
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    session.abort();
                }
            });
        };

        // Start the Apple Pay session
        // All our handlers are setup
        session.begin();
    },

    /**
     * Helper method to replace label in total line item with Merchant Display Name
     *
     * @param total
     * @param label
     * @returns {{label: *, amount: *}}
     */
    replaceTotalLabel: function (total, label) {
        var newTotal = {
            label: label,
            amount: total.amount
        };
        if (total.type) {
            newTotal.type = total.type;
        }

        return newTotal;
    }

};
