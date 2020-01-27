jQuery( document ).ready(function() {

	jQuery("#returnTrackingForm").validate( {
		ignore: [],
		rules: {
			orderId: { required: true }
		},
	 	submitHandler: function(form) {

        	jQuery('#notFound').hide();
        	jQuery('#errorShow').hide();
			jQuery('#sectionProcessing').show();

			var orderId = jQuery('#orderId').val();
			var requestData = { orderId: orderId };
	        jQuery.ajax({
				method      : 'POST',
				url         : restBase + 'tracking/return',
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
						console.log(jsonObj);

						var html = "";
						var orderFound = false;
						var returnAvailable = false;
						for (var i = 0; i < jsonObj.length; i++) {
							var returnData = jsonObj[i];

							orderFound = true;

							html += returnData.ReturnHtml
							//console.log(html);
							if(!returnData.NotAvailable) {
								returnAvailable = true;
							}
						}

						if(orderFound) {
							if(!returnAvailable) {
								jQuery('#newgistics').hide();
								jQuery('#saddleCreek').hide();
								jQuery('#notAvailable').fadeIn('500');
							} else {
								jQuery('#tracking-results').html(html);
							}

						} else{
							jQuery('#newgistics').hide();
							jQuery('#saddleCreek').hide();
							jQuery('#notFound').fadeIn('500');
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
		jQuery("#returnTrackingForm").trigger('submit');
	}
	
});
