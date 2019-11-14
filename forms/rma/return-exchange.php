<?php
//if($_SERVER['SERVER_PORT'] != '443') { header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); exit(); }

function test_input($data) {
    if( isset($_POST[$data]) ) {
        $postData = $_POST[$data];
        $postData = trim($postData);
        $postData = stripslashes($postData);
        $postData = htmlspecialchars($postData);

        return $postData;
    }

    return "";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $orderId = test_input("orderId");
    $ignoreClearance = test_input("ignoreClearance");
    $creditMemoUsed = test_input("creditMemoUsed");
    $giftCardUsed = test_input("giftCardUsed");
    $combinedOrder = test_input("combinedOrder");
    $isAdmin = test_input("isAdmin");
    $exchangeOnly = test_input("exchangeOnly");
    $simpleRefund = test_input("simpleRefund");
    $sixMonthCashRefund = test_input("sixMonthCashRefund");
} else {
    $orderId = "";
    $ignoreClearance = "false";
    $creditMemoUsed = "false";
    $giftCardUsed = "false";
    $combinedOrder = "false";
    $isAdmin = "false";
    $exchangeOnly = "false";
    $simpleRefund = "false";
	$sixMonthCashRefund = "false";
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>HYLETE Returns & Exchanges</title>
    <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
    <meta content="utf-8" http-equiv="encoding">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="NOINDEX, NOFOLLOW" />
    <link rel="shortcut icon" href="https://www.hylete.com/media/favicon/default/favicon.png" type="image/x-icon" />

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="/forms/rma/20160104_css.css" media="all" />
    <link rel="stylesheet" href="/forms/rma/return-exchange-css.css">

    <script src="/forms/js/jquery.min.js"></script>
    <!--<script src="https://code.jquery.com/jquery-migrate-3.0.1.js"></script>-->
    <script type="text/javascript" src="/forms/js/bootstrap.min.js"></script>
    <script src="/forms/js/jquery.validate.min.js"></script>
    <script src="/forms/js/additional-methods.min.js"></script>
    <script type="text/javascript" src="/forms/js/config.js"></script>
    <script type="text/javascript" src="/forms/js/return-exchange-script.js"></script>

</head>
<body>
<div id="head-container">
    <div id="logo-container"><a target="_blank" href="/"><img class="h_logo" border="0" src= "/forms/img/logo-white.png" /> </a> </div>
</div>
<div class="container">

    <div id="loading_area">
        <?php if($orderId == "") {
            echo "<h3>No Order Number Was Entered Into The Form.<br>Please Try Again.</h3>";
        } else{
            echo "<h3 id='loadingMessage'>Your Order Was Found! Loading Items...</h3><img id='loadingImage' src=\"/forms/img/ajax-loader.gif\" border=\"0\" />";
        }
        ?>
    </div>

    <div class="modal fade" id="myModal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div id="modal-message"><h1>Thanks!</h1><br/><h4>please wait while we process your return...</h4><img src="/forms/img/ajax-loader.gif" border="0" /></div>
                    <div id="print-label-area" style="display:none;">
                        <h5><a class="text-link" target="_blank" href="">view & print your shipping label</a></h5>
                        <br/>
                        <h5 id="email-link"></h5>
                        <p class="email-loader" style="display:none;">
                            <span class="email-notice">sending email, please wait.</span><br/>
                            <img src="/forms/img/ajax-loader.gif" border="0" />
                        </p>
                        <p class="email-sent" style="display:none;">
                            <span class="email-notice">email sent!</span><br/>
                        </p>
                        <a class="img-link" target="_blank" href="">
                            <img src="/forms/img/return-label.png">
                        </a>
                        <div style="text-align: left;">
                            <h2>Please follow these steps for a smooth return:</h2>
                            <ul style="list-style-type: circle; list-style-position: inside;">
                                <li>Keep the tags on or include them in your return package.</li>
                                <li>Use the return label we provide, after you click submit below.</li>
                                <li>Make sure that any item included in your return package is also entered here on this form.</li>
                                <li>Drop it off at your local U.S. Post Office.</li>
                                <li>After your package has been picked up and scanned by the carrier, we'll get to work on processing it for you, as quickly as possible.</li>
                                <li>If for any reason you are not able to submit your request as outlined above, please reach out to our Brand Support Team through our LiveChat option or via email at <a href="mailto:customerservice@hylete.com">customerservice@hylete.com</a>a>. We’d be happy to help!</li>
                            </ul>
                            <br/>
                        </div>
                    </div>
                    <div id="warranty-label-area" style="display:none;">
                        <h5 id="warranty-message"></h5>
                        <a href="/smart-return">Go back to the Smart Return page</a>
                    </div>
                    <div id="nonreturnable_contact" style="display:none;">
                        <form id="nonreturnable_contactForm" method="post" action="xxx.php">
                            <h2>create a support ticket</h2>
                            <p>Fill out the email address below to create a support ticket for your return.</p>
                            <ul class="form-list">
                                <li>
                                    <label for="nonreturnable_subject">Subject:</label>
                                    <p style="text-align: left;">item assistance return request for order: <?php echo strtoupper($orderId); ?></p>
                                </li>
                                <li>
                                    <label for="nonreturnable_email" class="required">Email Address <span class="required">*</span></label>
                                    <input type="email" id="nonreturnable_email" class="input-text required-entry validate-email validation-passed" title="Email Address" autocomplete="off" required data-msg="Please enter your email address"/>
                                </li>
                                <li>
                                    <label for="nonreturnable_comments">Comments</label>
                                    <textarea id="nonreturnable_comments"></textarea>
                                </li>
                                <li><input style="width:200px;" class="button" type="submit" value="submit"></li>
                            </ul>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>


    <div id="content_area">
        <h2>Return/Exchange for Order: <?php echo strtoupper($orderId); ?></h2>
        <div class="steps">Would you like a <b>refund</b> or an <b>exchange for size</b>? <span id="step1" class="restrictions">more info</span></div>
        <div class="errorNotice" id="methodErrorNotice"></div>
        <form id="choiceForm" autocomplete="off">
            <input type="hidden" name="isAdmin" id="isAdmin" value="<?php echo $isAdmin ?>">
            <input type="hidden" name="orderId" id="orderId" value="<?php echo $orderId ?>">
            <input type="hidden" name="ignoreClearance" id="ignoreClearance" value="<?php echo $ignoreClearance ?>">
            <input type="hidden" name="creditMemoUsed" id="creditMemoUsed" value="<?php echo $creditMemoUsed ?>">
            <input type="hidden" name="giftCardUsed" id="giftCardUsed" value="<?php echo $giftCardUsed ?>">
            <input type="hidden" name="combinedOrder" id="combinedOrder" value="<?php echo $combinedOrder ?>">
            <input type="hidden" name="exchangeOnly" id="exchangeOnly" value="<?php echo $exchangeOnly ?>">
            <input type="hidden" name="simpleRefund" id="simpleRefund" value="<?php echo $simpleRefund ?>">
            <input type="hidden" name="sixMonthCashRefund" id="sixMonthCashRefund" value="<?php echo $sixMonthCashRefund ?>">
            <div id="choice_area">
                <div id="choice1">
                    <input type="radio" value="store_credit" id="memo_choice" name="refund-or-exchange">
                    <label id="creditMemoTooltip" class="choice_text">&nbsp;Store Credit</label>
                    <span class="spacer"></span>
                    <input type="radio" value="refund" id="refund_choice" name="refund-or-exchange" >
                    <label id="refundTooltip" class="choice_text">&nbsp;Cash Refund</label>
                    <span class="spacer"></span>
                    <input type="radio" value="exchange" id="exchange_choice" name="refund-or-exchange">
                    <label id="exchangeTooltip" class="choice_text">&nbsp;Exchange For Size</label>

                    <?php
                    if($isAdmin == "true") {
                        ?>
                        <span class="spacer"></span>
                        <input type="radio" value="exchange" id="exchange_other_choice" name="refund-or-exchange">
                        <label id="exchangeOtherTooltip" class="choice_text">&nbsp;Exchange For Other Item</label>
                        <span class="spacer"></span>
                        <input type="radio" value="warranty" id="warranty_other_choice" name="refund-or-exchange">
                        <label id="warrantyOtherTooltip" class="choice_text">&nbsp;Warranty Exchange</label>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </form>

        <hr>

        <div class="steps">Click the <span class="plus-text">+</span> on the products you want to return/exchange. <span id="productTooltip" class="restrictions">view restrictions</span></div>
        <div class="errorNotice" id="productErrorNotice"></div>

        <form id="productForm" autocomplete="off">
            <div id="product_table_area">

            </div>
        </form>

        <hr>

        <div class="steps">Enter or verify your contact information. If exchanging, this is the address we will send the new order to.</div>
        <div class="errorNotice" id="addressErrorNotice"></div>
        <form id="addressForm" method="post" action="">
            <fieldset id="address_fields">
                <label>first name <span class="required">*</span></label><br><input type="text" name="firstName" id="firstName" required data-msg="Please enter your first name"/>
                <br><label>last name <span class="required">*</span></label><br><input type="text" name="lastName" id="lastName" required data-msg="Please enter your last name"/>
                <br><label>email <span class="required">*</span></label><br><input type="text" name="email" id="email" required data-msg="Please enter your email address"/>
                <br><label>address 1 <span class="required">*</span></label><br><input type="text" name="address1" id="address1" required data-msg="Please enter your address"/>
                <br><label>address 2</label><br><input type="text" name="address2" id="address2"/>
                <br><label>city <span class="required">*</span></label><br><input type="text" name="city" id="city" required data-msg="Please enter your city"/>
                <br><label>state <span class="required">*</span></label><br><select name="state" id="state" required data-msg="Please select a state">
                    <option value="AE">Armed Forces Europe</option>
                    <option value="AP">Armed Forces Pacific</option>
                    <option value="AA">Armed Forces Americas</option>
                    <option value="AL">Alabama</option>
                    <option value="AK">Alaska</option>
                    <option value="AZ">Arizona</option>
                    <option value="AR">Arkansas</option>
                    <option value="CA">California</option>
                    <option value="CO">Colorado</option>
                    <option value="CT">Connecticut</option>
                    <option value="DE">Delaware</option>
                    <option value="DC">District Of Columbia</option>
                    <option value="FL">Florida</option>
                    <option value="GA">Georgia</option>
                    <option value="HI">Hawaii</option>
                    <option value="ID">Idaho</option>
                    <option value="IL">Illinois</option>
                    <option value="IN">Indiana</option>
                    <option value="IA">Iowa</option>
                    <option value="KS">Kansas</option>
                    <option value="KY">Kentucky</option>
                    <option value="LA">Louisiana</option>
                    <option value="ME">Maine</option>
                    <option value="MD">Maryland</option>
                    <option value="MA">Massachusetts</option>
                    <option value="MI">Michigan</option>
                    <option value="MN">Minnesota</option>
                    <option value="MS">Mississippi</option>
                    <option value="MO">Missouri</option>
                    <option value="MT">Montana</option>
                    <option value="NE">Nebraska</option>
                    <option value="NV">Nevada</option>
                    <option value="NH">New Hampshire</option>
                    <option value="NJ">New Jersey</option>
                    <option value="NM">New Mexico</option>
                    <option value="NY">New York</option>
                    <option value="NC">North Carolina</option>
                    <option value="ND">North Dakota</option>
                    <option value="OH">Ohio</option>
                    <option value="OK">Oklahoma</option>
                    <option value="OR">Oregon</option>
                    <option value="PA">Pennsylvania</option>
                    <option value="RI">Rhode Island</option>
                    <option value="SC">South Carolina</option>
                    <option value="SD">South Dakota</option>
                    <option value="TN">Tennessee</option>
                    <option value="TX">Texas</option>
                    <option value="UT">Utah</option>
                    <option value="VT">Vermont</option>
                    <option value="VA">Virginia</option>
                    <option value="WA">Washington</option>
                    <option value="WV">West Virginia</option>
                    <option value="WI">Wisconsin</option>
                    <option value="WY">Wyoming</option>
                </select>
                <br/>
                <label>postal code <span class="required">*</span></label><br><input type="text" name="postalCode" id="postalCode" required data-msg="Please enter your postal code"/>

                <input type="checkbox" value="yes" name="acknowledge" id="acknowledge"/>
                <label for="acknowledge"><span style="text-transform:uppercase">I</span> acknowledge that <span style="text-transform:uppercase">I</span> am returning items that match my submitted return authorization exactly. <span style="text-transform:uppercase">I</span> also acknowledge that if the items <span style="text-transform:uppercase">I</span> am returning are missing tags, damaged (excluding manufacturer damage) or otherwise in a condition that makes them unfit for sale, <span style="text-transform:uppercase">I</span> may have to pay a restocking fee of $10.00.<span class="required">*</span></label>

                <br><span style="display:block;text-align:right;color:red;">* required fields</span>
            </fieldset>

            <fieldset id="notes_fields">
                <!--<label>notes for customer service</label><textarea cols="20" rows=30" type="text" name="notes" id="notes" maxlength="1000"></textarea><br>
                *This field is intended for feedback and does not serve to modify your return request.-->
                <div style="text-align: left;">
                    <h2>Please follow these steps for a smooth return:</h2>
                    <ul style="list-style-type: circle; list-style-position: inside;">
                        <li>Keep the tags on or include them in your return package.</li>
                        <li>Use the return label we provide, after you click submit below.</li>
                        <li>Make sure that any item included in your return package is also entered here on this form.</li>
                        <li>Drop it off at your local U.S. Post Office.</li>
                        <li>After your package has been picked up and scanned by the carrier, we'll get to work on processing it for you, as quickly as possible.</li>
                        <li>If for any reason you are not able to submit your request as outlined above, please reach out to our Brand Support Team through our LiveChat option or via email at <a href="mailto:customerservice@hylete.com">customerservice@hylete.com</a>. We’d be happy to help!</li>
                    </ul>
                    <br/>
                    <p>Please remember, the items you are returning must match your submitted return authorization exactly. Should you neglect to return items from your submitted return authorization or return different/additional items as not reflected on your submitted return authorization, you may be charged a fee for any differences.</p>
                </div>
            </fieldset>

            <fieldset id="results_field">
                <input class="button" type="submit" value="submit" />
            </fieldset>

            <div id="sectionProcessing" align="center" style="display: none;"><label for="form_submit">creating label, please wait...</label><br />
                <img src="/forms/img/ajax-loader.gif" border="0" />
            </div>
            <div id="errorShow" style="display:none;">
                <table style="width:90%">
                    <tr>
                        <td align="center"><label>There was an error creating your return:</label></td>
                    </tr>
                    <tr>
                        <td align="center" id="errorMessage"></td>
                    </tr>
                </table>
            </div>
        </form>
    </div>
</div>
<br><br>
</body>
</html>
