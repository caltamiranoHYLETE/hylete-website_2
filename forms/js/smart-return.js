function displayItems(jsonObj) {
    var showBackup;
    var html = "";
    for (var u in jsonObj.ReturnItems) {
        showBackup = false;
        var item = jsonObj.ReturnItems[u];
        if (typeof item.Image !== "undefined") {
            html += "<div class=\"return-item\">" + item.Image + " " + item.Qty + " X " + item.Sku + " - " + item.Description + "</div>";
        }
    }

    if (showBackup) {
        for (var u in jsonObj.NwgOrderStatus.Items) {
            var item = jsonObj.NwgOrderStatus.Items[u];
            if (typeof item.Description !== "undefined") {
                html += "<ul><li>" + item.Qty + " : " + item.Description + "</li></ul>";
            }
        }
    }

    return html;
}

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
            jQuery('#resultShow').empty().hide();

			var orderId = jQuery('#orderId').val();
			var str = jQuery('#returnForm').serialize();
            var html = "";

	        jQuery.ajax({
	            type        : 'POST',
	            url         : '/forms/rma/smart-return-process.php',
	            data        : str,
	            dataType    : 'json',
	            encode      : true,
                timeout: 60000
	        })
	            .success(function(data) {

					jQuery('#sectionProcessing').hide();

					if(data.success == 'false') {
						jQuery('#errorMessage').html(data.message);
	                	jQuery('#errorShow').fadeIn('500');
					} else {
                        var jsonObj = jQuery.parseJSON(data.GetReturnOrdersResult);
                        //console.log(jsonObj);

                        //if we only have one order found we will process it
                        if(jsonObj.length == 1) {
                            //console.log(JSON.stringify(jsonObj[0], null, 4));
                            processSingle(jsonObj[0]);
                        } else if (jsonObj.length == 0) {
                            html += "<h2>No order was found matching that order number. Please try again.</h2>";
                        } else{

                            html += "<h2>We found multiple shipments for this order number. Please review each shipment for the product(s) you need to return.</h2>";
                            //We are going to loop through the returned orders
                            for (var i in jsonObj) {
                                //console.log(JSON.stringify(jsonObj[i], null, 4));

                                if (jsonObj[i].OrderFound) {

                                    html += "<div class='return-order'><h1>" + jsonObj[i].OrderId + "</h1>";

                                    if (jsonObj[i].IsGovX == true) {

                                        html += BuildGovxHtml();

                                        html += displayItems(jsonObj[i]);
                                        html+= "<hr></div>";
                                        continue;
                                    }

                                    if (jsonObj[i].IsGlobale == true) {

                                        html += BuildGlobaleHtml(jsonObj[i]);

                                        html += displayItems(jsonObj[i]);
                                        html+= "<hr></div>";
                                        continue;
                                    }

                                    //Passed Date Section
                                    if (jsonObj[i].PassedDate == true) {

                                        //We need to display a date error to the customer
                                        html += "<ul><li class='passed-date'>Our records show this shipment exceeds the 60 day window for an eligible return. Please contact us at <a href='mailto:customerservice@HYLETE.com'>customerservice@HYLETE.com</a> if you have any questions.</li></ul>";

                                        html += displayItems(jsonObj[i]);
                                        html+= "<hr></div>";
                                        continue;
                                    }

                                    if(jsonObj[i].ReturnFound == true) {

                                        //We need to display a date error to the customer
                                        html += "<h2>A return has already been submitted for this shipment.</h2>";
                                        html += "<ul><li class='return-found'><a target=\"_blank\"  href=\"" + jsonObj[i].LabelUrl + "\"><img width=\"28\" height=\"28\" src=\"/forms/rma/img/print.png\">If you need to print your return label again, click here.</a></li></ul>";
                                    }

                                    if(jsonObj[i].NotEligible == true) {
                                        html += "<ul><li class='passed-date'>Our records show this shipment is not eligible for a return. Please contact us at <a href='mailto:customerservice@HYLETE.com'>customerservice@HYLETE.com</a> if you have any questions.</li></ul>";
                                        html+= "<hr></div>";
                                        continue;
                                    }

                                    if(jsonObj[i].International == true) {
                                        html += "<ul><li class='return-found'><a href=\"/international-returns.html\">Click here to fill out the return form for this shipment.</a></li></ul>";
                                        html+= "<hr></div>";
                                        continue;
                                    }

                                    if(!jsonObj[i].IsShipped) {
                                        html += "<ul><li class='passed-date'>This order has not been shipped yet so it can't be returned. Please contact us at <a href='mailto:customerservice@HYLETE.com'>customerservice@HYLETE.com</a> if you need to make changes to the order.</li></ul>";
                                        html+= "<hr></div>";
                                        continue;
                                    }

                                    if(jsonObj[i].Location == "SC") {
                                        html += "<ul><li class='return-found'><a href=\"/cs-return.html\">Click here to fill out the return form for this shipment.</a></li></ul>";
                                        html+= "<hr></div>";
                                        continue;
                                    }

                                    if(jsonObj[i].Location == "NG") {

                                        var return_form = '<form id=\"form_' + i + '\" name=\"form_' + i + '\" action=\"/forms/rma/return-exchange.php\" method=\"post\">' +
                                            '<input type="hidden" name="creditMemoUsed" value="' + jsonObj[i].CreditMemoUsed + '" />' +
                                            '<input type="hidden" name="giftCardUsed" value="' + jsonObj[i].GiftCardUsed + '" />' +
                                            '<input type="hidden" name="combinedOrder" value="' + jsonObj[i].CombinedOrder + '" />' +
                                            '<input type="hidden" name="orderId" value="' + jsonObj[i].OrderId + '" />' +
                                            '<input type="hidden" name="ignoreClearance" value="' + jsonObj[i].IgnoreClearance + '" />' +
                                            '<input type="hidden" name="isAdmin" value="' + jsonObj[i].IsAdmin + '" />' +
                                            '<input type="hidden" name="exchangeOnly" value="' + jsonObj[i].ExchangeOnly + '" />' +
                                            '<input type="hidden" name="simpleRefund" value="' + jsonObj[i].SimpleRefund + '" />' +
                                            '<input type="hidden" name="sixMonthCashRefund" value="' + jsonObj[i].SixMonthCashRefund + '" />' +
                                            '</form>';

                                        html += return_form;

                                        var message = "Click here to start a return for this shipment.";
                                        if(jsonObj[i].ReturnFound == true) {
                                            message = "If you need to place another return or edit the return for this shipment, click here."
                                        }

                                        html += "<ul><li class='can-return'><a id=\"newReturn_" + i +"\" href=\"javascript:document.form_" + i + ".submit();\"><img width=\"28\" height=\"28\" src=\"/forms/rma/img/package_return.png\">" + message + "</a></li></ul>";

                                        html += displayItems(jsonObj[i]);
                                    }

                                    html+= "<hr></div>";
                                }
                            }

                            jQuery('#resultShow').html(html);

                            jQuery('#resultShow').fadeIn('500');
						}
                    }
	            });
			}
	});
	
});

