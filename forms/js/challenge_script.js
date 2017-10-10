
// A jQuery( document ).ready() block.
jQuery( document ).ready(function() {

	var xhr;
	jQuery("#autocomplete").autocomplete({
		delay: 200,
		minLength: 3,
		source: function( request, response ) {
			var regex = new RegExp(request.term, 'i');
			if(xhr){
				xhr.abort();
			}
			xhr = jQuery.ajax({
				cache: false,
				url: "/forms/partners/customer_data.json",
				dataType: "json",
				success: function(data) {
					response(jQuery.map(data, function(item) {
						if(regex.test(item.label)){
							return {
								label: item.label,
								value: item.value
							};
						}
					}));
				}
			});
		},
		focus: function(event, ui) {
			// prevent autocomplete from updating the textbox
			event.preventDefault();
			// manually update the textbox
			jQuery(this).val(ui.item.label);
			jQuery("#autocomplete2").html(ui.item.label);
			jQuery("#affiliateName").val(ui.item.label);
			jQuery("#autocomplete2value").val(ui.item.value);
		},
		select: function(event, ui) {
			// prevent autocomplete from updating the textbox
			event.preventDefault();
			// manually update the textbox and hidden field
			jQuery(this).val(ui.item.label);
			jQuery("#autocomplete2").html(ui.item.label);
			jQuery("#affiliateName").val(ui.item.label);
			jQuery("#autocomplete2value").val(ui.item.value);
		}
	});

	jQuery("#txtFirstName").on ({
		focus: function() {
			if(jQuery(this).val() == "first name") {
				jQuery(this).val("");
			}
		},
		blur: function() {
			if(jQuery(this).val() == "") {
				jQuery(this).val("first name");
			}
		}
	});

	jQuery("#txtLastName").on ({
		focus: function() {
			if(jQuery(this).val() == "last name") {
				jQuery(this).val("");
			}
		},
		blur: function() {
			if(jQuery(this).val() == "") {
				jQuery(this).val("last name");
			}
		}
	});

	jQuery("#txtEmail").on ({
		focus: function() {
			if(jQuery(this).val() == "email address") {
				jQuery(this).val("");
			}
		},
		blur: function() {
			if(jQuery(this).val() == "") {
				jQuery(this).val("email address");
			}

			//https://pbhservice.hylete.com
			//http://localhost:60601
			if(jQuery(this).val() != "" || jQuery(this).val() != "email address") {
				var requestData = { email: jQuery("#txtEmail").val() };
				jQuery.ajax({ url: "/forms/lib/proxy.php",
					data: {requrl: "https://pbhservice.hylete.com/hyletePBHService.asmx/CheckChallengeMemberEmail?" + jQuery.param(requestData) },
					dataType: "json",
					success: function(data) {
						if(data.CustomerId == "0") {
							jQuery(".passwordSection").show();
						} else{
							jQuery(".passwordSection").hide();
						}
					}
				});
			}
		}
	});

	jQuery("#defaultPassword").on ({
		focus: function() {
			if(jQuery(this).val() == "password") {
				jQuery(this).val("");
				jQuery(this).get(0).type='password';
			}
		},
		blur: function() {
			if(jQuery(this).val() == "") {
				jQuery(this).val("password");
			}
		}
	});

	jQuery("#defaultRePassword").on ({
		focus: function() {
			if(jQuery(this).val() == "confirm password") {
				jQuery(this).val("");
				jQuery(this).get(0).type='password';
			}
		},
		blur: function() {
			if(jQuery(this).val() == "") {
				jQuery(this).val("confirm password");
			}
		}
	});

	jQuery("#defaultRePassword").blur( function() {
		jQuery(this).validate({
			rules: {
				defaultPassword: {
					equalTo: '#defaultRePassword'
				}
			},
			messages: {
				defaultPassword: "please make sure your passwords match",
			}
		});
	});

	function validateConsumerEmail(email){
		if (/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))jQuery/.test(email)) {
			return true;
		} else {
			return false;
		}
	}

	// jquery extend function
	jQuery.extend(
		{
			redirectPost: function(location, args)
			{
				var form = '';
				jQuery.each( args, function( key, value ) {
					form += '<input type="hidden" name="'+key+'" value="'+value+'">';
				});
				jQuery('<form action="'+location+'" method="POST">'+form+'</form>').appendTo('body').submit();
			}
		});

	jQuery.validator.addMethod("notEqual", function(value, element, param) {
		return this.optional(element) || value != param;
	}, "Please enter your name");

	jQuery.validator.addMethod("validTLD", function(value, element, param) {
		return validateConsumerEmail(value);
	}, "<span style='display:block;text-align:center'>please check your email address.</span>");

	jQuery('.login-to-shop').click(function(event) {

		var data = { };
		data['email'] = jQuery('#txtEmail').val();
		data['password'] = jQuery('#defaultPassword').val();

		jQuery.redirectPost("/customer/account/login/", data);
	});

	jQuery('#country').change(function() {
		if(jQuery(this).val() == "US") {
			jQuery('#state-block').show();
		} else{
			jQuery('#state-block').hide();
		}
	});

	jQuery("#regForm").validate( {
		ignore:":not(:visible)",
		rules: {
			txtFirstName: {
				notEqual: "first name"
			},
			txtLastName: {
				notEqual: "last name"
			},
			txtEmail: {
				required: true,
				//validTLD: "",
				email: true
			},
			defaultPassword: {
				required: true,
				minlength: 6,
				equalTo: '#defaultRePassword'
			}
		},
		errorElement: 'div',
		submitHandler: function(form) {

			jQuery('#sectionProcessing').show();

			var str = jQuery('#regForm').serialize();

			//console.log(str);

			jQuery.ajax({
				type        : 'POST',
				url         : '/forms/challenge/process.php',
				data        : str,
				dataType    : 'json',
				encode      : true
			})
				.done(function(data) {

					jQuery('#sectionProcessing').hide();

					//console.log(data);

					if(data.success == 'false') {
						jQuery('#errorMessage').html(data.message);
						jQuery('#my_signup').html("Account Not Created!");
						jQuery('#errorShow').fadeIn('500');
					} else {
						var jsonObj = jQuery.parseJSON('[' + data.CreateChallengeMemberResult + ']');

						//console.log(jsonObj);

						breakMe: {
							if(jsonObj[0].ForceCode) {
								jQuery('#couponCode').html(jsonObj[0].CouponCode);
								jQuery('#my_signup').html("Account Created!");
								jQuery('#registerShowForm').fadeOut('500', function() {
									jQuery('#forceShow').fadeIn('500');
								});
								break breakMe;
							}

							if(jsonObj[0].ShowCode) {
								//jQuery('#couponCode').html(jsonObj[0].CouponCode);
								jQuery('#my_signup').html("Account Created!");
								jQuery('#registerShowForm').fadeOut('500', function() {
									jQuery('#couponShow').fadeIn('500');
								});
								break breakMe;
							}

							if(jsonObj[0].ShowError) {
								jQuery('#errorMessage').html(jsonObj[0].ErrorMessage);
								jQuery('#my_signup').html("Account Not Created!");
								jQuery('#errorShow').fadeIn('500');
								break breakMe;
							}
						}
					}
				});
		}
	});
});
