document.observe("dom:loaded", function() {

	var jsonFields = ["bakerlooorder_json_payload", "bakerlooorder_json_request_headers"];

	for (var i = jsonFields.length - 1; i >= 0; i--) {
		var jvalue = $(jsonFields[i]).value;
		$(jsonFields[i]).value = jsl.format.formatJson(jvalue);
	};

});


TryAgain = function(btn, postURL) {

    if(confirm(Translator.translate('Are you sure?'))) {
        $('edit_form').writeAttribute('action', postURL);
        $('edit_form').submit();
		btn.toggle();
    }

}