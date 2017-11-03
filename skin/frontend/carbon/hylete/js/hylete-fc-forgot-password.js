var FC = FC || {};
FC.ForgotPassword = {
    forgot: function(emailField, messageContainer, triggerEl) {
        if (!Validation.validate(emailField)) {
            FC.Utils.shake(emailField.next('.validation-advice'));
            return;
        }

        if (this.showPopupWithCaptcha(emailField.getValue())) {
            return;
        }

        triggerEl.setOpacity(0);
        var parentEl = triggerEl.up('.input-box').down('input');
        if (parentEl) {
            FC.Loader.toggle(false, parentEl);
        }

        new Ajax.Request(forgotpasswordUrl, {
            parameters: {
                email: emailField.getValue()
            },
            onSuccess: function(transport) {
                if (parentEl) {
                    FC.Loader.toggle(false, parentEl);
                }
                triggerEl.setOpacity(1);

                FC.Messenger.clear(messageContainer);

                var response = transport.responseText.evalJSON();
                if (response.error) {
                    FC.Messenger.add(response.error, messageContainer, 'error');
                    var captchaEl = $('user_forgotpassword');
                    if (captchaEl && captchaEl.captcha) {
                        captchaEl.captcha.refresh(captchaEl.previous('img.captcha-reload'));
                        // try to focus input element:
                        var inputEl = $('captcha_' + id);
                        if (inputEl) {
                            inputEl.focus();
                        }
                    }
                } else if (response.message) {
                    FC.Messenger.add(response.message, messageContainer, 'success');
                }
            }
        });
    },

    showPopupWithCaptcha: function(email) {
        var forgotWindow = $('firecheckout-forgot-window');
        if (forgotWindow.down('#user_forgotpassword')) {
            firecheckoutWindow
                .update(forgotWindow)
                .show();
            forgotWindow.down('#email_address').setValue(email);
            var captchaInput = forgotWindow.down('#captcha_user_forgotpassword');
            if (captchaInput) {
                captchaInput.focus();
            }
            return true;
        }
        return false;
    }
};


