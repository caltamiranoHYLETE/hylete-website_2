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
			var str = jQuery('#smartTrackingForm').serialize();

			//console.log(str);

			jQuery.ajax({
						type        : 'POST',
						url         : '/forms/tracking/smart-tracking-process.php',
						data        : str,
						dataType    : 'json',
						encode      : true
					})
					.done(function(data) {

						jQuery('#sectionProcessing').hide();

						if(data.success == 'false') {
							jQuery('#errorMessage').html(data.message);
							jQuery('#errorShow').fadeIn('500');
						} else {
							var jsonObj = jQuery.parseJSON('[' + data.GetOrderTrackingResult + ']');
							jQuery('#tracking-results').empty();
							//console.log(jsonObj[0]);

							var shipment = "shipment";
							if(jsonObj[0].length > 1) {
								shipment = "shipments";
							}
							var found = "<h2>We found " + jsonObj[0].length + " matching " + shipment + "</h2>"

							jQuery('#tracking-results').append(found);

							for (var i = 0; i < jsonObj[0].length; i++) {
								var foundObj = jsonObj[0][i];

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
