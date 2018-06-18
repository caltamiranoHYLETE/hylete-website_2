document.observe("dom:loaded", function() {
    BWAutocompleteEmailcapture();
});

function BWIsValidEmailAddress(emailAddress) {
    var pattern = /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i;
    return pattern.test(emailAddress);
};


function BWAutocompleteEmailcapture(){
    var stCookieValue = Mage.Cookies.get('bw_ac');
    if(stCookieValue != null){
        new Ajax.Request('/abandonedcart/main/getSavedEmail', {
            parameters: {isAjax: 1, method: 'POST'},
            onSuccess: function(transport) {
                var ajaxResponse= JSON.parse(transport.responseText);
                var emailaddress= ajaxResponse.emailValue;

                if( BWIsValidEmailAddress( emailaddress ) ){
                    $$('input[type=email]').forEach(function(inputField){
                        inputField.value = emailaddress;
                    });

                    $$('input').forEach(function(inputField){
                        var title= inputField.readAttribute('title');
                        title= String(title).toUpperCase();

                        if(title.indexOf('EMAIL') !== -1)
                        {
                            inputField.value = emailaddress;
                        }

                    });
                }
            }
        });
    }
}
