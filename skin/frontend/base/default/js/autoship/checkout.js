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

// Call Magento payment save ajax call
function paymentSaveAjaxCall(paymentSaveThis) {
    (function($){
        // Make original payment save function ajax call to Magento
        var formParams = Form.serialize(paymentSaveThis.form);
        var request = new Ajax.Request(
            paymentSaveThis.saveUrl,
            {
                method:'post',
                onComplete: paymentSaveThis.onComplete,
                onSuccess: paymentSaveThis.onSave,
                onFailure: checkout.ajaxFailure.bind(checkout),
                parameters: formParams
            }
        );
    })(jQuery);
}

// Handle document ready event
jQuery(document).ready(
    function ($) {
        // Replace payment.switchMethod function
        payment.switchMethod = function(method) {
            if (method === "subscribe_pro") {
                SubscribeProCC.init(iframeEnvironmentKey, {
                    "error": "#subscribe_pro_error",
                    "token": "#subscribe_pro_payment_token",
                    "firstName": "#subscribe_pro_firstname",
                    "lastName": "#subscribe_pro_lastname",
                    "cardType": "#subscribe_pro_cc_card_type",
                    "numberContainer": "#spreedly_number_container",
                    "cvvContainer": "#spreedly_cvv_container",
                    "expiryDate": "#subscribe_pro_cc_expiry",
                    "expiryMonth": "#subscribe_pro_cc_exp_month",
                    "expiryYear": "#subscribe_pro_cc_exp_year",
                    "hiddenCCNum": "#subscribe_pro_cc_number",
                    "numberInputStyle": "width: 100%; font-size: 15px; font-family: \"Helvetica Neue\", Verdana, Arial, sans-serif; color: #636363;",
                    "cvvInputStyle": "width: 100%; font-size: 15px; font-family: \"Helvetica Neue\", Verdana, Arial, sans-serif; color: #636363;"
                });
            }
            return Payment.prototype.switchMethod.call(this, method);
        };
        // Replace payment.save function
        payment.save = function () {
            var self = this;
            if (this.currentMethod !== 'subscribe_pro') {
                // Call original payment save function
                return Payment.prototype.save.call(this);
            }
            // Check if we are already running and return immediately if so
            if (checkout.loadWaiting !== false) return;
            // Check if we already have a tokenized card
            if (SubscribeProCC.alreadyTokenized()) {
                // Turn on waiting indicator
                checkout.setLoadWaiting('payment');
                // Call Magento payment save ajax call
                paymentSaveAjaxCall(this);
            } else {
                SubscribeProCC.tokenize(function() {
                    paymentSaveAjaxCall(self);
                });
            }
        };
    }
);
