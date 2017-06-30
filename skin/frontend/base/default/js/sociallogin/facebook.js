;
(function ($, window, undefined) {

    $.widget('vaimo.socialLogin', {
        initialized: undefined,

        _create: function () {
            if (!this.configuration.lazyInit) {
                this.initFacebookApi();
            }
            window.facebookPopup = this.facebookPopupAsync.bind(this);
            window.facebookLogin = this.facebookLoginAsync.bind(this);
        },

        onFacebookScriptLoad: function () {
            FB.init(this.configuration);
            if (FB.getLoginStatus !== undefined) {
                FB.getLoginStatus(this.getLoginStatus.bind(this));
            }
        },

        initFacebookApi: function () {
            if (this.isIframe()) {
                return $.Deferred().reject();
            } else if (this.initialized === undefined) {
                this.initialized = false;
                return $.ajax({
                    url: this.configuration.apiUrl,
                    dataType: 'script',
                    success: function () {
                        this.initialized = true;
                        this.onFacebookScriptLoad.call(this)
                    }.bind(this),
                    cache: true
                });
            } else if (this.initialized) {
                return $.Deferred().resolve().promise();
            }
        },

        getLoginStatus: function (response) {
            if (response.status == 'connected') {
                FB.api('/me', function (response) {
                    // console.log("Connected as " + response.name);
                });
            } else if (response.status === 'not_authorized') {
                // Do stuff when user refuses to authorize facebook app
            } else {
                // Do stuff when not logged.
            }
        },

        facebookPopup: function () {
            if (this.isChromeOnIOS()) {
                window.open(this.configuration.loginUrlChromeIOS, '', null);
            } else {
                FB.login(function (response) {
                    if (response.authResponse) {
                        this.facebookLogin(response.authResponse.signedRequest);
                    }
                }, {scope: this.configuration.scope});
            }
        },

        facebookLogin: function (data) {
            if (typeof data == 'undefined' || data == '') {
                document.location = this.configuration.loginUrl;
                return;
            }
            this.submitLoginForm(data);
        },

        // fbsr_ cookie is not always available and therefore login fails sometimes
        // try to post signedRequest-data as it checks also on _REQUEST['signed_request']
        submitLoginForm: function (data) {
            var formHtml = '<form method="POST" action="' + this.configuration.loginUrl + '">';
            formHtml += '<input type="hidden" name="signed_request" value="' + data + '"/>';
            formHtml += '</form>';

            $(formHtml).appendTo('body')[0].submit();
        },

        facebookPopupAsync: function () {
            return this.initFacebookApi().done(this.facebookPopup.bind(this));
        },

        facebookLoginAsync: function () {
            return this.initFacebookApi().done(this.facebookLogin.bind(this));
        },

        isIframe: function () {
            return window !== window.top;
        },

        isChromeOnIOS: function () {
            return navigator.userAgent.match('CriOS');
        }
    });
})(jQuery, window);