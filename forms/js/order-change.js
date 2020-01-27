jQuery( document ).ready(function() {

    jQuery("input[name='change-order']").on('change', function(){

        jQuery('#submit-area').hide();

        if(jQuery(this).val() === "other" || jQuery(this).val() === "item") {

            jQuery('#comments-label').html("Please describe what you need to change about your order below and we'll help you out.");
            jQuery('#address-area').hide();
            jQuery('#comment-area').show();

        } else if(jQuery(this).val() === "address") {
            jQuery('#comments-label').html("Please add the new shipping address below and we'll help you out.");
            jQuery('#address-area').hide();
            jQuery('#comment-area').show();
        } else{
            jQuery('#comment-area').hide();
            jQuery('#address-area').hide();
        }

        jQuery('#submit-area').show();
    });

    jQuery("#change-order-form").validate( {
        ignore: ":not(:visible)",
        rules: {
            comments: { required: true}
        },
        submitHandler: function() {

            var fromVal =jQuery("input[name='change-order']:checked", "#change-order-form").val();
            if(fromVal === "cancel") {
                if(confirm("Are you sure you want to CANCEL the order? There may be a delay in refunding the purchase.")){

                    jQuery('#changeForm').hide();
                    jQuery('#submit-area').hide();
                    jQuery('#resultShow').hide();
                    jQuery('#sectionProcessing').show();

                    var orderId = jQuery('#order-change-orderId').val();
                    var requestData = { orderId: orderId };
                    jQuery.ajax({
                        method        : 'POST',
                        url         : restBase + 'change-request/cancel-order',
                        beforeSend: function(request) {
                            request.setRequestHeader("APIKey", ApiKey);
                        },
                        data        : JSON.stringify(requestData),
                        dataType    : 'json',
                        contentType: "application/json",
                        encode      : true,
                        timeout: 10000,
                        success: function(data) {
                            //console.log(data);
                            var jsonObj = JSON.parse(data);
                            jQuery('#sectionProcessing').hide();

                            var html = "";
                            if(jsonObj.Success) {
                                html = "<ul><li class='can-return'>Your order has been canceled. Please Note: There may be a delay in refunding your purchase.</li></ul>";
                            }
                            else if(jsonObj.Success === "true") {
                                html = "<ul><li class='can-return'>Your order has been canceled. Please Note: There may be a delay in refunding your purchase.</li></ul>";
                            } else {
                                html = "<ul><li class='passed-date'>There was problem processing your request. The message has been sent to customer support to resolve the issue.</li></ul>";
                            }

                            jQuery('#resultShow').html(html).fadeIn('500');
                        }
                    });
                }
            }

            if(fromVal === "other" || fromVal === "item" || fromVal === "address") {
                if(confirm("This is going to place your order on HOLD until we process your request. Are you sure you want to do this?")){
                    jQuery('#changeForm').hide();
                    jQuery('#submit-area').hide();
                    jQuery('#resultShow').hide();
                    jQuery('#sectionProcessing').show();

                    var orderId = jQuery('#order-change-orderId').val();
                    var message = jQuery('#comments').val();
                    var requestData = { orderId: orderId, message: message};
                    jQuery.ajax({
                        method        : 'POST',
                        url         : restBase + 'change-request/hold-order',
                        beforeSend: function(request) {
                            request.setRequestHeader("APIKey", ApiKey);
                        },
                        data        : JSON.stringify(requestData),
                        dataType    : 'json',
                        contentType: "application/json",
                        encode      : true,
                        timeout: 10000,
                        success: function(data) {
                            //console.log(data);
                            var jsonObj = JSON.parse(data);
                            jQuery('#sectionProcessing').hide();

                            var html = "";
                            if(jsonObj.Success) {
                                html = "<ul><li class='can-return'>Your order has been put on hold and customer service will contact you soon about your request.</li></ul>";
                            } else if(jsonObj.Success === "true") {
                                html = "<ul><li class='can-return'>Your order has been put on hold and customer service will contact you soon about your request.</li></ul>";
                            } else{
                                html = "<ul><li class='passed-date'>There was problem processing your request. The message has been sent to customer support to resolve the issue.</li></ul>";
                            }

                            jQuery('#resultShow').html(html).fadeIn('500');
                        }
                    });
                }
            }

        }
        }
    );

    jQuery("#returnForm").validate( {
        ignore: [],
        rules: {
            orderId: { required: true },
            email: { required: true }
        },
        submitHandler: function(form) {

        	jQuery('#errorShow').hide();
			jQuery('#sectionProcessing').show();
            jQuery('#resultShow').empty().hide();

			var orderId = jQuery('#order-change-orderId').val();
            var email = jQuery('#order-change-email').val();
            var requestData = { orderId: orderId, email: email };

            jQuery.ajax({
                method        : 'POST',
                url         : restBase + 'change-request/order',
                beforeSend: function(request) {
                    request.setRequestHeader("APIKey", ApiKey);
                },
                data        : JSON.stringify(requestData),
                dataType    : 'json',
                contentType: "application/json",
                encode      : true,
                timeout: 10000,
                success: function(data) {
                    //console.log(data);
                    var jsonObj = JSON.parse(data);
                    jQuery('#sectionProcessing').hide();

                    var html = "";

                    if(jsonObj.Error !== null && jsonObj.Error !== "") {
                        html += "<ul><li class='passed-date'>The email you have entered does not match the order number entered. Please try again.</li></ul>";

                        jQuery('#resultShow').html(html).fadeIn('500');

                        return;
                    }

                    if (jsonObj.OrderFound === true) {

                        if (jsonObj.CanReturn === true) {
                            html += "<ul><li class='can-return'>We found your order! Please select from the options below to make your change. Please note: this is time sensitive as we try and process orders as fast as we can. </li></ul>";

                            jQuery('#changeForm').fadeIn('500');

                        } else{
                            html += "<ul><li class='passed-date'>It looks like this order has been processed for shipment or has already been shipped. Please contact us at <a href='mailto:customerservice@HYLETE.com'>customerservice@HYLETE.com</a>, or the live chat (if available), to help you with your order.</li></ul>";
                        }
                    } else{
                        html += "<ul><li class='passed-date'>We couldn't find an order with the number you provided. Please try again. If you are still having a problem, please contact us at <a href='mailto:customerservice@HYLETE.com'>customerservice@HYLETE.com</a>, or the live chat (if available), to help you with your order.</li></ul>";
                    }

                    jQuery('#resultShow').html(html).fadeIn('500');
                }
            });
		}
	});
	
});
