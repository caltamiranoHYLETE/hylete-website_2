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

			var orderId = jQuery('#orderId').val();
			var str = jQuery('#returnForm').serialize();
	        
	        //console.log(str);

	        jQuery.ajax({
	            type        : 'POST',
	            url         : '/forms/rma/smart-return-process.php',
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
						var jsonObj = jQuery.parseJSON('[' + data.GetReturnOrderLocationResult + ']');
						console.log(jsonObj);
						if(jsonObj[0].Error != "") {
							jQuery('#notFound').fadeOut('500', function () {
								jQuery('#errorMessage').html(jsonObj[0].Error);
								jQuery('#errorShow').fadeIn('500');
							});
						} else
						{
							breakme:if(jsonObj[0].OrderFound) {
								if(jsonObj[0].PassedDate == true) {
									jQuery('#notFound').fadeOut('500', function() {
										jQuery('#dateError').fadeIn('500');
									});

									if(jsonObj[0].ReturnFound == true) {
										jQuery('#notFound').fadeOut('500', function () {
											//we set the links for the customer to choose
											jQuery("#returnLabel").attr("href", jsonObj[0].LabelUrl);
											jQuery('#labelFound').fadeIn('500');
										});
									}

									break breakme;
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

									var form = jQuery('<form action="/forms/returns/return-exchange.php" method="post">' +
											'<input type="hidden" name="creditMemoUsed" value="' + jsonObj[0].CreditMemoUsed + '" />' +
											'<input type="hidden" name="giftCardUsed" value="' + jsonObj[0].GiftCardUsed + '" />' +
										    '<input type="hidden" name="combinedOrder" value="' + jsonObj[0].CombinedOrder + '" />' +
											'<input type="hidden" name="orderId" value="' + jsonObj[0].OrderId + '" />' +
											'<input type="hidden" name="ignoreClearance" value="' + jsonObj[0].IgnoreClearance + '" />' +
											'<input type="hidden" name="isAdmin" value="' + jsonObj[0].IsAdmin + '" />' +
                                            '<input type="hidden" name="exchangeOnly" value="' + jsonObj[0].ExchangeOnly + '" />' +
                                            '<input type="hidden" name="simpleRefund" value="' + jsonObj[0].SimpleRefund + '" />' +
											'</form>');

									if(jsonObj[0].ReturnFound == true) {
										jQuery('#notFound').fadeOut('500', function () {
											//we set the links for the customer to choose
											jQuery("#returnLabel").attr("href", jsonObj[0].LabelUrl);
											jQuery('#labelFound').fadeIn('500');
											jQuery('#showNewReturn').fadeIn('500');
											jQuery("#newReturn").click(function(e) {
												e.preventDefault();
												jQuery('body').append(form);
												jQuery(form).submit();
											})
										});
									} else{
										jQuery('body').append(form);
										jQuery(form).submit();
									}
								}

								else if(jsonObj[0].Location == "SC") {
									window.location.href = "http://www.hylete.com/cs-return.html";
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
	
});
