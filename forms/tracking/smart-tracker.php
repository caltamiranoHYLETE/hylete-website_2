<?php
//if($_SERVER['SERVER_PORT'] != '443') { header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); exit(); }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "https://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="https://www.w3.org/1999/xhtml">
<head>
<title>HYLETE Order Tracking</title>
<meta name="robots" content="NOINDEX, NOFOLLOW" />
<meta property="og:type" content="website" />
<meta property="og:url" content="http://www.hylete.com/" />
<meta property="og:title" content="HYLETE - Returns & Exchanges" />
<meta property="og:description" content="Performance Cross Training Apparel" />
<link rel="shortcut icon" href="http://www.hylete.com/media/favicon/default/favicon.ico" type="image/x-icon" />

<link rel="stylesheet" type="text/css" href="http://www.hylete.com/media/css/5faceca00c3148479453fc6e46a2b43b.1.0.css" media="all" />
    <link rel="stylesheet" type="text/css" href="/forms/tracking/smart-tracker.css" media="all" />
<script src="/forms/js/jquery.js"></script>
<script src="/forms/js/jquery-ui-1.11/jquery-ui.min.js"></script>
<script src="/forms/js/jquery.validate.min.js"></script>
<script src="/forms/js/smart-tracking.js"></script>

</head>
<body>
    <div style="max-width:500px">
        <form id="smartTrackingForm" method="post" action="xxx">
            <fieldset>
                <label for="orderId">Enter Your Order Number</label>
                <input type="text" name="orderId" id="orderId"/>
                <br/><input type="submit" value="submit" style="margin-top:10px;" />
            </fieldset>
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
            <div style="display:none;" id="notFound">
                Your order was not found. Try reentering your order number, insuring that there are no spaces or invalid characters. If you are still having trouble accessing your tracking, contact customer service at <a href="mailto:customerservice@HYLETE.com">customerservice@HYLETE.com</a>.
            </div>
            <div style="display:none;" id="notAvailable">
                Your order was found but it looks like tracking is unavailable at this time. Please give 24 hours and try again. If you are still having trouble accessing your return tracking, contact customer service at <a href="mailto:customerservice@HYLETE.com">customerservice@HYLETE.com</a>.
            </div>
        </form>
    </div>
    <br/>
    <div id="tracking-results">
    </div>
</body>
</html>
