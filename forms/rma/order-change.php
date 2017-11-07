<?php
//if($_SERVER['SERVER_PORT'] != '443') { header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); exit(); }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "https://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="https://www.w3.org/1999/xhtml">
<head>
    <title>HYLETE Order Change Request</title>
    <meta name="robots" content="NOINDEX, NOFOLLOW" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="http://www.hylete.com/" />
    <meta property="og:title" content="HYLETE - Returns & Exchanges" />
    <meta property="og:description" content="Performance Cross Training Apparel" />
    <link rel="shortcut icon" href="//www.hylete.com/media/favicon/default/favicon.ico" type="image/x-icon" />

    <link rel="stylesheet" type="text/css" href="/media/css/f92e344302cbea810509bf8feda03642.4.0.css" media="all" />
    <link rel="stylesheet" type="text/css" href="order-change.css" media="all" />
    <script src="/forms/js/jquery.js"></script>
    <script src="/forms/js/jquery-ui-1.11/jquery-ui.min.js"></script>
    <script src="/forms/js/jquery.validate.min.js"></script>
    <script type="text/javascript" src="/forms/js/config.js"></script>
    <script src="/forms/js/order-change.js"></script>
    <script type="text/javascript" src="/forms/js/iframeResizer.contentWindow.min.js"></script>

</head>
<body>
<div class="container">
    <form id="returnForm" method="post" action="process.php">
        <fieldset>
            <label for="orderId">Enter Your Order Number</label>
            <input type="text" name="orderId" id="orderId"/>
            <label for="orderId">Enter the email used on the order</label>
            <input type="text" name="email" id="email"/>
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
    <div class="results" id="resultShow" style="display:none;">

    </div>
    <div class="results" id="changeForm" style="display:none;">
        <form method="POST" id="change-order-form">
            <label for="change-order">Please select from the options below to make a change to your order:</label>
            <ul>
                <!--<li><input type="radio" name="change-order" value="address">I need to change my shipping address (US Only).</input></li>-->
                <li><input type="radio" name="change-order" value="item"> I need to change a product/size/color etc.</input></li>
                <li><input type="radio" name="change-order" value="cancel"> I need to CANCEL my order</input></li>
                <li><input type="radio" name="change-order" value="other"> I need to change something else.</input></li>
            </ul>

            <div id="address-area" style="display:none;">
                <p>Please enter the desired shipping address below:</p>
                <label for="first-name">First Name</label><input name="first-name" type="text"/>
                <label for="last-name">Last Name</label><input name="last-name" type="text"/>
                <label for="address1">Address 1</label><input name="address1" type="text"/>
                <label for="address2">Address 2</label><input name="address2" type="text"/>
                <label for="city">City</label><input name="city" type="text"/>
                <label for="state">State</label><input name="state" type="text"/>
                <label for="zip">Zip</label><input name="zip" type="text"/>
            </div>

            <div class="comments" id="comment-area" style="display:none;">
                <label for="comment">Please describe what you need to change about your order below and we'll help you out.</label>
                <textarea title="comments" id="comments" name="comments" rows="20" cols="30" data-msg-required="Please make sure you enter a detailed message so customer support can help you."></textarea>
            </div>

            <div id="submit-area" style="display:none;">
                <input type="submit" value="submit" style="margin-top:10px;" />
            </div>


        </form>
    </div>
    <div style="display:none;" id="notFound">
        Your order was not found. Try reentering your order number, insuring that there are no spaces or invalid characters. If you are still having trouble accessing your return form, contact customer service at <a href="mailto:customerservice@HYLETE.com">customerservice@HYLETE.com</a>.
    </div>
</div>
<br><br><br><br>
</body>
</html>
