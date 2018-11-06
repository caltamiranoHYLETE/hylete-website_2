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
            overlay: $$('.modals-overlay')[0],
            logOutBtn: $$('.open-logout')[0],
            registrationBtn: $$('.open-registration')[0],
            resetPasswordBtn: $$('.reset-password-btn')[0],
            forgetPasswordSection: $$('.mcs-forgetpassword-form-slide')[0],
            forgetPasswordForm: $('mcs-form-forget-reset'),
            loginSection: $$('.mcs-login-form-slide')[0],
            loginForm: $('mcs-form-login'),
            logOutSection: $$('.mcs-logout-slide')[0],
            registrationForm: $('mcs-form-register'),
            registrationSection: $$('.mcs-register-form-slide')[0],
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
                this.config.overlay.hide();
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

    _toggleOverlay: function () {
        if (this.config.overlay.getStyle('display') == 'none') {
            this.config.overlay.show();
            this.config.body.addClassName(this.config.bodyModalClass);
        } else {
            this.config.overlay.hide();
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
                this.config.overlay.hide();
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

        this.config.overlay.observe('click', function (event) {
            event.preventDefault();
            this.closeAllModal();
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
        setTimeout(function () {
            section.down('.message').update();
        }, 6000, section);

    },

    _addEventListeners: function () {
        var self = this;

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
        this._toggleOverlay();
        this.config.loginForm.show();
        this.config.loginSection.removeClassName(this.config.sectionCloseClass).addClassName(this.config.sectionActiveClass);
    },

    openRegistration: function () {
        this._hideHelpWidgetCloseIfActive();
        this._toggleOverlay();
        this.config.registrationForm.show();
        this.config.registrationSection.removeClassName(this.config.sectionCloseClass).addClassName(this.config.sectionActiveClass);
    },

    logout: function () {
        var self = this;
        this._toggleOverlay();
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

    toRegistration() {
        this._hideHelpWidgetCloseIfActive();
        this._closeAll(true);
        this.config.registrationForm.show();
        this.config.loginSection.removeClassName(this.config.sectionActiveClass).addClassName(this.config.sectionCloseClass);
        this.config.registrationSection.removeClassName(this.config.sectionCloseClass).addClassName(this.config.sectionActiveClass);
    },

    toResetPassword() {
        this._hideHelpWidgetCloseIfActive();
        this._closeAll(true);
        this.config.loginSection.removeClassName(this.config.sectionActiveClass).addClassName(this.config.sectionCloseClass);
        this.config.forgetPasswordSection.removeClassName(this.config.sectionCloseClass).addClassName(this.config.sectionActiveClass);
    }
};