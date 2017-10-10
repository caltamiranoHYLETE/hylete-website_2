<?php
if($_SERVER['SERVER_NAME'] != "vaimo.hylete.com") {
	if($_SERVER['SERVER_PORT'] != '443') { header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); exit(); }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "https://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="https://www.w3.org/1999/xhtml">
<head>
<title>powered by HYLETE Account</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
<link href="//s3.amazonaws.com/buzzwidgets/nub/css/buttons.css" type="text/css" rel="stylesheet" />
<link rel="stylesheet" href="/forms/js/jquery-ui-1.11/jquery-ui.css" type="text/css" />
<link rel="stylesheet" href="css/pbh.css">
<link rel="icon" type="image/ico" href="/media/favicon.ico" />

<script src="/forms/js/jquery.js"></script>
<script src="/forms/js/jquery-ui-1.11/jquery-ui.min.js"></script>
<script src="//ajax.aspnetcdn.com/ajax/jquery.validate/1.13.0/jquery.validate.min.js"></script>
<script src="/forms/js/account_script.js?version=20150129"></script>
<style type="text/css">
	*{ padding:0px; margin:0px;}
	#regErrorInlineDiv
	{
		padding:0px!important;
		text-align:center!important;
	}
	.spacer{
		height:7px!important;
	}
	
	.m_on
	{
		color:#ff0000!important;
		display:none!important;
	}
	
	.registrationFormContainer .rfc {
	 padding: 0 5px !important;
	 float:none !important;
	 }
	 .clsRegTextBox {
		border: 1px solid #cccccc !important;
		height: 15px !important;
		padding: 5px !important;
		width: 100% !important;
	}
	
#td_signup{
	font-size:14px;
	color:#666666;
}
#mytd1{
	font-size:14px;
	color:#666666;
}
#mytd2{
	font-size:14px;
	color:#4a4a4a;
}
#mytd3{
	font-size:14px;
	color:#666666; 
	line-height:22px;
}
#mytd4{
	font-size:14px;
	color:#666666; 
	font-weight:normal; 
	font-style:normal;
}
#mytd5{
	font-size:12px;
	color:#666666;
}

.number {
	font-size:42px;
	font-weight:bolder;
	color: #76da36;
}

li {
	margin-left:20px;
	list-style-type: disc;
	line-height: 30px;
}
.banner-container{
	margin-left: 0%; 
	width: 100%; 
	margin-top: 2%;
}
#container-fourth{
	margin-left:2%;
}
h5 {
	font-size:16px;
	color:#666666;
}

.col-md-4 {
	float:right;
	margin-bottom:20px;
}
	#ui-id-1 {
		background-color: #e2e2e2;
		width: 260px;
	}
 
</style>
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
    <div class="col-md-1 column"></div>
	<div id="logo-container" class="col-md-10">
		<a target="_blank" href="/"><img  class="h_logo" border="0" src= "/forms/img/logo-white.png" class="img-v2"   /></a>
	</div>
</div>
	<div class="col-md-1 column"></div>
