<?php
if($_SERVER['SERVER_NAME'] != "vaimo.hylete.com") {
	if($_SERVER['SERVER_PORT'] != '443') { header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); exit(); }
}

require 'functions.php';
require 'config.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "https://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="https://www.w3.org/1999/xhtml">
<head>
<title><?php echo $pageTitle ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
<link rel="stylesheet" href="/forms/js/jquery-ui-1.11/jquery-ui.css" type="text/css" />
<link rel="stylesheet" href="/forms/partners/css/styles.css?version=20160210">
<link rel="icon" type="image/ico" href="/media/favicon.ico" />

<script src="/forms/js/jquery.js"></script>
<script src="/forms/js/jquery-ui-1.11/jquery-ui.min.js"></script>
<script src="/forms/js/jquery.validate.min.js"></script>
<script src="/forms/js/partner_script.js?version=20151123"></script>
<script src="/forms/js/ga.js?"></script>
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
			<?php if($error == true) {
			    echo "<h1>There was an error processing your request</h2>";
			    echo "<p>If you continue to receive this error, please let us know by sending an email to <a href='mailto:support@hylete.com'>support@hylete.com</a>.<br><br>Thank You!</p>";
			} else {
			?>
    			<img class="img-v2 center-block"  src="/forms/img/<?= $banner ?>"  width="100%;"/>
    			<?php if($title !="") { ?>
    				<!--<h4 style="text-align: center;"><?php echo $title ?></h4>-->
    			<?php } ?>

			<?php if($showStandard == "true") { ?>
				<h4 style="text-align: center;">Our promise to you is to deliver premium performance apparel and gear that powers your functional-fitness lifestyle, while continuously pushing the limits to support and strengthen the HYLETE nation.</h4>
				</br>
				<h4 style="text-align: center;"><?php echo $intro ?></h4>
				<h5><b>remove the middle man:</b> You won't find HYLETE at traditional retail stores. By removing the retailer's markup, we can pass the savings on to you.</h5>
				<h5><b>more you buy, lower the cost:</b> Shipping one item is expensive. The more you buy, the lower the cost to pick, pack and ship your order. That means you get more, for less.</h5>
				<h5><b>HYLETE nation:</b> Your voice is our most powerful promotion; we need you to share the HYLETE story.</h5>
			<?php } elseif($showStandard == "trainer") { ?>
				<h4 style="text-align: center;"><?php echo $intro ?></h4>
				<h5 style="text-align: center;">As a fast growing premium performance apparel company, we rely on the support and feedback of trainers such as yourself to build and strengthen the HYLETE nation. The HYLETE trainer program is built for those who aspire to motivate and improve the lifestyle of their clients through health and fitness.</h5>
			<?php } else { ?>
			    <h4 style="text-align: center;"><?php echo $intro ?></h4>
			<?php } ?>

		 </div>
			</br>
		 <div class="row clearfix">
            <div class="col-md-12 column" id="container-second">
              <div class="row clearfix">
				   	<div class="col-md-7 column" id="container-fourth">

						<?php if($showStandard == "true") { ?>
						<h4>how it works</h4>
						<?php } elseif($showStandard == "trainer") { ?>
						<h4>Sign up and receive the following perks:</h4>
						<?php } else { ?>
							<div class="col-md-12 column" id="container-fourth">
								<h4>First Time Users:</h4>
								<table cellpadding="1" cellspacing="1" width="100%">
									<tr>
										<td id="td_signup">Enter your first and last name, email address, and password to create your HYLETE account. </td>
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
						<?php } ?>

	                	<div style="clear:both; height:15px;"></div>
		                  <table cellpadding="1" cellspacing="1" width="100%">
		                    <?php if(isset($step1) && $step1 != "") { ?>
		                    	<tr>
									<td width="52"><span class="dash">-</span></td>
			                      <td class="td_signup"><?php echo $step1 ?></td>
			                    </tr>
			                    <tr>
			                      <td height="15"></td>
			                    </tr>
		                    <?php } ?>
		                    <?php if(isset($step2) && $step2 != "") { ?>
		                    	<tr>
									<td width="52"><span class="dash">-</span></td>
			                      <td class="td_signup"><?php echo $step2 ?></td>
			                    </tr>
			                    <tr>
			                      <td height="15"></td>
			                    </tr>
		                    <?php } ?>
		                    <?php if(isset($step3) && $step3 != "") { ?>
		                    	<tr>
									<td width="52"><span class="dash">-</span></td>
			                      <td class="td_signup"><?php echo $step3 ?></td>
			                    </tr>
			                    <tr>
			                      <td height="15"></td>
			                    </tr>
		                    <?php } ?>
		                    <?php if(isset($step4) && $step4 != "") { ?>
		                    	<tr>
									<td width="52"><span class="dash">-</span></td>
			                      <td class="td_signup"><?php echo $step4 ?></td>
			                    </tr>
			                    <tr>
			                      <td height="15"></td>
			                    </tr>
		                    <?php } ?>
							<?php if(isset($step5) && $step5 != "") { ?>
								  <tr>
									  <td width="52"><span class="dash">-</span></td>
									  <td class="td_signup"><?php echo $step5 ?></td>
								  </tr>
								  <tr>
									  <td height="15"></td>
								  </tr>
							  <?php } ?>
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
          <?php } ?>
        </div>
        
        <div class="col-md-1 column"></div>
      </div>
      
    </div>
  </div>
</div>
 <!--footer-->
 <div class="footer-container">
		<div id="my_div4">
			&copy; Copyright 2015 HYLETE, LLC - All Rights Reserved.
		</div>
</div>
<!--footer-->
</body>
</html>
