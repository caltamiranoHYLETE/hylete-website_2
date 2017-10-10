
// A $( document ).ready() block.
$( document ).ready(function() {

	$(function() {
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
		
		//"https://pbhservice.hylete.com/hyletePBHService.asmx/CheckPBHMemberEmail?" + $.param(requestData)
		$("#txtEmail").blur( function() {
			var requestData = { email: $("#txtEmail").val() };
			$.ajax({ url: "../lib/proxy.php",
				data: {requrl: "https://pbhservice.hylete.com/hyletePBHService.asmx/CheckPBHMemberEmail?" + $.param(requestData) },
		        dataType: "json",
		        success: function(data) {
		            if(data.CustomerId != "0") {
		            	$("#email_in_use").show();
		            	$('#form_submit').attr('disabled', 'disabled');
		            	
		            } else {
		            	$("#email_in_use").hide();
		            	$('#form_submit').removeAttr('disabled', 'disabled');
		            }
		        }
		    });
		});
	});
	
	$("#txtFirstName").on ({
		focus: function() { 
		    if($(this).val() == "First Name") {
		    	$(this).val(""); 
		    }
		},   
	    blur: function() { 
		    if($(this).val() == "") {
		    	$(this).val("First Name"); 
		    }
		}
	});
	
	$("#txtLastName").on ({
		focus: function() { 
		    if($(this).val() == "Last Name") {
		    	$(this).val(""); 
		    }
		},   
	    blur: function() { 
		    if($(this).val() == "") {
		    	$(this).val("Last Name"); 
		    }
		}
	});
	
	$("#txtEmail").on ({
		focus: function() { 
		    if($(this).val() == "Email Address") {
		    	$(this).val(""); 
		    }
		  },
	    blur: function() { 
		    if($(this).val() == "") {
		    	$(this).val("Email Address"); 
		    }
	    }
	});
	
	$("#defaultPassword").on ({
		focus: function() { 
		    if($(this).val() == "Password") {
		    	$(this).val("");
		    	$(this).get(0).type='password';
		    }
		  },
	    blur: function() { 
		    if($(this).val() == "") {
		    	$(this).val("Password"); 
		    }
	    }
	});
	
	$("#defaultRePassword").on ({
		focus: function() { 
		    if($(this).val() == "Confirm Password") {
		    	$(this).val(""); 
		    	$(this).get(0).type='password';
		    }
		  },
	    blur: function() { 
		    if($(this).val() == "") {
		    	$(this).val("Confirm Password"); 
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
	
	function validateConsumerEmailOld(email){
	    if (/^[\w\.%\+\-]+@[a-z0-9.-]+\.(con)$/i.test(email)) {
	        return false;
	    } else {
	    	return true;
	    }
	}
	
	$.validator.addMethod("notEqual", function(value, element, param) {
  		return this.optional(element) || value != param;
	}, "Please enter your name");
	
	$.validator.addMethod("hasAffiliate", function(value, element, param) {
  		return value != '';
  	}, "<span style='display:block;text-align:center'>Please select a gym, event or organization<br/> from the list</span>");

	$.validator.addMethod("validTLD", function(value, element, param) {
  		return validateConsumerEmail(value);
  	}, "<span style='display:block;text-align:center'>Please check your email address.</span>");

	$("#regForm").validate( {
		ignore: [],
		rules: {
			txtFirstName: {
				notEqual: "First Name"
			},
			txtLastName: {
				notEqual: "Last Name"
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
			},
			autocomplete2value: {
				hasAffiliate: ""
			},
		},
	 	submitHandler: function(form) {
			$('#sectionProcessing').show();
			form.submit();
		}
	});
	

});