function BuildGlobaleHtml(obj) {

    var html = "";

    html += "<p><b>It looks like your order was shipped by our international shipping partner. Returns can easily be completed through their returns portal.</b></p>";

    html += "<p><a href='https://web.global-e.com/returns/portal/mZyt?orderID=" + obj.PoOrder + "&email=" + obj.Email + "'>Click here to begin your return.</a></p>";

    return html;
}

function BuildGovxHtml() {

    var html = "";
    html += "<p><h4>It looks like your HYLETE order was originally placed through GovX.com!</h4></p>";

    html += "<p>If you are not 100% satisfied with your purchase, you can return your item(s) within 30 days of purchase for a full product refund directly through GovX.com.</p>";

    html += "<p>GovX will cover the cost of your return shipping! It's easy, and it's FREE!</p>";

    html += "<p><b>To request a Return follow these steps: </b><ol>";
    html += "<li>1. Log in to your account on GovX.com</li>";
    html += "<li>2. Click on My Account in the upper right hand corner of the page and select the Orders tab</li>";
    html += "<li>3. Click on the Return button for the order that contains the item you wish to return</li>";
    html += "<li>4. Fill out the form and submit</li>";
    html += "</ol></p>";

    html += "<p><b>GovX will grant a full refund provided:</b><ol>";
    html += "<li>1. All items are returned in the original state in which they were received (unworn). Clothing and shoes cannot be worn and all tags must be attached</li>";
    html += "<li>2. All items are returned in original packaging with original contents </li>";
    html += "<li>3. All items are shipped in an appropriately sized box with adequate protection to ensure the product is not damaged during transport </li>";
    html += "</ol></p>";

    html += "<p>Please note we cannot accept returns for Clearance items. Clearance items are always final sale.</p>";

    html += "<p>Need a different size? No problem! After you have received your free return shipping label, take advantage of flat rate shipping on all GovX orders to go ahead and purchase the correct size! If you have any other questions, feel free to contact us at 888-468-5511 or email wegotyourback@govx.com.</p>";

    return html;
}

