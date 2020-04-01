/**
 * @author    Mediotype Development <diveinto@mediotype.com>
 * @copyright 2018 Mediotype. All Rights Reserved.
 */

AjaxLogin = Class.create();
AjaxLogin.prototype = {
    initialize: function (config) {

        this.config = Object.extend({
            body: $$('body')[0],
            bodyModalClass: '_has-modal',
            logOutBtn: $$('.open-logout')[0],
            registrationBtn: $$('.open-registration')[0],
            resetPasswordBtn: $$('.reset-password-btn')[0],
            forgetPasswordSection: $$('.mcs-forgetpassword-form-slide')[0],
            forgetPasswordForm: $('mcs-form-forget-reset'),
            loginSection: $$('.mcs-login-form-slide')[0],
            loginForm: $('mcs-form-login'),
            logOutSection: $$('.mcs-logout-slide')[0],
            nasmRegisterCheckbox: $('nasm-register-checkbox'),
            rubiconRegisterCheckbox: $('rubicon-register-checkbox'),
            radianceRegisterCheckboxNasm: $('radiance-lab-create-account-optin-check-nasm'),
            radianceRegisterCheckboxRubicon: $('radiance-lab-create-account-optin-check-rubicon'),
            radianceRegisterCheckbox: $('radiance-lab-create-account-optin-check'),
            registrationForm: $('mcs-form-register'),
            registrationFormNasm: $('mcs-form-register-nasm'),
            registrationFormRubicon: $('mcs-form-register-rubicon'),
            registrationFormNasmWrapper: $$('.mcs-form-register-nasm-wrapper'),
            registrationFormRubiconWrapper: $$('.mcs-form-register-rubicon-wrapper'),
            radianceCreateAccountOptin: $$('.radiance-create-account-optin'),
            registrationSection: $$('.mcs-register-form-slide')[0],
            registrationNasmSection: $$('.mcs-nasm-register-form-slide')[0],
            registrationRubiconSection: $$('.mcs-rubicon-register-form-slide')[0],
            sectionActiveClass: 'show',
            sectionCloseClass: 'close',
            loader: $$('.loading-mask')[0],
            helpWidget: $$('._elevio_widget')[0],
            notificationTemplate: "<ul class=\"messages\"><li class=\"#{type}-msg\"><ul><li><span>#{message}</span></li></ul></li></ul>"
        }, config || {});

        this._addEventListeners();
        this._addCloseEventListener();
    },

    _closeAll: function (changing = null) {
        $$('.modal-slide').each(function (element) {
            if (element.hasClassName(this.config.sectionActiveClass)) {
                element.removeClassName(this.config.sectionActiveClass);
                element.addClassName(this.config.sectionCloseClass);
            }
            if (!changing) {
                this.config.body.removeClassName(this.config.bodyModalClass);
            }

        }.bind(this));
    },

    _toggleLoader: function () {
        if (this.config.loader.getStyle('display') == 'none') {
            this.config.loader.show();
        } else {
            this.config.loader.hide();
        }
    },
    _radianceLabOptinNasm: function (phoneNumber) {
        if(phoneNumber){
            RadianceLabs.linkSMS({opt_in_location:"nasm_create_account",command:"OptInDiscount",phone:phoneNumber});
        }
    },
    _radianceLabOptinRubicon: function (phoneNumber) {
        if(phoneNumber){
            RadianceLabs.linkSMS({opt_in_location:"rubicon_create_account",command:"OptInDiscount",phone:phoneNumber});
        }
    },
    _radianceLabOptinEveryDayAthlete: function (phoneNumber) {
        if(phoneNumber){
            RadianceLabs.linkSMS({opt_in_location:"everyday_athlete_create_account",command:"OptInDiscount",phone:phoneNumber});
        }
    },
    _toggleBodyClass: function () {
        if (!this.config.body.hasClassName(this.config.bodyModalClass)) {
            this.config.body.addClassName(this.config.bodyModalClass);
        } else {
            this.config.body.removeClassName(this.config.bodyModalClass);
            this.closeAllModal();
        }
    },

    _hideHelpWidgetCloseIfActive: function () {
        if (this.config.helpWidget && this.config.helpWidget.down('div') && this.config.helpWidget.down('div').hasClassName('_h8fiz')) {
            this.config.helpWidget.down('div').removeClassName('_h8fiz').addClassName('_11l9b');
        }
    },

    closeAllModal: function () {
        $$('.modal-slide').each(function (element) {
            if (element.hasClassName(this.config.sectionActiveClass)) {
                element.removeClassName(this.config.sectionActiveClass).addClassName(this.config.sectionCloseClass);
                this.config.body.removeClassName(this.config.bodyModalClass);
            }
        }.bind(this));
    },

    _addCloseEventListener: function () {
        $$('.action-close').each(function (element) {
            element.observe('click', function () {
                this.closeAllModal();
            }.bind(this));
        }.bind(this));
    },

    _notification: function (response, section) {
        var template = new Template(this.config.notificationTemplate);
        if (response.success) {
            section.down('.message').update(template.evaluate({
                type: 'success',
                message: response.message
            }));

        } else if (response.error) {
            section.down('.message').update(template.evaluate({
                type: 'error',
                message: response.error
            }));
        }
        jQuery(".modal-content").animate({ scrollTop: 0 }, 1500);
        setTimeout(function () {
            section.down('.message').update();
        }, 16000, section);

    },

    _addEventListeners: function () {
        var self = this;


        this.config.nasmRegisterCheckbox.observe('click', function(e) {
            var checked = this.checked;
            if (checked){
                jQuery('.nasm-register-checkbox-wrapper').fadeOut();
                jQuery(self.config.registrationFormNasmWrapper).fadeIn();
            }else{
                jQuery(self.config.registrationFormNasmWrapper).fadeOut();
            }
        });
        this.config.rubiconRegisterCheckbox.observe('click', function(e) {
            var checked = this.checked;
            if (checked){
                jQuery('.rubicon-register-checkbox-wrapper').fadeOut();
                jQuery(self.config.registrationFormRubiconWrapper).fadeIn();
            }else{
                jQuery(self.config.registrationFormRubiconWrapper).fadeOut();
            }
        });

        this.config.radianceRegisterCheckbox.observe('click', function(e) {
            var checked = this.checked;
            if (checked){
                jQuery(self.config.radianceCreateAccountOptin).fadeIn();
            }else{
                jQuery(self.config.radianceCreateAccountOptin).fadeOut();
            }
        });
        this.config.radianceRegisterCheckboxNasm.observe('click', function(e) {
            var checked = this.checked;
            if (checked){
                jQuery(self.config.radianceCreateAccountOptin).fadeIn();
            }else{
                jQuery(self.config.radianceCreateAccountOptin).fadeOut();
            }
        });
        this.config.radianceRegisterCheckboxRubicon.observe('click', function(e) {
            var checked = this.checked;
            if (checked){
                jQuery(self.config.radianceCreateAccountOptin).fadeIn();
            }else{
                jQuery(self.config.radianceCreateAccountOptin).fadeOut();
            }
        });

        this.config.loginForm && this.config.loginForm.observe('submit', function (e) {
            e.preventDefault();
            Event.stop(e);

            if (loginForm.validator.validate()) {
                setTimeout(function () {
                    self.config.loginSection.removeClassName(self.config.sectionActiveClass);
                }, 4000, self);
                self._toggleLoader();
                new Ajax.Request($('mcs-form-login').action, {
                    method: "post",
                    requestHeaders: {Accept: 'application/json'},
                    parameters: $('mcs-form-login').serialize(),
                    onSuccess: function (transport) {
                        self._toggleLoader();
                        self.config.loginForm.reset();

                        var response;

                        if (transport.responseJSON) {
                            response = transport.responseJSON;
                        } else {
                            response = transport.responseText.evalJSON();
                        }

                        self._notification(response, self.config.loginSection);
                        if (response.redirect) {
                            window.location.reload();
                        }
                    }
                });
            }
        });

        this.config.registrationForm && this.config.registrationForm.observe('submit', function (e) {
            e.preventDefault();
            Event.stop(e);
            if (registrationForm.validator.validate()) {
                self._toggleLoader();
                let radianceLabPhoneNumber = $F($('mcs-form-register')['radiance-lab-create-account-optin']) == '' ? false : $F($('mcs-form-register')['radiance-lab-create-account-optin']);
                new Ajax.Request($('mcs-form-register').action, {
                    method: "post",
                    parameters: $('mcs-form-register').serialize(),
                    onSuccess: function (transport) {
                        self._toggleLoader();
                        self.config.registrationForm.reset();
                        self.config.registrationForm.hide();
                        var response;

                        if (transport.responseJSON) {
                            response = transport.responseJSON;
                        } else {
                            response = transport.responseText.evalJSON();
                        }

                        self._notification(response, self.config.registrationSection);
                        if (response.error) {
                            self.config.registrationForm.show();
                        } else {
                            self._radianceLabOptinEveryDayAthlete(radianceLabPhoneNumber);
                            setTimeout(function () {
                                if (response.redirect) {
                                    window.location.reload();
                                } else {
                                    self._closeAll();
                                }
                            }, 4000);
                        }
                    }
                });
            }
        });

        this.config.registrationFormNasm && this.config.registrationFormNasm.observe('submit', function (e) {
            e.preventDefault();
            Event.stop(e);
            if (registrationFormNasm.validator.validate()) {
                self._toggleLoader();
                let radianceLabPhoneNumber = $F($('mcs-form-register-nasm')['radiance-lab-create-account-optin']) == '' ? false : $F($('mcs-form-register-nasm')['radiance-lab-create-account-optin']);
                new Ajax.Request($('mcs-form-register-nasm').action, {
                    method: "post",
                    parameters: $('mcs-form-register-nasm').serialize(),
                    onSuccess: function (transport) {
                        self._toggleLoader();
                        // self.config.registrationFormNasm.reset();
                        self.config.registrationFormNasm.hide();
                        var response;

                        if (transport.responseJSON) {
                            response = transport.responseJSON;
                        } else {
                            response = transport.responseText.evalJSON();
                        }
                        self._notification(response, self.config.registrationNasmSection);
                        if (response.error) {
                            self.config.registrationFormNasm.show();
                        } else {
                            setTimeout(function () {
                                self._radianceLabOptinNasm(radianceLabPhoneNumber);
                                if (response.redirect) {
                                    window.location.href = "/?nasmaccount";
                                } else if(response.redirect == false) {
                                    //   redirect will return false only when user is not logged in, but has an account and is upgraded to the nasm group
                                    self._notification(response, self.config.loginSection);
                                    self.toLogin();
                                }else{
                                    self._closeAll();
                                }
                            }, 4000);
                        }
                    }
                });
            }
        });
        this.config.registrationFormRubicon && this.config.registrationFormRubicon.observe('submit', function (e) {
            e.preventDefault();
            Event.stop(e);
            if (registrationFormRubicon.validator.validate()) {
                self._toggleLoader();
                let radianceLabPhoneNumber = $F($('mcs-form-register-rubicon')['radiance-lab-create-account-optin']) == '' ? false : $F($('mcs-form-register-rubicon')['radiance-lab-create-account-optin']);
                new Ajax.Request($('mcs-form-register-rubicon').action, {
                    method: "post",
                    parameters: $('mcs-form-register-rubicon').serialize(),
                    onSuccess: function (transport) {
                        self._toggleLoader();
                        // self.config.registrationFormRubicon.reset();
                        self.config.registrationFormRubicon.hide();
                        var response;

                        if (transport.responseJSON) {
                            response = transport.responseJSON;
                        } else {
                            response = transport.responseText.evalJSON();
                        }
                        self._notification(response, self.config.registrationRubiconSection);
                        if (response.error) {
                            self.config.registrationFormRubicon.show();
                        } else {
                            setTimeout(function () {
                                self._radianceLabOptinRubicon(radianceLabPhoneNumber);
                                if (response.redirect) {
                                    window.location.href = "/?rubiconaccount";
                                } else if(response.redirect == false) {
                                    //   redirect will return false only when user is not logged in, but has an account and is upgraded to the nasm group
                                    self._notification(response, self.config.loginSection);
                                    self.toLogin();
                                }else{
                                    self._closeAll();
                                }
                            }, 4000);
                        }
                    }
                });
            }
        });

        this.config.forgetPasswordForm && this.config.forgetPasswordForm.observe('submit', function (e) {
            e.preventDefault();
            Event.stop(e);

            if (forgetForm.validator.validate()) {
                self._toggleLoader();
                new Ajax.Request(self.config.forgetPasswordForm.action, {
                    method: "post",
                    parameters: self.config.forgetPasswordForm.serialize(),
                    onSuccess: function (transport) {
                        var response;
                        self._toggleLoader();
                        if (transport.responseJSON) {
                            response = transport.responseJSON;
                        } else {
                            response = transport.responseText.evalJSON();
                        }
                        self._notification(response, self.config.forgetPasswordSection);
                        setTimeout(function () {
                            self._closeAll();
                        }, 4000, self);
                    }
                });
            }
        });
    },

    openLogin: function () {
        this._hideHelpWidgetCloseIfActive();
        this._toggleBodyClass();
        this.config.loginForm.show();
        this.config.loginSection.removeClassName(this.config.sectionCloseClass).addClassName(this.config.sectionActiveClass);
    },

    openRegistration: function () {
        this._hideHelpWidgetCloseIfActive();
        this._toggleBodyClass();
        this.config.registrationForm.show();
        this.config.registrationSection.removeClassName(this.config.sectionCloseClass).addClassName(this.config.sectionActiveClass);
    },

    logout: function () {
        var self = this;
        this._toggleBodyClass();
        this._hideHelpWidgetCloseIfActive();
        this.config.logOutSection.addClassName(this.config.sectionActiveClass);
        setTimeout(function () {
            self.config.logOutSection.removeClassName(self.config.sectionCloseClass);
        }, 4000, self);

        new Ajax.Request(self.config.logOutUrl, {
            method: "post",
            onSuccess: function (transport) {
                window.location.reload();
            }
        });
    },

    toLogin() {
        this._hideHelpWidgetCloseIfActive();
        this._closeAll(true);
        this.config.loginForm.show();
        this.config.forgetPasswordSection.removeClassName(this.config.sectionActiveClass).addClassName(this.config.sectionCloseClass);
        this.config.loginSection.removeClassName(this.config.sectionCloseClass).addClassName(this.config.sectionActiveClass);
    },

    toLoginFromRegistraion() {
        this._hideHelpWidgetCloseIfActive();
        this._closeAll(true);
        this.config.loginForm.show();
        this.config.registrationNasmSection.removeClassName(this.config.sectionActiveClass).addClassName(this.config.sectionCloseClass);
        this.config.loginSection.removeClassName(this.config.sectionCloseClass).addClassName(this.config.sectionActiveClass);
    },

    toRegistration() {
        this._hideHelpWidgetCloseIfActive();
        this._closeAll(true);
        this.config.registrationForm.show();
        this.config.loginSection.removeClassName(this.config.sectionActiveClass).addClassName(this.config.sectionCloseClass);
        this.config.registrationSection.removeClassName(this.config.sectionCloseClass).addClassName(this.config.sectionActiveClass);
    },

    openNasmRegistration: function () {
        this._hideHelpWidgetCloseIfActive();
        this._toggleBodyClass();
        this.config.registrationFormNasm.show();
        this.config.registrationNasmSection.removeClassName(this.config.sectionCloseClass).addClassName(this.config.sectionActiveClass);
    },
    openRubiconRegistration: function () {
        this._hideHelpWidgetCloseIfActive();
        this._toggleBodyClass();
        this.config.registrationFormRubicon.show();
        this.config.registrationRubiconSection.removeClassName(this.config.sectionCloseClass).addClassName(this.config.sectionActiveClass);
    },

    toResetPassword() {
        this._hideHelpWidgetCloseIfActive();
        this._closeAll(true);
        this.config.loginSection.removeClassName(this.config.sectionActiveClass).addClassName(this.config.sectionCloseClass);
        this.config.forgetPasswordSection.removeClassName(this.config.sectionCloseClass).addClassName(this.config.sectionActiveClass);
    }
};