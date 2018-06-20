<?php
//if($_SERVER['SERVER_PORT'] != '443') { header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); exit(); }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "https://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="https://www.w3.org/1999/xhtml">
<head>
<title>HYLETE Returns</title>
<meta name="robots" content="NOINDEX, NOFOLLOW" />
<meta property="og:type" content="website" />
<meta property="og:url" content="http://www.hylete.com/" />
<meta property="og:title" content="HYLETE - Returns & Exchanges" />
<meta property="og:description" content="Performance Cross Training Apparel" />
<link rel="shortcut icon" href="//www.hylete.com/media/favicon/default/favicon.ico" type="image/x-icon" />

<link rel="stylesheet" type="text/css" href="/media/css/fe4108bfc5cebdf1d9492053b77f3311.1.0.css" media="all" />
    <link rel="stylesheet" type="text/css" href="smart-return.css" media="all" />
<script src="/forms/js/jquery.js"></script>
<script src="/forms/js/jquery-ui-1.11/jquery-ui.min.js"></script>
<script src="/forms/js/jquery.validate.min.js"></script>
    <script src="/forms/js/config.js"></script>
<script src="/forms/js/smart-return.js"></script>

</head>
<body>
    <div class="container">
    <form id="returnForm" method="post" action="smart">
    <fieldset>
        <input type="hidden" name="isAdmin" id="isAdmin" value="false">
        <input type="checkbox" name="ignoreDates" id="ignoreDates" value="true"/>
        <label for="ignoreDates">Ignore Date Requirements</label>
        <br>
        <input type="checkbox" name="ignoreClearance" id="ignoreClearance" value="true"/>
        <label for="ignoreClearance">Ignore Clearance Requirements</label>
        <br><br>
        <label for="orderId">Enter Your Order Number</label>
        <br/>
        <input type="text" name="orderId" id="orderId"/>
        <br/><br/>
        <label for="orderId">Enter the Zip Code for the Shipping Address on the order</label>
        <br/>
        <input type="text" name="zipCode" id="zipCode"/>
        <input type="submit" value="submit" style="margin-top:10px;" />
    </fieldset>
    </form>
    <div id="sectionProcessing" style="display:none">
        <h4>Searching for order, please wait...</h4><img alt="loading..." src="/forms/img/ajax-loader.gif" border="0" />
    </div>
    <div id="errorShow" style="display:none;">
        <table style="width:90%">
            <tr>
                <td><label>There was an error searching for your order:</label></td>
            </tr>
            <tr>
                <td id="errorMessage"></td>
            </tr>
        </table>
    </div>
    <div id="resultShow" style="display:none;">

    </div>
    <div style="display:none;" id="labelFound">
        <h2>A return has already been submitted for this order.</h2>
        <ul>
            <li>
                <h3><a target="_blank" id="returnLabel" href="">If you need to print your return label again, click here.</a></h3>
            </li>
        </ul>
    </div>
    <div style="display:none;" id="showNewReturn">
        <ul>
            <li>
                <h3><a id="newReturn" href="">If you need to return different items on the order, click here.</a></h3>
            </li>
        </ul>
    </div>
    <div style="display:none;" id="notFound">
        Your order was not found. Try reentering your order number, insuring that there are no spaces or invalid characters. If you are still having trouble accessing your return form, contact customer service at <a href="mailto:customerservice@HYLETE.com">customerservice@HYLETE.com</a>.
    </div>
    <div style="display:none;" id="combinedOrder">
        We found your order but there are some special circumstances that need you to contact customer service to complete the return or exchange. <br><br>
        Copy and paste the following information in an email to customerservice@hylete.com.
        <div id="combinedInfo">
            Info...
        </div>
    </div>
    <div style="display:none;" id="newgistics">
        Your order was found. You will now be sent to your HYLETE return form.
    </div>
    <div style="display:none;color:red;" id="dateError">
        Our records show that your order exceeds the 60 day window for an eligible return. Please contact us at <a href="mailto:customerservice@HYLETE.com">customerservice@HYLETE.com</a> if you have any questions.
    </div>
    <div style="display:none;color:red;" id="notEligible">
        Our records show that your order is not eligible for a return. Please contact us at <a href="mailto:customerservice@HYLETE.com">customerservice@HYLETE.com</a> if you have any questions.
    </div>
    <div style="display:none;" id="saddleCreek">
        Your order was found. Proceed to the HYLETE return form.
    </div>
    <div style="display:none;" id="international">
        Your order was found. Proceed to the HYLETE International return form.
    </div>
    </div>
<br><br><br><br>
</body>
</html>
