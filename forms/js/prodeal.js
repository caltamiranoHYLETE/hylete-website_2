
function objectifyForm(formArray) {//serialize data function

	var returnArray = {};
	for (var i = 0; i < formArray.length; i++){
		returnArray[formArray[i]['name']] = formArray[i]['value'];
	}
	return returnArray;
}

jQuery( document ).ready(function() {

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

	jQuery("#defaultRePassword").on('blur', function() {
		jQuery(this).validate({
			rules: {
				defaultPassword: {
					equalTo: '#defaultRePassword'
				}
			},
			messages: {
				defaultPassword: "Please make sure your passwords match",
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
	}, "<span style='display:block;text-align:center'>Please check your email address.</span>");

	jQuery('#login-to-shop').on('click', function(event) {

		var data = { };
		data['email'] = jQuery('#txtEmail').val();
		data['password'] = jQuery('#defaultPassword').val();

		jQuery.redirectPost("/customer/account/login/", data);
	});

	jQuery('#login-customer').on('click', function(event) {

		var data = { };
		data['email'] = jQuery('#txtEmail').val();
		data['password'] = jQuery('#defaultPassword').val();

		jQuery.redirectPost("/customer/account/login/", data);
	});

	jQuery("#regForm").validate( {
		ignore:".ignore",
		rules: {
			txtFirstName: {
				notEqual: "first name"
			},
			txtLastName: {
				notEqual: "last name"
			},
			txtEmail: {
				required: true,
				email: true
			},
			defaultPassword: {
				required: true,
				minlength: 6,
				equalTo: '#defaultRePassword'
			}
		},
		submitHandler: function(form) {

			jQuery('#sectionProcessing').show();
			var requestData = objectifyForm(jQuery('#regForm').serializeArray());
			//console.log(requestData);
			jQuery.ajax({
				method      : 'POST',
				url         : restBase + 'prodeal/create',
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

					//console.log(data);

					if(data.success == 'false') {
						jQuery('#errorMessage').html(data.message);
						jQuery('#my_signup').html("ACCOUNT NOT CREATED!");
						jQuery('#errorShow').fadeIn('500');
					} else {
						var jsonObj = JSON.parse(data);

						//console.log(jsonObj);

						breakMe: {

							if(jsonObj.HasAccount) {
								jQuery('#my_signup').html("ACCOUNT ALREADY CREATED");
								jQuery('#registerShowForm').fadeOut('500', function() {
									jQuery('#accountShow').fadeIn('500');
								});
								break breakMe;
							}

							if(jsonObj.NewAccount) {
								jQuery('#my_signup').html("ACCOUNT CREATED!");
								jQuery('#registerShowForm').fadeOut('500', function() {
									jQuery('#newAccountShow').fadeIn('500');
								});
								break breakMe;
							}

							if(jsonObj.ShowError) {
								jQuery('#errorMessage').html(jsonObj.ErrorMessage);
								jQuery('#my_signup').html("ACCOUNT NOT CREATED!");
								jQuery('#account-form').fadeOut('500', function() {
									jQuery('#errorShow').fadeIn('500');
								});

								break breakMe;
							}
						}
					}
				});
		}
	});



});
