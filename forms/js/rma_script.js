
// A jQuery( document ).ready() block.
jQuery( document ).ready(function() {

	jQuery("#returnForm").validate( {
		ignore: [],
		rules: {
			orderId: { required: true }
		},
	 	submitHandler: function(form) {
			
			jQuery('#international').hide();
        	jQuery('#newgistics').hide();
        	jQuery('#saddleCreek').hide();
        	jQuery('#notFound').hide();
        	jQuery('#dateError').hide();
        	jQuery('#errorShow').hide();
			jQuery('#sectionProcessing').show();

	        var str = jQuery('#returnForm').serialize();
	        
	        //console.log(str);

	        jQuery.ajax({
	            type        : 'POST',
	            url         : '/forms/rma/process.php',
	            data        : str,
	            dataType    : 'json',
	            encode      : true
	        })
	            .done(function(data) {
					
					jQuery('#sectionProcessing').hide();
					
					//console.log(data);
					
					if(data.success == 'false') {
						jQuery('#errorMessage').html(data.message);
	                	jQuery('#errorShow').fadeIn('500');
					} else {
						var jsonObj = jQuery.parseJSON('[' + data.GetReturnOrderLocationResult + ']');
	                
		                //console.log(jsonObj);
		                
		                breakable: if(jsonObj[0].OrderFound) {

		                	if(jsonObj[0].PassedDate == true) {
			                	jQuery('#notFound').fadeOut('500', function() {
								    jQuery('#dateError').fadeIn('500');
								});

								break breakable;
		                	}

							if(jsonObj[0].NotEligible == true) {
								jQuery('#notFound').fadeOut('500', function() {
									jQuery('#notEligible').fadeIn('500');
								});
							}
		                	
		                	else if(jsonObj[0].International == true) {
			                	window.location.href = "http://www.hylete.com/international-returns.html";
		                	}
		                	
		                	else if(jsonObj[0].Location == "NG") {
			                	var apiKey = '1680b6d0e99f43649c03f34919cd5b91';
                    			var NgsMid = '2262';
                    			var orderID = jQuery('#orderId').val();
                    			
                    			if(jsonObj[0].Extension != "") {
                    				orderID = orderID + jsonObj[0].Extension;
                    			}

                    			jQuery('#SLORequest' ).val('<?xml version="1.0" encoding="utf-16"?><ReturnCenterXmlFormData xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="Newgistics.ReturnCenter.3.0"><FormData><OrderSelection><FormID>21</FormID><NGSMerchantID>' + NgsMid + '</NGSMerchantID><SecurityIdentity><IdentityType>Consumer</IdentityType></SecurityIdentity><Order><Identifier Qualifier="AtLastAPIKey" Value="' + apiKey + '" Default="true" xmlns="urn:Newgistics.Document.2.0" /><OrderID Qualifier="OrderID" Value="' + orderID.trim() + '" Default="true" xmlns="urn:Newgistics.Document.2.0" /></Order></OrderSelection></FormData></ReturnCenterXmlFormData>');

								jQuery('#newgisticsForm' ).submit();
		                	}
		                	
		                	else if(jsonObj[0].Location == "SC") {
			                	window.location.href = "http://www.hylete.com/returns-and-exchanges.html";
		                	}
		                	
		                	else if(jsonObj[0].Error != "") {
			                	jQuery('#notFound').fadeOut('500', function() {
								    jQuery('#errorMessage').html(jsonObj[0].Error);
								    jQuery('#errorShow').fadeIn('500');
								});
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
	
});
