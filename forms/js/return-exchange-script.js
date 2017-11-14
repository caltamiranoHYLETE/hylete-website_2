/**
 * Created by Skennerly on 12/23/2015.
 */
jQuery(document).ready(function(){

    var orderId = jQuery("#orderId").val();
    var ignoreClearance = jQuery("#ignoreClearance").val();
    var isAdmin = jQuery("#isAdmin").val();

    jQuery('#step1').tooltip({title: "You can return products for a cash refund (credit card or PayPal), store credit, or exchange the same product for a different size. ", placement: "bottom", animation: true});
    jQuery('#refundTooltip').tooltip({title: "Select cash refund if you are returning products for a refund to your original payment method (credit card, PayPal). After your return is processed, allow up to a week for the funds to show up in your account.", placement: "right", animation: true});
    jQuery('#creditMemoTooltip').tooltip({title: "Recommended for the fastest way to get your refund. After your return is processed, a credit is added to your account to purchase anything in the store. This is also perfect for when you want to exchange for a different product or style.", placement: "right", animation: true});
    jQuery('#exchangeTooltip').tooltip({title: "Select exchange if all the items need an exchange for size. After your return is processed, a new order will be sent to you that same day.", placement: "right", animation: true});
    jQuery('#productTooltip').tooltip({title: "Some items cannot be exchanged for size and can only be refunded. Clearance items cannot be refunded or exchanged.", placement: "top", animation: true});

    jQuery('#refundTooltip, #creditMemoTooltip, #exchangeTooltip').hover(function(){
        jQuery('#choice1').tooltip("destroy");
    });

    if(orderId != "") {
        var requestData = { orderId: orderId, ignoreClearance: ignoreClearance, isAdmin: isAdmin };
        jQuery.ajax({ url: "../lib/proxy.php",
            data: {requrl: urlBase + "GetReturnProductTable?" + jQuery.param(requestData) },
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            success: function(data) {
                //console.log(data);
                //var jsonObj = jQuery.parseJSON('[' + data + ']');
                if(data.ErrorMessage != "" && data.ErrorMessage != null) {
                    jQuery("#loadingMessage").text(data.ErrorMessage);
                    jQuery("#loadingImage").hide();
                } else{
                    jQuery("#product_table_area").html(data.Table);
                    jQuery("#firstName").val(data.FirstName);
                    jQuery("#lastName").val(data.LastName);
                    jQuery("#email").val(data.Email);
                    jQuery("#nonreturnable_email").val(data.Email);
                    jQuery("#address1").val(data.Address1);
                    jQuery("#address2").val(data.Address2);
                    jQuery("#city").val(data.City);
                    jQuery("#state").val(data.State);
                    jQuery("#postalCode").val(data.PostalCode);

                    //Amazon, GOVX and BodyBuilding get a simple refund
                    if(jQuery("#simpleRefund").val() == "true") {
                        jQuery("#creditMemoTooltip").css("display","none");
                        jQuery("#memo_choice").css("display","none");
                        jQuery("#refundTooltip").html("&nbsp;Refund");
                        jQuery("#refund_choice").prev('span.spacer').remove();
                        jQuery("#refund_choice").prop('checked', true);
                        jQuery('#refundTooltip').tooltip("destroy").tooltip({title: "Select refund if you are returning products for a refund to your original payment method (credit card, PayPal, etc). If your purchase was not at HYLETE.com, you may have to wait additional time for the 3rd party to process your refund.", placement: "right", animation: true});
                    } else{
                        if(jQuery("#exchangeOnly").val() == "true") {
                            jQuery("#memo_choice").prop('disabled', true);
                            jQuery("#refund_choice").prop('disabled', true);
                            jQuery("#exchange_choice").prop('checked', true);

                            jQuery("#refundTooltip").removeClass("choice_text").addClass("choice_text_disabled").tooltip("destroy").tooltip({title: "Cash refund is not available for your order. Please select exchange instead.", placement: "top", animation: true});
                            jQuery("#creditMemoTooltip").removeClass("choice_text").addClass("choice_text_disabled").tooltip("destroy").tooltip({title: "Credit memo is not available for your order. Please select exchange instead.", placement: "top", animation: true});
                        } else{
                            if(jQuery("#creditMemoUsed").val() == "true" || jQuery("#giftCardUsed").val() == "true") {
                                jQuery("#refund_choice").prop('disabled', true);
                                jQuery("#memo_choice").prop('checked', true);
                                jQuery("#refundTooltip").removeClass("choice_text").addClass("choice_text_disabled").tooltip("destroy").tooltip({title: "Cash refund is not available for your order. Please select refund for store credit or exchange instead.", placement: "top", animation: true});
                            }
                        }
                    }

                    jQuery('a.nonreturnable_contact').click(function() {
                        jQuery('#modal-message').hide();
                        jQuery('#print-label-area').hide();
                        jQuery('#nonreturnable_contact').show();

                        jQuery('#choice1').tooltip("destroy");

                        jQuery("#myModal").modal();
                    });

                    jQuery('.select_exchange:disabled').wrap(function() {
                        return '<div class="exchangeTooltip" />';
                    });

                    jQuery('.exchangeTooltip').tooltip({title: "If you want to exchange for size, select the \"exchange for size\" option above.", placement: "left", animation: true});
                    jQuery('span.plus:first').tooltip({title: "Click the + button to add items to your return.", placement: "left", animation: true});

                    jQuery('#choice1').tooltip({title: "Start by selecting which type of return you would like.", placement:"right", animation:true});

                    jQuery("#loading_area").fadeOut(600, function() {
                        jQuery("#content_area").fadeIn();
                    });

                    setTimeout(showReturnTooltip, 2000);
                    setTimeout(showPlusTooltip, 3000);
                }
            }
        });
    }

    var showPlusTooltip = function() {
        jQuery('span.plus:first').tooltip("show");
    };

    var showReturnTooltip = function() {
        jQuery('#choice1').tooltip("show");
    };

    jQuery("#nonreturnable_contactForm").validate ({
        rules: {
            nonreturnable_email: { required: true}
        },
        submitHandler: function() {

            var email = jQuery("#nonreturnable_email").val();
            var comments = jQuery("#nonreturnable_comments").val();
            var firstName = jQuery("#firstName").val();
            var lastName = jQuery("#lastName").val();

            var requestData = { orderId: orderId, email: email, comments: comments, firstName: firstName, lastName: lastName };

            jQuery.ajax({
                url: "../lib/proxy.php",
                data: {requrl: urlBase + "QueueReturnSupportEmail?" + jQuery.param(requestData)},
                contentType: "application/json; charset=utf-8",
                dataType: "json",
                success: function (data) {
                    //if we don't have any errors, we will show the customer a return label
                    //console.log(data);
                    if (data.success == 'false') {
                        jQuery('#nonreturnable_contactForm').html("<h1>Message Failed!</h1><p>There was a problem creating your ticket. An message has been sent to technical support to resolve the issue.</p>");
                    } else {
                        jQuery('#nonreturnable_contactForm').html("<h1>Message Sent!</h1><p>We appreciate your patience while our brand reps work on responding to you as soon as possible. Thanks for your support.</p>");
                    }
                }
            })
        }
    });

    jQuery('#exchange_other_choice').on('click', function() {
        jQuery(".select_exchange").fadeOut(function() {
            jQuery("#exchange_title").text("exchange for sku");
            jQuery(".input_exchange").fadeIn();
            jQuery('.small_input').each(function() {
                var qty = parseInt(jQuery(this).val());
                if(qty > 0){
                    jQuery(this).parent().nextAll().find('.select_reason').prop('disabled', false);
                    jQuery(this).parent().nextAll().find('.input_exchange').prop('disabled', false);
                    jQuery(this).parent().nextAll().find('.select_exchange').prop('disabled', true);
                }else{
                    jQuery(this).parent().nextAll().find('.input_exchange').prop('disabled', true);
                    jQuery(this).parent().nextAll().find('.select_exchange').prop('disabled', true);
                }
            });

            jQuery('.plus_disabled').each(function() {
                jQuery(this).removeClass("plus_disabled").addClass("plus");
            });

            jQuery('.minus_disabled').each(function() {
                jQuery(this).removeClass("minus_disabled").addClass("minus");
            });
        });

        jQuery('.exchangeTooltip').tooltip("destroy");
    });

    jQuery('#exchange_choice').on('click', function() {
        jQuery('#choice1').tooltip("destroy");
        jQuery("#exchange_title").text("exchange for size");
        jQuery(".input_exchange").fadeOut(function() {
            jQuery(".input_exchange").prop("disabled", "disabled");
            jQuery(".select_exchange").fadeIn();
        });

        jQuery('.product_title').each(function() {
            if(jQuery(this).attr("exchangable") == "false") {
                jQuery(this).removeClass("product_title").addClass("product_title_disabled");
            }
        });

        jQuery('.plus').each(function() {
            var qtyField = jQuery(this).prev('input');
            if(qtyField.attr("exchangable") == "false") {
                jQuery(this).removeClass("plus").addClass("plus_disabled");
            }
        });

        jQuery('.minus').each(function() {
            var qtyField = jQuery(this).next('input');
            if(qtyField.attr("exchangable") == "false") {
                jQuery(this).removeClass("minus").addClass("minus_disabled");
            }
        });

        jQuery('.small_input').each(function() {
            if(jQuery(this).attr("exchangable") == "false") {
                jQuery(this).val("0");
                jQuery(this).parent().nextAll().find('.select_reason').prop('disabled', true);
            } else{
                var qty = parseInt(jQuery(this).val());
                if(qty > 0){
                    jQuery(this).parent().nextAll().find('.select_exchange').prop('disabled', false);
                    jQuery(this).parent().nextAll().find('.select_reason').val('Size Issue');
                    jQuery(this).parent().nextAll().find('.select_reason option').not(':selected').attr('disabled', 'disabled')
                }
            }
        });

        jQuery(".exchangeTooltip.tooltip").each(function(){
            jQuery(this).hide();
        });

        jQuery('.exchangeTooltip').tooltip("destroy");

    });

    jQuery('#refund_choice, #memo_choice').change(function() {

        jQuery('#choice1').tooltip("destroy");

        jQuery(".input_exchange").hide(function() {
            jQuery(".select_exchange").show();
        });

        jQuery('.product_title_disabled').each(function() {
            jQuery(this).removeClass("product_title_disabled").addClass("product_title");
        });

        jQuery('.plus_disabled').each(function() {
            jQuery(this).removeClass("plus_disabled").addClass("plus");
        });

        jQuery('.minus_disabled').each(function() {
            jQuery(this).removeClass("minus_disabled").addClass("minus");
        });

        jQuery('.select_exchange').prop('disabled', 'disabled');
        jQuery('.small_input').each(function() {
            var qty = parseInt(jQuery(this).val());
            if(qty > 0){
                jQuery(this).parent().nextAll().find('.select_reason option').not(':selected').attr('disabled', false)
            }
        })

        jQuery(".exchangeTooltip.tooltip").each(function(){
            jQuery(this).hide();
        });

    });

    jQuery('#product_table_area').on('click', '.plus', function(e) {
        e.preventDefault();
        var max = parseInt(jQuery(this).attr('max'));
        var qtyField = jQuery(this).prev('input');
        var qty = parseInt(qtyField.val());

        //hide any tooltips
        jQuery('span.plus:first').tooltip("destroy");

        if(qty < max) {
            qtyField.val(qty+1);
        }

        if(parseInt(qtyField.val()) > 0){
            jQuery(this).parent().nextAll().find('.select_reason').prop('disabled', false);

            if(jQuery('#exchange_choice').is(':checked')) {
                jQuery(this).parent().nextAll().find('.select_exchange').prop('disabled', false);
                jQuery(this).parent().nextAll().find('.select_reason').val('Size Issue');
                jQuery(this).parent().nextAll().find('.select_reason option').not(':selected').attr('disabled', 'disabled')
            }

            if(jQuery('#exchange_other_choice').is(':checked')) {
                jQuery(this).parent().nextAll().find('.input_exchange').prop('disabled', false);
            }

        } else{
            jQuery(this).parent().nextAll().find('.select_reason').prop('disabled', 'disabled');
            jQuery(this).parent().nextAll().find('.select_exchange').prop('disabled', 'disabled');
        }
    });

    jQuery('#product_table_area').on('click', '.minus', function(e) {
        e.preventDefault();
        var qtyField = jQuery(this).next('input');
        var qty = parseInt(qtyField.val());

        if(qty != 0) {
            qtyField.val(qty-1);
        }

        if(qtyField.val() == 0){
            jQuery(this).parent().nextAll().find('.select_reason').prop('disabled', 'disabled');
            jQuery(this).parent().nextAll().find('.select_exchange').prop('disabled', 'disabled');
        }
    });

    jQuery("#addressForm").validate({
        ignore: "",
        rules: {
            acknowledge: {
                required: true
            }
        },
        messages: {
            acknowledge: {
                required: 'You must select the acknowledgement to proceed.'
            }
        },
        debug: true,
        errorLabelContainer: "#addressErrorNotice",
        wrapper: "li",
        submitHandler: function()
        {
            var errorMessage = "";
            var methodErrorMessage = "";
            var qtyReturned = 0;
            var canSubmit = true;

            //we need to make sure they have selected one of the return methods
            jQuery('#methodErrorNotice').hide();

            if (jQuery('input:radio:checked').length == 0) {
                methodErrorMessage = "\<li\>Please make sure you select your preferred return method.\</li\>";
            }

            //we need to loop through the product form and validate it
            jQuery('#productErrorNotice').hide();

            jQuery('.small_input').each(function() {
                var qty = parseInt(jQuery(this).val());

                if(qty > 0){
                    qtyReturned += qty;
                    var reason = jQuery(this).parent().nextAll().find('.select_reason').val();

                    if(reason == "") {
                        errorMessage += "\<li\>Please make sure you have selected a return reason for all items being returned or exchanged.\</li\>";
                    }

                    if(jQuery('#exchange_choice').is(':checked')) {
                        var exchange = jQuery(this).parent().nextAll().find('.select_exchange').val();
                        if(exchange == "") {
                            errorMessage += "\<li\>Please make sure you have selected a size for the item(s) you are exchanging.\</li\>";
                        }
                    }

                    if(jQuery('#exchange_other_choice').is(':checked')) {
                        var exchange = jQuery(this).parent().nextAll().find('.input_exchange').val();
                        if(exchange == "") {
                            errorMessage += "\<li\>Please make sure you have entered a sku for the item(s) you are exchanging.\</li\>";
                        }
                    }
                }
            })

            if(qtyReturned == 0) {
                errorMessage += "\<li\>You need to set a quantity for the items you are returning. Use the + under qty to return\</li\>";
            }

            if(errorMessage != "" || methodErrorMessage != "") {
                canSubmit = false;

                if(errorMessage != "") {
                    jQuery('#productErrorNotice').show();
                    jQuery('#productErrorNotice').html(errorMessage);
                }

                if(methodErrorMessage != "") {
                    jQuery('#methodErrorNotice').show();
                    jQuery('#methodErrorNotice').html(methodErrorMessage);
                    jQuery('html, body').animate({ scrollTop: jQuery('#methodErrorNotice').offset().top }, 400);
                } else{
                    jQuery('html, body').animate({ scrollTop: jQuery('#productErrorNotice').offset().top }, 400);
                }

            }



            if(canSubmit) {
                var dataString  = jQuery("#productForm, #addressForm, #choiceForm").serialize();

                //console.log(dataString);
                jQuery("#myModal").modal();

                jQuery.ajax({
                        type        : 'POST',
                        url         : '/forms/rma/return-exchange-process.php',
                        data        : dataString ,
                        dataType    : 'json',
                        encode      : true
                    })
                    .done(function(data) {
                        //if we don't have any errors, we will show the customer a return label
                        //console.log(data);
                        if(data.success == 'false') {
                            jQuery('#print-label-area').hide();
                            jQuery('#modal-message').html(data.message);
                        } else {
                            var jsonObj = jQuery.parseJSON('[' + data.CreateReturnExchangeResult + ']');
                            if(jsonObj[0].Success == false) {
                                jQuery('#print-label-area').hide();
                                jQuery('#modal-message').html("<h2>There was an error processing your return!</h2><h4>" + jsonObj[0].ErrorMessage + "</h4>");
                            } else{

                                jQuery('#modal-message').html("<h4>Your return has been submitted successfully!</h4>");
                                jQuery('#print-label-area').show();
                                jQuery("a.text-link").prop("href", jsonObj[0].LabelUrl);
                                jQuery("a.img-link").prop("href",jsonObj[0].LabelUrl);
                                jQuery("#email-link").text("An email with your label and more information has been sent to " + jsonObj[0].Email);
                            }
                        }

                        jQuery("#myModal").modal();
                    });
            }
        }
    })

    /*jQuery('#email-link').click( function() {
        jQuery(".email-loader").show();

        //https://pbhservice.hylete.com
        var requestData = { email: $("#email").val() , labelUrl: jQuery("a.text-link").prop("href"), customerName: jQuery("#firstName").val() + " " + jQuery("#lastName").val() };
        $.ajax({ url: "../lib/proxy.php",
            data: {requrl: "https://pbhservice.hylete.com/hyletePBHService.asmx/SendLabelEmail?" + $.param(requestData) },
            //data: {requrl: "http://localhost:60601/hyletePBHService.asmx/SendLabelEmail?" + $.param(requestData) },
            dataType: "json",
            success: function(data) {
                if(data.ErrorMessage == "") {
                    jQuery(".email-loader").hide();
                    jQuery(".email-sent").show();
                } else {
                    jQuery(".email-notice").text(data.ErrorMessage);
                    jQuery(".email-loader").hide();
                    jQuery(".email-sent").show();
                }
            }
        });
    });*/

    jQuery("#myModal img").bind('contextmenu', function(e) {
        return false;
    });
});
