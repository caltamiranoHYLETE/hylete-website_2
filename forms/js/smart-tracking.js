jQuery( document ).ready(function() {

	jQuery("#smartTrackingForm").validate( {
		ignore: [],
		rules: {
			orderId: { required: true }
		},
		submitHandler: function(form) {

			jQuery('#notFound').hide();
			jQuery('#notAvailable').hide();
			jQuery('#errorShow').hide();
			jQuery('#tracking-results').hide();
			jQuery('#sectionProcessing').show();

			var orderId = jQuery('#orderId').val();
			var requestData = { orderId: orderId };
			jQuery.ajax({
					method      : 'POST',
					url         : restBase + 'tracking/order',
					beforeSend: function(request) {
						request.setRequestHeader("APIKey", ApiKey);
					},
					data        : JSON.stringify(requestData),
					dataType    : 'json',
					contentType: "application/json",
					encode      : true,
					timeout: 10000,
					})
					.done(function(data) {

						jQuery('#sectionProcessing').hide();

						if(data.success == 'false') {
							jQuery('#errorMessage').html(data.message);
							jQuery('#errorShow').fadeIn('500');
						} else {
							var jsonObj = JSON.parse(data);
							jQuery('#tracking-results').empty();
							//console.log(jsonObj);

							var shipment = "shipment";
							if(jsonObj.length > 1) {
								shipment = "shipments";
							}
							var found = "<h2>We found " + jsonObj.length + " matching " + shipment + "</h2>"

							jQuery('#tracking-results').append(found);

							for (var i = 0; i < jsonObj.length; i++) {
								var foundObj = jsonObj[i];

								//console.log(foundObj);

								if(foundObj.Errors != "") {
									jQuery('#notFound').fadeOut('500', function () {
										jQuery('#errorMessage').html(foundObj.Errors);
										jQuery('#errorShow').fadeIn('500');
									});
								} else
								{
									breakme: if(foundObj.OrderFound) {

										jQuery('#tracking-results').append(foundObj.ReturnHtml);
										//jQuery('#tracking-results').append("<hr>");
										jQuery('#tracking-results').show();

									} else{
										jQuery('#newgistics').hide();
										jQuery('#saddleCreek').hide();
										jQuery('#notFound').fadeIn('500');
									}
								}
							}

						}
					});
		}
	});

	function getUrlVars()
	{
		var vars = [], hash;
		var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
		for(var i = 0; i < hashes.length; i++)
		{
			hash = hashes[i].split('=');
			vars.push(hash[0]);
			vars[hash[0]] = hash[1];
		}
		return vars;
	}

	var qsOrder = getUrlVars()["orderid"];
	if(qsOrder != "" && qsOrder != null) {
		jQuery('#orderId').val(qsOrder);
		jQuery("#smartTrackingForm").trigger('submit');
	}

});