function processSingle(returnObject) {
    if(returnObject.Error != "") {
        jQuery('#notFound').fadeOut('500', function () {
            jQuery('#errorMessage').html(returnObject.Error);
            jQuery('#errorShow').fadeIn('500');
        });
    } else
    {
        var html = "";

        if(returnObject.OrderFound) {

            html += "<div class='return-order'><h1>" + returnObject.OrderId + "</h1>";

            if (returnObject.IsGovX == true) {

                html += BuildGovxHtml();

                html += displayItems(returnObject);
                html += "</div>";

                jQuery('#resultShow').html(html);
                jQuery('#resultShow').fadeIn('500');

                return;
            }

            if (returnObject.IsGlobale == true) {

                html += BuildGlobaleHtml(returnObject);

                html += displayItems(returnObject);
                html += "</div>";

                jQuery('#resultShow').html(html);
                jQuery('#resultShow').fadeIn('500');

                return;
            }

            //Passed Date Section
            if (returnObject.PassedDate == true) {

                //We need to display a date error to the customer
                html += "<ul><li class='passed-date'>Our records show this shipment exceeds the 60 day window for an eligible return. Please contact us at <a href='mailto:customerservice@HYLETE.com'>customerservice@HYLETE.com</a> if you have any questions.</li></ul>";

                html += displayItems(returnObject);
                html+= "</div>";

                jQuery('#resultShow').html(html);
                jQuery('#resultShow').fadeIn('500');

                return;
            }

            if(returnObject.ReturnFound == true) {

                //We need to display a date error to the customer
                html += "<h2>A return has already been submitted for this shipment.</h2>";
                html += "<ul><li class='return-found'><a target=\"_blank\"  href=\"" + returnObject.LabelUrl + "\"><img width=\"28\" height=\"28\" src=\"/forms/rma/img/print.png\">If you need to print your return label again, click here.</a></li></ul>";
            }

            if(returnObject.NotEligible == true) {
                html += "<ul><li class='passed-date'>Our records show this shipment is not eligible for a return. Please contact us at <a href='mailto:customerservice@HYLETE.com'>customerservice@HYLETE.com</a> if you have any questions.</li></ul>";
                html+= "</div>";
                jQuery('#resultShow').html(html);
                jQuery('#resultShow').fadeIn('500');

                return;
            }

            if(returnObject.International == true) {

                html += "<ul><li class='return-found'><a href=\"/international-returns.html\">Click here to fill out the return form for this shipment.</a></li></ul>";
                html+= "</div>";
                jQuery('#resultShow').html(html);
                jQuery('#resultShow').fadeIn('500');

                return;
            }

            if(!returnObject.IsShipped) {
                html += "<ul><li class='passed-date'>This order has not been shipped yet so it can't be returned. Please contact us at <a href='mailto:customerservice@HYLETE.com'>customerservice@HYLETE.com</a> if you need to make changes to the order.</li></ul>";
                html+= "</div>";
                jQuery('#resultShow').html(html);
                jQuery('#resultShow').fadeIn('500');

                return;
            }

            if(returnObject.Location == "SC") {
                html += "<ul><li class='return-found'><a href=\"/cs-return.html\">Click here to fill out the return form for this shipment.</a></li></ul>";
                html+= "</div>";
                jQuery('#resultShow').html(html);
                jQuery('#resultShow').fadeIn('500');

                return;
            }

            else if(returnObject.Location == "NG") {

                var return_form = '<form name="return_form" action="/forms/rma/return-exchange.php" method="post">' +
                    '<input type="hidden" name="creditMemoUsed" value="' + returnObject.CreditMemoUsed + '" />' +
                    '<input type="hidden" name="giftCardUsed" value="' + returnObject.GiftCardUsed + '" />' +
                    '<input type="hidden" name="combinedOrder" value="' + returnObject.CombinedOrder + '" />' +
                    '<input type="hidden" name="orderId" value="' + returnObject.OrderId + '" />' +
                    '<input type="hidden" name="ignoreClearance" value="' + returnObject.IgnoreClearance + '" />' +
                    '<input type="hidden" name="isAdmin" value="' + returnObject.IsAdmin + '" />' +
                    '<input type="hidden" name="exchangeOnly" value="' + returnObject.ExchangeOnly + '" />' +
                    '<input type="hidden" name="simpleRefund" value="' + returnObject.SimpleRefund + '" />' +
                    '<input type="hidden" name="sixMonthCashRefund" value="' + returnObject.SixMonthCashRefund + '" />' +
                    '</form>';

                html += return_form;

                if(returnObject.ReturnFound == true) {
                    var message = "If you need to place another return or edit the return for this shipment, click here.";
                    html += "<ul><li class='can-return'><a id=\"newReturn\" href=\"javascript:document.return_form.submit();\"><img width=\"28\" height=\"28\" src=\"/forms/rma/img/package_return.png\">" + message + "</a></li></ul>";

                    html += displayItems(returnObject);
                    html+= "</div>";

                    jQuery('#resultShow').html(html);
                    jQuery('#resultShow').fadeIn('500');
                } else{
                    jQuery('#resultShow').html(return_form);
                    //document.return_form.submit();
                    jQuery("form[name='return_form']").submit();
                }
            }

        } else{
            jQuery('#resultShow').hide();
            jQuery('#newgistics').hide();
            jQuery('#saddleCreek').hide();
            jQuery('#notFound').fadeIn('500');
        }
    }
}
