
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
			select: function(event, ui) {
				// prevent autocomplete from updating the textbox
				event.preventDefault();
				// manually update the textbox and hidden field
				$(this).val(ui.item.label);
				$("#autocomplete2").html(ui.item.label);
			}
		});

	$("#regForm").validate( {
		ignore: [],
		rules: {
			txtEmail: {
				required: true,
				email: true
			}
		},
	 	submitHandler: function(form) {
			$('#sectionProcessing').show();
			getAccountInfo();
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
	
	
	//"https://pbhservice.hylete.com/hyletePBHService.asmx/CheckAccountInfo?" + $.param(requestData)
	function getAccountInfo() {
		var requestData = { email: $("#txtEmail").val() };
		$.ajax({ url: "proxy.php",
			data: {requrl: "https://pbhservice.hylete.com/hyletePBHService.asmx/CheckAccountInfo?" + $.param(requestData) },
	        dataType: "json",
	        success: function(data) {
	            if(data.CustomerId == "0") {
	            	$('#sectionProcessing').hide();
	            	$("#email_not_found").show();
	            } else {
	            	$('#sectionProcessing').hide();
	            	$("#email_not_found").hide();
	            	$("#current_children").text(data.ChildCount);
	            	$("#account_name").text(data.AccountName);
	            	$("#account_name1").text(data.AccountName);
	            	$("#account_name2").text(data.AccountName);
	            	$("#child_info").show();
	            }
	        }
	    });
	 }
	 
});
