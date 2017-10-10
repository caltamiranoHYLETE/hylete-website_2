
// A $( document ).ready() block.
$( document ).ready(function() {

	var xhr;
	$("#autocomplete").autocomplete({
		delay: 200,
		minLength: 3,
		source: function( request, response ) {
			var regex = new RegExp(request.term, 'i');
			if(xhr){
				xhr.abort();
			}
			xhr = $.ajax({
				cache: false,
				url: "/forms/partners/customer_data.json",
				dataType: "json",
				success: function(data) {
					response($.map(data, function(item) {
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
			$(this).val(ui.item.label);
			$("#autocomplete2").html(ui.item.label);
			$("#affiliateName").val(ui.item.label);
			$("#autocomplete2value").val(ui.item.value);
		},
		select: function(event, ui) {
			// prevent autocomplete from updating the textbox
			event.preventDefault();
			// manually update the textbox and hidden field
			$(this).val(ui.item.label);
			$("#autocomplete2").html(ui.item.label);
			$("#affiliateName").val(ui.item.label);
			$("#autocomplete2value").val(ui.item.value);
		}
	});

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

	$('input[name=gymOwner]').click(function() {
		switch(this.value) {
			case 'true':
				$('#gymQuestions').show();
				$('#gymName').removeClass("ignore");
				$('#gymMembers').removeClass("ignore");
				$('#gymPhone').removeClass("ignore");
				break;
			case '':
				$('#gymQuestions').hide();
				$('#gymName').addClass("ignore");
				$('#gymMembers').addClass("ignore");
				$('#gymPhone').addClass("ignore");
				break;
		}
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
				url         : '/forms/partners/process.php',
				data        : str,
				dataType    : 'json',
				encode      : true
			})
				.done(function(data) {

					$('#sectionProcessing').hide();

					console.log(data);

					if(data.success == 'false') {
						$('#errorMessage').html(data.message);
						$('#my_signup').html("ACCOUNT NOT CREATED!");
						$('#errorShow').fadeIn('500');
					} else {
						var jsonObj = $.parseJSON('[' + data.CreatePartnerMemberResult + ']');

						console.log(jsonObj);

						breakMe: {
							if(jsonObj[0].ShowCode) {
								$('#couponCode').html(jsonObj[0].CouponCode);
								$('#my_signup').html("ACCOUNT CREATED!");
								$('#registerShowForm').fadeOut('500', function() {
									$('#couponShow').fadeIn('500');
								});
								break breakMe;
							}

							if(jsonObj[0].ShowAccount) {
								$('#my_signup').html("ACCOUNT ALREADY CREATED");
								$('#registerShowForm').fadeOut('500', function() {
									$('#accountShow').fadeIn('500');
								});
								break breakMe;
							}

							if(jsonObj[0].ShowLoginButton) {
								$('#my_signup').html("ACCOUNT CREATED!");
								$('#registerShowForm').fadeOut('500', function() {
									$('#loginButtonShow').fadeIn('500');
								});
								break breakMe;
							}

							if(jsonObj[0].ShowLoginLink) {
								$('#my_signup').html("ACCOUNT CREATED!");
								$('#registerShowForm').fadeOut('500', function() {
									$('#loginLinkShow').fadeIn('500');
								});
								break breakMe;
							}

							if(jsonObj[0].ShowError) {
								$('#errorMessage').html(jsonObj[0].ErrorMessage);
								$('#my_signup').html("ACCOUNT NOT CREATED!");
								$('#errorShow').fadeIn('500');
								break breakMe;
							}
						}
					}
				});
		}
	});



});
