jQuery( document ).ready(function() {

	jQuery("#eventCodeForm").validate( {
		ignore: [],
		rules: {
			event_code: { required: true }
		},
		submitHandler: function(form) {

			jQuery('#errorShow').hide();
			jQuery('#sectionProcessing').show();

			var eventCode = jQuery("#event_code").val();
			var requestData = {eventCode: eventCode};
			jQuery.ajax({
				url: "/forms/lib/proxy.php",
				data: {requrl: urlBase + "ValidateEventCode?" + jQuery.param(requestData)},
				contentType: "application/json; charset=utf-8",
				dataType: "json",
				success: function (data) {

					jQuery('#sectionProcessing').hide();

					//console.log(data);

					if (data.errorMessage != "" && data.errorMessage != null) {
						jQuery("#errorMessage").text(data.errorMessage);
						jQuery("#errorShow").show();
					}
					else if (data.Id == 0 ) {
						jQuery("#errorMessage").text("An event with this code could not be found. Please make sure the code is correct and try again.");
						jQuery("#errorShow").show();
					} else if (data.Used) {
						jQuery("#errorMessage").text("Your code has already been redeemed. No more awards can be sent with this code.");
						jQuery("#errorShow").show();
					} else {
						jQuery("#eventCodeForm").fadeOut(function() {
							jQuery("#event_email").val(data.EventEmail);
							jQuery("#event_name").text(': '+ data.EventName);
							jQuery("#event_title").val(data.EventName);
							jQuery("#event_id").val(data.Id);
							jQuery("#winnerForm").fadeIn();
						});
					}
				}
			});
		}
	});

	jQuery.validator.addMethod('nomatch', function(value, element) {
		var bMatch = true;
		jQuery("input[name$='_email']").each(function(){
			if(jQuery(this).attr('name') != jQuery(element).attr('name')){
				//do we have trouble?
				if(jQuery(this).val() != "") {
					if(jQuery(this).val() == jQuery(element).val()) {
						bMatch = false;
					}
				}
			}
		});

		return bMatch;
	}, "You cannot send multiple gift cards to the same email or yourself.");

	jQuery.validator.addMethod('emailNeedsName', function(value, element) {
		var bMatch = true;
		if(jQuery(element).val() != "") {
			if(jQuery(element).prevAll("input[name$='_first_name']").val() == ""){
				bMatch = false;
			}

			if(jQuery(element).prevAll("input[name$='_last_name']").val() == ""){
				bMatch = false;
			}
		}
		return bMatch;
	}, "You need to have a first name and last name for each email you are sending");

	jQuery.validator.addMethod("validEmail", function(value, element)
	{
		if(value == '')
			return true;
		var temp1;
		temp1 = true;
		var ind = value.indexOf('@');
		var str2=value.substr(ind+1);
		var str3=str2.substr(0,str2.indexOf('.'));
		if(str3.lastIndexOf('-')==(str3.length-1)||(str3.indexOf('-')!=str3.lastIndexOf('-')))
			return false;
		var str1=value.substr(0,ind);
		if((str1.lastIndexOf('_')==(str1.length-1))||(str1.lastIndexOf('.')==(str1.length-1))||(str1.lastIndexOf('-')==(str1.length-1)))
			return false;
		//str = /(^[a-zA-Z0-9]+[\._-]{0,1})+([a-zA-Z0-9]+[_]{0,1})*@([a-zA-Z0-9]+[-]{0,1})+(\.[a-zA-Z0-9]+)*(\.[a-zA-Z]{2,3})$/;
		str =  /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i;
		temp1 = str.test(value);
		return temp1;
	}, "Please enter valid email.");

	jQuery("#winner_form").validate({
		ignore: "",
		rules: {
			winner_1_email: {
				nomatch : true,
				validEmail : true,
				emailNeedsName: true
			},winner_2_email: {
				nomatch : true,
				validEmail : true,
				emailNeedsName: true
			},winner_3_email: {
				nomatch : true,
				validEmail : true,
				emailNeedsName: true
			},winner_4_email: {
				nomatch : true,
				validEmail : true,
				emailNeedsName: true
			},winner_5_email: {
				nomatch : true,
				validEmail : true,
				emailNeedsName: true
			},winner_6_email: {
				nomatch : true,
				validEmail : true,
				emailNeedsName: true
			},winner_7_email: {
				nomatch : true,
				validEmail : true,
				emailNeedsName: true
			},winner_8_email: {
				nomatch : true,
				validEmail : true,
				emailNeedsName: true
			}
		},
		messages: {},
		debug: false,
		errorPlacement: function( label, element ) {
			if( element.attr( "name" ) === "acknowledge" ) {
				element.parent().append( label );
			} else {
				label.insertAfter( element );
			}
		},
		submitHandler: function()
		{
			//we need to loop through the product form and validate it
			jQuery("#myModal").modal();
			jQuery('#send_awards').prop('disabled', true);
			var dataString  = jQuery("#winner_form").serialize();
			var requestData = { eventForm: dataString };
			jQuery.ajax({
				url: "/forms/lib/proxy.php",
				data: {requrl: urlBase + "SendEventAwards?" + jQuery.param(requestData)},
				contentType: "application/json; charset=utf-8",
				dataType: "json",
				success: function (data) {

					jQuery('#sectionProcessing').hide();

					//console.log(data);

					if (data.CustomerErrorMessage != "" && data.CustomerErrorMessage != null) {
						jQuery("#errorMessage").text(data.CustomerErrorMessage);
						jQuery("#errorShow").show();
					} else {
						jQuery("#loadingImage").fadeOut();
						jQuery("#loadingMessage").html("Your awards have been queued to be sent. They should be sent out within the hour. If you have any issues, please contact events@hylete.com.<br><br><a href='/'>Return to HYLETE.com</a>");
					}
				}
			});


		}
	});

});