<div class="container">
  <div class="row clearfix">
    <div class="col-md-12 column">
      <div class="row clearfix">
        <div class="col-md-1 column"></div>
        <div class="col-md-10 column" id="container-first">
		<div class="banner-container">
			<img class="img-v2 center-block" src="/forms/img/banners/HY_pbH_LP_Misc_980x325.jpg" width="100%;"/>
		 </div>
		 <div class="row clearfix">

            <div class="col-md-12 column" id="container-second">
            	
		 	
              <div class="row clearfix">

              		<div class="col-md-4 column">
                
                	<!--- Registration Frame Begins -->
					<form id="regForm" method="post" action="pbh_process.php">
					<fieldset>
                	<div id="registerRightDiv" align="center">
						<table cellpadding="0" cellspacing="0" border="0" align="center" width="100%" id="my_table">   
						 <tr><td>
						 <div id="regPanelDiv"> 
						
						<div id="my_signup" align="center">ACCOUNT CHECK</div>
						<div style="width:100%" id='err_msg' align="center">
							<div style="font-size:12px; color:#ffffff;width:90%" id='err_msg' align="center">
								Enter the email that was used on your account to view your sign ups.
							</div>
						</div>
						<div id="registerDiv"></div>
						<div id="registerShowForm" style="margin-top:10px;">
							<table style="width:100%" align="center">
							<tr>
							<td align="center">
							<input name="txtEmail" type="email" id="txtEmail" value="Email Address" maxlength="240" required />
							</td>
							</tr>
							
							<tr><td height="10"></td></tr>
	
							<tr>
							<td align="center" id="sectionRegister">
								<div style="width:100%;display:none;" id="email_not_found">
									<div style="font-size:12px; color:#76da36;width:90%" align="center">
										An account was not found associated with this email. Please check your spelling and make sure it's the email you used to sign up.
									</div>
								</div>
							
								<div style="width:100%;display:none;" id="child_info">
									<div style="font-size:16px; color:#76da36;width:90%" align="left">
										Welcome <span id="account_name"></span>
									</div>
									<div style="font-size:16px; color:#76da36;width:90%" align="left">
										Sign Ups: <span id="current_children"></span>
									</div>
								</div>
							
								<div id="sectionProcessing" align="center" style="display: none;"><label for="form_submit">Searching for account, please wait...</label><br />
									<img src="/forms/img/ajax-loader.gif" border="0" />
								</div>
								
								<br/>
							<input type="submit" value="Submit" id="form_submit" class="button nbsimple nbgrey">
							</td>
							</tr>
							
							<tr><td height="40"></td></tr>
							<tr>
								<td style="color:#FFFFFF; font-size:12px;font-family: Open Sans, sans-serif; line-height:22px;" align="center">
								<div style="margin-bottom:10px;font-size:12px; line-height:normal; color:#ffffff;width:90%" id='err_msg' align="center">
									Make sure you can be found on the HYLETE.com/pbH Smart Search Bar.
								</div>
								
								<input type="text" name="autocomplete" id="autocomplete" style="padding: 2px;width: 90%;">
								<br>
								<span style="font-size:10px;">(As you type a list of organizations will appear.)</span>
								
								</td>
							</tr>
							<tr>
								<td>
								<div style="width:100%;">
									<div style="padding:20px;font-size:10px; color:#ffffff;" align="left">
										*New accounts should appear on the smart search bar with one business day. <br><br>If your organization does not appear on the smart search bar or if you would like to add an alternative name/event,  please email <a href="mailto:pbH@hylete.com">pbH@hylete.com</a>.
									</div>
								</div>
								</td>
							</tr>
							</table>
						</td></tr></table>
						</div>
						</fieldset>
						</form>
						<!--Signup panel ends-->
						
						</div>
				   <div class="col-md-7 column" id="container-fourth">
              		
					<h5 id="program_details">Through the <b>"powered by HYLETE"</b> program we hope to add value for your gym, events, and members.</h5>
					
					<h5 id="program_details">To increase accounts created by your members please complete the following steps:</h5>

            		<div style="clear:both; height:15px;"></div>
	                  <table cellpadding="1" cellspacing="1" width="100%">
	                    <tr>
	                      <td width="40"><span class="number">1</span></td>
	                      <td id="td_signup">Go to our <a target="_blank" href="https://hylete.imagerelay.com/fl/20bc5ccd9cbe4c5a86fd7f6c62a9acec">HYLETE Banner Site</a> and place a banner on your site.<br/>Link the banners to www.hylete.com/pbh</td>
	                    </tr>
	                    <tr>
	                      <td height="20"></td>
	                    </tr>
	                    <tr>
	                      <td><span class="number">2</span></td>
	                      <td id="mytd2">Integrate the HYLETE offer into your client management system (MINDBODY/WODify/Zen Planner...) so that all current and new members have access to the discount automatically.</td>
	                    </tr>
	                    <tr>
	                      <td height="20"></td>
	                    </tr>
	                    <tr>
	                      <td><span class="number">3</span></td>
	                      <td id="mytd3">Wear HYLETE, and distribute the "powered by HYLETE" discount cards that came in the "powered by HYLETE" box. If you have not received a box or have run out of discount cards please email <a href="mailto:pbH@hylete.com">pbH@hylete.com</a></td>
	                    </tr>
	                    <tr>
	                      <td height="20"></td>
	                    </tr>
	                    <tr>
	                      <td><span class="number">4</span></td>
	                      <td id="mytd4">Communicate the promotion below to your network via social media and email:</td>
	                    </tr>
	                    <tr>
	                      <td></td>
	                      <td id="mytd5" height="180">
	                      	<span id="account_name1">YOUR NAME</span> is an official endorser of HYLETE performance apparel and has become "powered by HYLETE". As a benefit, you are eligible for a <b>"powered by HYLETE"</b> Athlete account.<br/><br/>
								<ul>
									<li>Visit HYLETE.com/pbh to create your account.</li>
									<li>Receive exclusive powered by HYLETE pricing.</li>
									<li>Select "<span id="account_name2">YOUR NAME</span>" as your referred by.</li>
								</ul>
	                      </td>
	                    </tr>
	                  </table>
                	</div>
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
			&copy; Copyright 2015 HYLETE, LLC - All Rights Reserved.
		</div>
</div>
<!--footer-->
</body>
</html>
