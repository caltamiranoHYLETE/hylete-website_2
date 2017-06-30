document.observe("dom:loaded", function() {

    if(null != $("ebizmarts-helpdesk-trigger")) {
        Event.observe("ebizmarts-helpdesk-trigger", "click", function(event) {
            event.preventDefault();
            popWin(this.href, 'helpdesk', 'width=800,height=750,top=0,left=0,resizable=yes,scrollbars=yes');
        });
    }

    var apiHolder = $("bakerloorestful_general_api_key");
    if(null != apiHolder) {
        apiHolder.writeAttribute("autocomplete", "off");
        apiHolder.writeAttribute("readOnly", "true");
    }

});

function pos_debug_truncate(message, url) {
    if(confirm(message)) {
        setLocation(url);
    }
}