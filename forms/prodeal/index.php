<?php
if($_SERVER['SERVER_NAME'] != "local.hylete.com") {
	if($_SERVER['SERVER_PORT'] != '443') { header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); exit(); }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "https://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>HYLETE - Pro Deal</title>
<meta name="robots" content="noindex, nofollow">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
<link rel="stylesheet" href="/forms/js/jquery-ui-1.11/jquery-ui.css" type="text/css" />
<link rel="stylesheet" href="/forms/prodeal/css/styles.css">
<link rel="icon" type="image/ico" href="/media/favicon.ico" />

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <!--<script src="https://code.jquery.com/jquery-migrate-3.0.1.js"></script>-->
    <script src="https://jqueryvalidation.org/files/dist/jquery.validate.min.js"></script>
    <script src="https://jqueryvalidation.org/files/dist/additional-methods.min.js"></script>
    <script type="text/javascript" src="/forms/js/config.js"></script>
	<script src="/forms/js/prodeal.js"></script>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-32586667-1', 'auto');
  ga('send', 'pageview');

</script>
</head>

<body>
<div id="head-container">
	<div id="logo-container"><a target="_blank" href="/"><img class="h_logo" border="0" src= "/forms/img/logo-white.png" /> </a> </div>
</div>
<div class="container">
  <div class="row clearfix">
    <div class="col-md-12 column">
      <div class="row clearfix">
        <div class="col-md-1 column"></div>
        <div class="col-md-10 column" id="container-first">
		<div class="banner-container">
			<img class="img-v2 center-block"  src="/forms/prodeal/img/large-large-hy_prodeal_1170.jpg"  width="100%;"/>
		 </div>
		 <div class="row clearfix">
            <div class="col-md-12 column" id="container-second">
              <div class="row clearfix">
	        	<div class="h_mycontent">
	       	  	<p>&nbsp;</p>
	          	<p class="myparagraph">Welcome to the HYLETE pro deal page. <br/>As a pro deal member, you'll receive exclusive pricing on all HYLETE products.</p>
			  	<p>&nbsp;</p>
			  	</div>
				   	<div class="col-md-7 column" id="container-fourth">
						<h4>First Time Users:</h4>
						  <table cellpadding="1" cellspacing="1" width="100%">
								<tr>
								  <td id="td_signup">Enter your first and last name, email address, and password to create your pro deal account. The email address associated with your account must be one that has been submitted for HYLETE pro deal activation.</td>
								</tr>
								<tr>
								  <td height="15"></td>
								</tr>
						  </table>
						<h4>Returning Users:</h4>
							<table cellpadding="1" cellspacing="1" width="100%">
								<tr>
									<td id="td_signup"><a href="/customer/account/login/">Log into HYLETE.com.</a></td>
								</tr>
								<tr>
									<td height="15"></td>
								</tr>
							</table>
						</div>
		                <div class="col-md-4 column">
							<?php include("account_form.php"); ?>
						</div>
                	</div>
	                <div class="col-md-10 column" style="margin:33px 0 45px 0;">
	                	<div class="row clearfix">
	                    </div>
	                </div>
                <div class="col-md-2 column"></div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="col-md-1 column"></div>
      </div>
      
    </div>
  </div>
</div>
 <!--footer-->
 <div class="footer-container">
		<div id="my_div4">
			&copy; Copyright <?php echo date("Y") ?> HYLETE, LLC - All Rights Reserved.
		</div>
</div>
<!--footer-->
</body>
</html>
