
// A $( document ).ready() block.
$( document ).ready(function() {

	$("#txtFirstName").on ({
		focus: function() {
			if($(this).val() == "first name") {
				$(this).val("");
			}
		},
		blur: function() {
			if($(this).val() == "") {
				$(this).val("first name");
			}
		}
	});

	$("#txtLastName").on ({
		focus: function() {
			if($(this).val() == "last name") {
				$(this).val("");
			}
		},
		blur: function() {
			if($(this).val() == "") {
				$(this).val("last name");
			}
		}
	});

	$("#txtEmail").on ({
		focus: function() {
			if($(this).val() == "email address") {
				$(this).val("");
			}
		},
		blur: function() {
			if($(this).val() == "") {
				$(this).val("email address");
			}
		}
	});

	$("#defaultPassword").on ({
		focus: function() {
			if($(this).val() == "password") {
				$(this).val("");
				$(this).get(0).type='password';
			}
		},
		blur: function() {
			if($(this).val() == "") {
				$(this).val("password");
			}
		}
	});

	$("#defaultRePassword").on ({
		focus: function() {
			if($(this).val() == "confirm password") {
				$(this).val("");
				$(this).get(0).type='password';
			}
		},
		blur: function() {
			if($(this).val() == "") {
				$(this).val("confirm password");
			}
		}
	});

	$("#defaultRePassword").blur( function() {
		$(this).validate({
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
		if (/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(email)) {
			return true;
		} else {
			return false;
		}
	}

	// jquery extend function
	$.extend(
		{
			redirectPost: function(location, args)
			{
				var form = '';
				$.each( args, function( key, value ) {
					form += '<input type="hidden" name="'+key+'" value="'+value+'">';
				});
				$('<form action="'+location+'" method="POST">'+form+'</form>').appendTo('body').submit();
			}
		});

	$.validator.addMethod("notEqual", function(value, element, param) {
		return this.optional(element) || value != param;
	}, "Please enter your name");

	$.validator.addMethod("validTLD", function(value, element, param) {
		return validateConsumerEmail(value);
	}, "<span style='display:block;text-align:center'>Please check your email address.</span>");

	$('#login-to-shop').click(function(event) {

		var data = { };
		data['email'] = $('#txtEmail').val();
		data['password'] = $('#defaultPassword').val();

		$.redirectPost("/customer/account/login/", data);
	});

	$('#login-customer').click(function(event) {

		var data = { };
		data['email'] = $('#txtEmail').val();
		data['password'] = $('#defaultPassword').val();

		$.redirectPost("/customer/account/login/", data);
	});

	$("#regForm").validate( {
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
				validTLD: "",
				email: true
			},
			defaultPassword: {
				required: true,
				minlength: 6,
				equalTo: '#defaultRePassword'
			}
		},
		submitHandler: function(form) {

			$('#sectionProcessing').show();

			var str = $('#regForm').serialize();

			//console.log(str);

			$.ajax({
				type        : 'POST',
				url         : '/forms/prodeal/process.php',
				data        : str,
				dataType    : 'json',
				encode      : true
			})
				.done(function(data) {

					$('#sectionProcessing').hide();

					//console.log(data);

					if(data.success == 'false') {
						$('#errorMessage').html(data.message);
						$('#my_signup').html("ACCOUNT NOT CREATED!");
						$('#errorShow').fadeIn('500');
					} else {
						var jsonObj = $.parseJSON('[' + data.CreateProDealMemberResult + ']');

						//console.log(jsonObj);

						breakMe: {

							if(jsonObj[0].HasAccount) {
								$('#my_signup').html("ACCOUNT ALREADY CREATED");
								$('#registerShowForm').fadeOut('500', function() {
									$('#accountShow').fadeIn('500');
								});
								break breakMe;
							}

							if(jsonObj[0].NewAccount) {
								$('#my_signup').html("ACCOUNT CREATED!");
								$('#registerShowForm').fadeOut('500', function() {
									$('#newAccountShow').fadeIn('500');
								});
								break breakMe;
							}

							if(jsonObj[0].ShowError) {
								$('#errorMessage').html(jsonObj[0].ErrorMessage);
								$('#my_signup').html("ACCOUNT NOT CREATED!");
								$('#account-form').fadeOut('500', function() {
									$('#errorShow').fadeIn('500');
								});

								break breakMe;
							}
						}
					}
				});
		}
	});



});
