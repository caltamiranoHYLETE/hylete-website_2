document.observe('dom:loaded', function() {
    /**
     * Enable or disable the place order button according to the checkbox for creating account
     */
    Billing.prototype.setCreateAccount = Billing.prototype.setCreateAccount.wrap(function (setCreateAccount, flag) {
        setCreateAccount(flag);
        var checkoutBtn = $$('.firecheckout-section-submit .button.btn-checkout').first();
        if (flag) {
            $('billing:register_account').checked = true;
            $('billing:register_account').value = 1;
        } else {
            $('billing:register_account').checked = false;
            $('billing:register_account').value = 0;
        }
        if (checkoutBtn) {
            if (flag) {
                checkoutBtn.addClassName('button-disabled').disable();
            } else {
                checkoutBtn.removeClassName('button-disabled').enable();
            }
        }
    });

    /**
     * Insert button after passwords fields
     */
    $('register-customer-password').insert(
        [
            '<div>',
                '<button type="submit" class="firecheckout-set button btn-checkout" title="'+ Translator.translate('Create Account') +'" name="register" id="billing-register-button">',
                    '<span>',
                        '<span>'+ Translator.translate('Create Account') +'</span>',
                    '</span>',
                '</button>',
            '</div>'
        ].join('')
    );

    /**
     *  Uncheck by default the checkbox for creating a new account
     */
    if (window.billing) {
        billing.setCreateAccount(false)
    }

    /**
     * Trigger account creation
     */
    $('billing-register-button').observe('click', function(event) {
        event.preventDefault();
        Event.stop(event);
        FC.Loader.show();
        if (
            Validation.validate(document.getElementById('billing:email'))
            && Validation.validate(document.getElementById('billing:customer_password'))
            && Validation.validate(document.getElementById('billing:confirm_password'))
        ){
            $('firecheckout-form').submit();
        } else {
            FC.Loader.hide();
        }
    });
});

