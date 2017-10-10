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
			var str = jQuery('#returnTrackingForm').serialize();
	        
	        //console.log(str);

	        jQuery.ajax({
	            type        : 'POST',
	            url         : '/forms/rma/return-tracking-process.php',
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
						var jsonObj = jQuery.parseJSON('[' + data.GetReturnTrackingResult + ']');

						//console.log(jsonObj);

						if(jsonObj[0].Errors != "") {
							jQuery('#notFound').fadeOut('500', function () {
								jQuery('#errorMessage').html(jsonObj[0].Errors);
								jQuery('#errorShow').fadeIn('500');
							});
						} else
						{
							breakme: if(jsonObj[0].OrderFound) {

								if(jsonObj[0].NotAvailable) {
									jQuery('#newgistics').hide();
									jQuery('#saddleCreek').hide();
									jQuery('#notAvailable').fadeIn('500');
								} else {
									jQuery('#tracking-results').html(jsonObj[0].ReturnHtml);
									//window.location = "http://tracking.smartlabel.com/?trackingvalue=" + jsonObj[0].Tracking;
								}

							} else{
								jQuery('#newgistics').hide();
								jQuery('#saddleCreek').hide();
								jQuery('#notFound').fadeIn('500');
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
		jQuery("#returnTrackingForm").trigger('submit');
	}
	
});
