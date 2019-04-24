jQuery(document).ready(
    function ($) {

    var login_with_livechat = document.getElementById('login-with-livechat');

    var sendMessage = function (msg) {
        login_with_livechat.contentWindow.postMessage(msg, '*');
    };

    var logoutLiveChat = function () {
        sendMessage('logout');
    };

    function receiveMessage(event) 
    {
        var livechatMessage = JSON.parse(event.data);
        
        if (livechatMessage.type === 'logged-in' && livechatMessage.eventTrigger === 'click') {
            jQuery('#login').hide();
            jQuery('iframe#login-with-livechat').addClass('hidden');
            jQuery('.progress-button').removeClass('hidden');
            
            
            jQuery('#livechat_license_number').val(livechatMessage.license);
            jQuery('#livechat_login').val(livechatMessage.email);

            jQuery('#livechat_already_have form')
                    .unbind('submit')
                    .submit();
        }
    }

    window.addEventListener("message", receiveMessage, false);

    window.logoutLiveChat = function () {
        sendMessage('logout');
    };
    
    document.getElementById("reset_settings").addEventListener(
        "click", function () {
            window.logoutLiveChat();
        }
    );
    }
);
