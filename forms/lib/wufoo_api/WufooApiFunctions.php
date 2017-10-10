<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/library/config.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/library/wufoo_api/WufooApiWrapper.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/library/phpmailer/class.phpmailer.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/library/phpmailer/class.smtp.php");
require_once($_SERVER["DOCUMENT_ROOT"].'/library/adodb5/adodb.inc.php');
require_once($_SERVER["DOCUMENT_ROOT"]."/library/mailchimp/MCAPI.class.php");

/* Main Login Process*/
function hasAccess() {
	if(isset($_COOKIE['TeamAdmin'])) {
		if($_COOKIE['TeamAdmin'] == "true") {
			return true;
		} else {
			return false;	
		}
	} else {
		return false;	
	}
}

function hasProFormAccess() {
	if(isset($_COOKIE['ProFormAdmin'])) {
		if($_COOKIE['ProFormAdmin'] == "true") {
			return true;
		} else {
			return false;	
		}
	} else {
		return false;	
	}
}

function CheckTeamRider($session) {
	
	$customer = $session->getCustomer(); 

	$blRet = false;
	//first we check if they are the right group_id
	//4 is Pro Form on local
	if($customer->getData('group_id') == 5) {
			$rider = new Rider();
			$rider->loadByEmail($customer->getData('email'));
				
			if($rider->opt == true && $rider->approved == true) {
				$blRet = true;
			}
	}
	
	return $blRet;
	
}

function LoadTeamRiderBySession($session) {
	
	$customer = $session->getCustomer(); 
	//first we check if they are the right group_id
	if($customer->getData('group_id') == 5) {
		$rider = new Rider();
		$rider->loadByEmail($customer->getData('email'));
			
		return $rider;
	} else {
		return null;
	}

}

function GetWufooEntry($wufooId) {
	$wrapper = new WufooApiWrapper(WUFOO_API, "h2oaudio");
	$args = "Filter1=EntryId+Is_equal_to+".$wufooId;
	return $wrapper->getEntries("m7p6p1", 'forms', $args);	
}

function GetProFormEntry($wufooId) {
	$wrapper = new WufooApiWrapper(WUFOO_API, "h2oaudio");
	$args = "Filter1=EntryId+Is_equal_to+".$wufooId;
	return $wrapper->getEntries("m7p6q5", 'forms', $args);	
}

function importNewEntries() {
	$example = new WufooApiWrapper(WUFOO_API, "h2oaudio");
										
	$strSort = "&sort=DateCreated";
	$strSort .= "&sortDirection=DESC";
	$args = "&pageStart=0&pageSize=100".$strSort;
	$arrEntries = $example->getEntries("m7p6p1", 'forms', $args);

	if(isset($arrEntries)){
		$arrCount = count($arrEntries);
	}

	foreach($arrEntries as $o => $entry){
		
		$athleteRS = getAthleteRS($entry->EntryId);
		if($athleteRS->EOF) {
			$rider = new Rider();
			$rider->insert($entry);
		}
	}
}

function importWufooEntries($arrEntries) {
	
	foreach($arrEntries as $o => $entry){
		
		$athleteRS = getAthleteRS($entry->EntryId);
		if($athleteRS->EOF) {
			$rider = new Rider();
			$rider->insert($entry);
			echo "Inserted ".$entry->Field2." ".$entry->Field3."<br>";
		} else {
			$rider = new Rider();
			$rider->update($entry);
			echo "Updated ".$entry->Field2." ".$entry->Field3."<br>";
		}
	}
	
}

//When we load a rider we will see if we can get their Magento ID and if their voucher code has been used.
function SyncRider($rider) {
		
	//echo "TESTING";	
	
	if($rider->magentoId == null || $rider->magentoId == '') {
		
		//let's see if we can sync their MagentoID
		Mage::app('h2oaudio', 'website');
		$websiteId = Mage::app()->getWebsite()->getId();
		$store = Mage::app()->getStore();
		
		$customer = Mage::getModel("customer/customer");
		$customer->website_id = $websiteId;
		$customer->setStore($store);
	
		try{
			$customer->loadByEmail($rider->email);
			$strID = $customer->getId();
			if($strID != '' && $strID != null) {
				//echo "Magento ID: ".$strID;
				$rider->magentoId = $strID;
				$rider->save();
			}
		} catch(exception $ex) {
			echo($ex->getMessage());
		}
	}
	
	if($rider->voucher != '' && $rider->voucherUsed == 0) {
		//Next We see if they have a voucher code. If they do we see if it has been used.
		$conn = NewADOConnection('mysql');
		$conn->Connect("www.x-1.com", SQL_USER, SQL_PWD, MAGENTO_DB);
		$rs = $conn->Execute("SELECT * FROM sales_order_varchar WHERE value = '".$rider->voucher."'");
	
		while (!$rs->EOF) {

			$couponCode = trim($rs->fields["value"]);
			$coupon = Mage::getModel('salesrule/rule');
			$coupon->load($couponCode, 'coupon_code');
	
			$couponID = $coupon->getId();
			
			if($couponID != '') {
				$quoteUsage = Mage::getResourceModel('sales/quote');
				$read = $quoteUsage->getReadConnection();     
				$select2 = $read->select()
					->from($quoteUsage->getMainTable())                 
					->where('applied_rule_ids like ?', "%".$couponID."%");    
				$data2 = $read->fetchAll($select2);
				
				//echo($select2);
		
				foreach($data2 as $quoteUsage){
					//print_r($quoteUsage);
					
					$strSql = "UPDATE team_riders SET voucher_used = 1 WHERE rider_id = ".$rider->riderId;
					dbExecute($strSql);
		
				}
			}
	
			$rs->MoveNext();
		}
		
	}
	
	
}

function addAnd($string) {
	
	if($string != "") {
		return " AND ".$string;
	} else {
		return $string;
	}
}

function getFeaturedTeamRiders() {
	$strSql = "SELECT * FROM team_riders WHERE featured = 1 ORDER BY lastname";

	$result = dbExecute($strSql);
	
	return $result;
					
}

/*
  This writes the Athletes table out
  $strFilter: a string of SQL filters
*/
function writeEntries($strFilter) {
	
	$strSql = "SELECT * FROM team_riders ".$strFilter;
	
	//echo $strSql;
	
	$result = dbExecute($strSql);
					
	while (!$result->EOF) {
		
		$rider = new Rider();
		$rider->load($result);
		writeEntry($rider);
		
		$result->MoveNext();
	 }
	
	echo "<tr><td align='right' colspan='13'>Total Team Members: ".$result->RecordCount()."</td></tr>";
}

function getAllRiders($filter) {
	
	$strSql = "SELECT * FROM team_riders ".$filter;
	
	//echo $strSql;
	
	return dbExecute($strSql);
}

/*
  This writes the Athletes table out
  $rider: the Rider Object
*/
function writeEntry($rider) {
		
	if($rider->comments != "") {
		$icon = "<img width='18' height='18' title='".$rider->comments."' src='/images/comment.png' />";
	} else {
		$icon = "";
	}
	
	if($rider->level == 3) {
		$stars = "<img title='Super Star!' width='52' height='16' src='/images/team/3_stars.png' />";
	} elseif($rider->level == 2) {
		$stars = "<img title='Shining Star!' width='52' height='16' src='/images/team/2_stars.png' />";
	}else {
		$stars = "<img title='One Star!' width='52' height='16' src='/images/team/1_star.png' />";
	}
	
	if($rider->voucherUsed == "1") {
		$money = "<img width='18' height='18' title='Voucher Used' src='/images/dollar_icon.png' />";
	} else {
		$money = "";
	}
	
	$photos = getRiderFeaturedPhoto($rider->riderId);
	if($photos->RecordCount() > 0) {
		if($rider->featured) {
			$profile = "<img width='19' height='18' title='Profile Added' src='/images/profile_icon_green.png' />";
		} else {
			$profile = "<img width='19' height='18' title='Profile Added' src='/images/profile_icon.png' />";
		}
	} else {
		$profile = "";
	}
	
	if($rider->facebook_group == "1") {
		$facebook = "<img width='15' height='15' title='Facebook Group' src='/images/blue-facebook.gif' />";
	} else {
		$facebook = "";
	}
	
	$strOpt = "Not Yet";
	if(cBoolString($rider->opt) == "Yes"){
		$strOpt = "<span style='color:green;'>Yes</span>";
	} elseif(cBoolString($rider->opt) == "No") {
		$strOpt = "<span style='color:red;'>No</span>";
	}
	
	$strProForm = "-";
	if(cBoolString($rider->proformOnly) == "Yes"){
		$strOpt = "-";
		$strProForm = "<span style='color:green;'>Yes</span>";
	} elseif(cBoolString($rider->proformOnly) == "No") {
		$strProForm = "<span style='color:red;'>No</span>";
	}

	$strApproved = "Not Yet";
	if(cBoolString($rider->approved) == "Yes"){
		$strApproved = "<span style='color:green;'>Yes</span>";
	} elseif(cBoolString($rider->approved) == "No") {
		$strApproved = "<span style='color:red;'>No</span>";
		$strOpt = "-";
	}
	
	$strFeatured = "-";
	if(cBoolString($rider->featured) == "Yes"){
		$strFeatured = "<span style='color:green;'>Yes</span>";
	} elseif(cBoolString($rider->featured) == "No") {
		$strFeatured = "<span style='color:red;'>No</span>";
	}
	
	$strManage = "<a target='_blank' href='https://h2oaudio.wufoo.com/entries/m7p6p1/".$rider->wufooId."'>Wufoo</a>";	

	echo("<tr><td>".$rider->wufooId."</td>
		 <td>".$money."</td>
		 <td>".$icon."</td>
		 <td>".$facebook."</td>
		 <td>".$profile."</td>
		 <td>".$stars."</td>
		 <td><a href='profile.php?wufooid=".$rider->wufooId."'>".$rider->firstName." ".$rider->lastName."</a></td>
		 <td><a href='mailto:".$rider->email."'>".$rider->email."</a></td>
		 <td>".FormatDate($rider->joinDate)."</td>
		 <td>".$strFeatured."</td>
		 <td>".$strApproved."</td>
		 <td>".$strOpt."</td>
		 <td>".$strProForm."</td>
		 <td>".$strManage."</td></tr>");
}

function writeProFormEntries($arrEntries) {
	foreach($arrEntries as $o => $entry){
		
		$athleteRS = getProFormRS($entry->EntryId);
		if($athleteRS->EOF) {
			insertProForm($entry);
		}
		
		if($athleteRS->fields["comments"] != "") {
			$icon = "<img width='18' height='18' alt='This athlete has comments.' src='/images/comment.png' />";
		} else {
			$icon = "";
		}
		
		if(cBoolString($athleteRS->fields["approved"]) == "Yes"){
			$strApproved = "<span style='color:green;'>Yes</span>";
		} elseif(cBoolString($athleteRS->fields["approved"]) == "No") {
			$strApproved = "<span style='color:red;'>No</span>";
		} else {
			$strApproved = "Not Yet";
		}
		
		$strManage = "<a target='_blank' href='https://h2oaudio.wufoo.com/entries/m7p6q5/".$entry->EntryId."'>Wufoo</a>";	

		echo("<tr><td>".$entry->EntryId."</td>
			 <td>".$icon."</td>
			 <td><a href='profile.php?wufooid=".$entry->EntryId."'>".$entry->Field2." ".$entry->Field3."</a></td>
			 <td><a href='mailto:".$entry->Field4."'>".$entry->Field4."</a></td>
			 <td>".$entry->Field6."</td>
			 <td>".FormatDate($entry->DateCreated)."</td>
			 <td>".$strApproved."</td>
			 <td>".$strManage."</td></tr>");
	}
}

/*
  This prints the Wufoo Array so you can see all the fields easily
*/
function print_a($subject){
	echo str_replace("=>","&#8658;",str_replace("Array","<font color=\"red\"><b>Array</b></font>",nl2br(str_replace(" "," &nbsp; ",print_r($subject,true)))) . '<br />');
}

function getClean($strVar) {
	if(empty($_GET)) {
		return "";
	} else {
		if(isset($_GET[$strVar])) {
			return $_GET[$strVar];   
		} else {
			return "";
		}
	}	
}

function getPost($strVar) {
	if(empty($_POST)) {
		return "";
	} else {
		if(isset($_POST[$strVar])) {
			return $_POST[$strVar];   
		} else {
			return "";
		}
	}	
}
	
function checkPage($strToCheck) {
	if($strToCheck == "") {
		return "0";
	} else {
		$num = (int)$strToCheck;
		if($num < 0) {
			return "0";	
		} else {
			return $strToCheck;
		}
	}
}

function getAthleteRS($wufoo_id) {
	return dbExecute("SELECT * FROM team_riders WHERE wufoo_id = ".$wufoo_id);
}

function getAthleteByRiderIDRS($rider_id) {
	return dbExecute("SELECT * FROM team_riders WHERE rider_id = ".$rider_id);
}

function getAthleteRSbyEmail($strEmail) {
	return dbExecute("SELECT * FROM team_riders WHERE email = '".$strEmail."' AND Deleted != 1");
}

function getProFormRS($wufoo_id) {
	return dbExecute("SELECT * FROM pro_form WHERE wufoo_id = ".$wufoo_id);
}

function getPlaylistRS($rider_id) {
	return dbExecute("SELECT * FROM rider_playlist WHERE rider_id = ".$rider_id." ORDER BY id" );
}

function getSetupRS($rider_id) {
	return dbExecute("SELECT * FROM rider_setup WHERE rider_id = ".$rider_id." ORDER BY product_num" );
}


function getRiderSocialRS($rider_id) {
	return dbExecute("SELECT * FROM rider_social WHERE rider_id = ".$rider_id);
}


function cBoolString($bln) {
	if (is_null($bln) || $bln == "") {
		return "Not Yet";
	} else {
		if($bln==1) {
			return "Yes";
		}elseif($bln==0) {
			return "No";
		}
	}
}

function isOpted($wufooID) {
	$strSql = "SELECT opt FROM team_riders WHERE wufoo_id = ".$wufooID;
	$val = dbGetOne($strSql);
	
	if (is_null($val)) {
		return "Not Yet";
	} else {
		if($val==1) {
			return "Yes";
		}else {
			return "No";
		}
	}
}

function isProFormApproved($wufooID) {
	$strSql = "SELECT approved FROM pro_form WHERE wufoo_id = ".$wufooID;
	$val = dbGetOne($strSql);
	
	if (is_null($val)) {
		return "Not Yet";
	} else {
		if($val==1) {
			return "Yes";
		}else {
			return "No";
		}
	}
}

function isApproved($val) {
	
	if (is_null($val)) {
		return "Not Yet";
	} else {
		if($val==1) {
			return "Yes";
		}else {
			return "No";
		}
	}
}

function getVoucher($wufooID) {
	$strSql = "SELECT voucher FROM team_riders WHERE wufoo_id = ".$wufooID;
	$val = dbGetOne($strSql);
	
	if (is_null($val)) {
		return "";
	} else {
		return $val;
	}
}

function getCoupon($riderID) {
	$strSql = "SELECT coupon FROM team_riders WHERE rider_id = ".$riderID;
	$val = dbGetOne($strSql);
	
	if (is_null($val)) {
		return "";
	} else {
		return $val;
	}
}

function getProFormNextID($wufooId) 
{
	$strSql = "SELECT wufoo_id FROM pro_form WHERE wufoo_id > ".$wufooId." ORDER BY wufoo_id LIMIT 1";
	$val = dbExecute($strSql);
	
	if (is_null($val->fields["wufoo_id"])) {
		return "";
	} else {
		return $val->fields["wufoo_id"];
	}
}

function getNextID($wufooId) 
{
	$strSql = "SELECT wufoo_id FROM team_riders WHERE wufoo_id > ".$wufooId." AND deleted = 0 ORDER BY wufoo_id LIMIT 1";
	$val = dbExecute($strSql);
	
	if (is_null($val->fields["wufoo_id"])) {
		return "";
	} else {
		return $val->fields["wufoo_id"];
	}
}

function getPreviousID($wufooId) 
{
	$strSql = "SELECT wufoo_id FROM team_riders WHERE wufoo_id < ".$wufooId." AND deleted = 0  ORDER BY wufoo_id DESC  LIMIT 1";
	$val = dbExecute($strSql);
	
	if (is_null($val->fields["wufoo_id"])) {
		return "";
	} else {
		return $val->fields["wufoo_id"];
	}
}

function getNextFeaturedID($intRow, $riders) 
{
		$riders->Move($intRow + 1);
		return $riders->fields["rider_id"];
}


function getPreviousFeaturedID($intRow, $riders) 
{
	$intSearch = $intRow - 1;
	
	if($intSearch >= 0) {
		$riders->Move($intSearch);
		return $riders->fields["rider_id"];
	} else {
		return "";
	}	
}


function updateAthleteEmail($entry){
	
	$strSql = "UPDATE team_riders SET email = '".$entry->Field4."' WHERE wufoo_id = ".$entry->EntryId ;
	dbExecute($strSql);
}

function updateVoucherCodeUsed($email){
	
	$strSql = "UPDATE team_riders SET voucher_used = 1 WHERE email = '".$email."'";
	dbExecute($strSql);
}

function insertProForm($entry){
	
	$strSql = "INSERT INTO pro_form (wufoo_id, join_date) VALUES (".$entry->EntryId.",'".$entry->DateCreated."')";
	dbExecute($strSql);
}


/*
  DATABASE FUNCTIONS
*/


function getConnection() {
	$conn = NewADOConnection('mysql');
	$conn->Connect("www.x-1.com", SQL_USER, SQL_PWD, SQL_DB);

	return $conn;
}

function dbExecute($sqlStr) {
	$conn = getConnection();
	return $conn->Execute($sqlStr);
}

function dbGetOne($sqlStr) {
	$conn = getConnection();
	return $conn->GetOne($sqlStr);
}

function dbGetRow($sqlStr) {
	$conn = getConnection();
	return $conn->GetRow($sqlStr);
}

function dbGetArray($sqlStr) {
	$conn = getConnection();
	$rs = $conn->Execute($sqlStr);

	return $rs->GetArray();
}

function FormatDate($strDate) {
	if($strDate != "") {
		$time = strtotime($strDate);
		return date("m-d-Y", $time);
	} else {
		return "";
	}
				
}

/*
  EMAIL FUNCTIONS
*/


/*
  Sends emails to Riders
*/
function SendEmail($strSubject, $strBody, $strToEmail, $strName) {
	
	$mail             = new PHPMailer();
	//$body             = file_get_contents('contents.html');
	//$body             = eregi_replace("[\]",'',$body);
	
	$mail->IsSMTP(); // telling the class to use SMTP
	$mail->SMTPAuth   = true;                  // enable SMTP authentication
	$mail->SMTPSecure = "ssl";
	$mail->Host       = "smtp.gmail.com"; // sets the SMTP server
	$mail->Port       = 465;                    // set the SMTP port for the GMAIL server
	$mail->Username   = EMAIL_FROM; // SMTP account username
	$mail->Password   = EMAIL_PWD;        // SMTP account password
	
	$mail->SetFrom("team@x-1.com", "Team X-1");
	
	$mail->AddReplyTo("team@x-1.com","Team X-1");
	
	$mail->Subject    = $strSubject;
	
	//$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
	
	$mail->MsgHTML($strBody);
	
	$mail->AddAddress($strToEmail, $strName);
	
	//$mail->AddAttachment("images/phpmailer.gif");      // attachment
	//$mail->AddAttachment("images/phpmailer_mini.gif"); // attachment
	
	if(!$mail->Send()) {
	  return " - Mailer Error: " . $mail->ErrorInfo;
	} else {
	  return " (Email Sent!)";
	}

}

function SendProFormEmail($strSubject, $strBody, $strToEmail, $strName) {
	
	$mail             = new PHPMailer();
	//$body             = file_get_contents('contents.html');
	//$body             = eregi_replace("[\]",'',$body);
	
	$mail->IsSMTP(); // telling the class to use SMTP
	$mail->SMTPAuth   = true;                  // enable SMTP authentication
	$mail->SMTPSecure = "ssl";
	$mail->Host       = "smtp.gmail.com"; // sets the SMTP server
	$mail->Port       = 465;                    // set the SMTP port for the GMAIL server
	$mail->Username   = EMAIL_FROM; // SMTP account username
	$mail->Password   = EMAIL_PWD;        // SMTP account password
	
	$mail->SetFrom("proform@x-1.com", "X-1 ProForm");
	
	$mail->AddReplyTo("proform@x-1.com","X-1 ProForm");
	
	$mail->Subject    = $strSubject;
	
	//$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
	
	$mail->MsgHTML($strBody);
	
	$mail->AddAddress($strToEmail, $strName);
	
	//$mail->AddAttachment("images/phpmailer.gif");      // attachment
	//$mail->AddAttachment("images/phpmailer_mini.gif"); // attachment
	
	if(!$mail->Send()) {
	  return " - Mailer Error: " . $mail->ErrorInfo;
	} else {
	  return " (Email Sent!)";
	}

}

function GetAcceptEmail($wufooId) {
	
	$rider = new Rider();
	$rider->loadById($wufooId);
	
	$strName = $rider->firstName." ".$rider->lastName;
	$strEmail = $rider->email;
	
	$strBody = "Hello ".$strName.",<br> 
	We really liked your application, so much so that we'd like to offer you a spot on Team X-1! As a team athlete, you'll get a one-time $75 shopping spree at x-1.com and special pro pricing (50% off!) for any gear you'd like to purchase after that.*  In exchange for hooking you up, we ask that you meet the athlete requirements, stay in good communication with us and spread the waterproof word!<br><br>
	To take the next step, follow the link below. You'll get the details on what's required of team athletes and decide if you agree to the terms. If you do decide to opt in, you'll get your official login and be on your way, or if you decide our program is not for you, then no hard feelings.<br><br>
<a href='http://www.x-1.com/team/optin.php?riderid=".$wufooId."'>http://www.x-1.com/team/optin.php?riderid=".$wufooId."</a>
<br><br>
See ya in the water!<br>

X-1 Team Manager<br>
team@x-1.com<br>
www.x-1.com<br>
<br>
*Shopping spree and pro pricing good on X-1 branded products only.
";

	return $strBody;
}

//Email for Riders only getting pro form
function GetRiderProFormEmail($wufooId, $strPassword) {
	
	$arrEntries = GetWufooEntry($wufooId);
	
	foreach($arrEntries as $o => $entry){
		$strName = $entry->Field2." ".$entry->Field3;
		$strEmail = $entry->Field4;
	}
	
	$strBody = "Hello ".$strName.",<br> 
	Thanks for applying to be a part of Team X-1. We had a huge response to the number of people who filled out the application this quarter. Unfortunately, since there were so many people and only a 30 slots you just missed the cut for the team. There are many factors we use to determine who makes the team each quarter. Sometimes, it can be as simple as having too many people in your sport or age group currently in the program. That said, we really liked your application and would still like to offer you access to our 50% off Pro Pricing. Just go to www.x-1.com and click the \"Log In\" link at the top of the page. Use the log in info below and you'll see all the X-1 products 50% off.
<br>
<h3>Username:".$strEmail."<br>Password:".$strPassword."</h3>
Thanks for your interest in Team X-1 and we'll see ya in the water!<br>

X-1 Team Manager<br>
team@x-1.com<br>
www.x-1.com<br>
";
	return $strBody;
}

function GetRejectEmail($wufooId) {
	$arrEntries = GetWufooEntry($wufooId);
	
	foreach($arrEntries as $o => $entry){
		$strName = $entry->Field2." ".$entry->Field3;
		$strEmail = $entry->Field4;
	}
	
	$strBody = "Hello ".$strName.",<br> 
	Thanks for applying to be a part of Team X-1. Unfortunately, we weren't able to accept you on to the team this quarter. This wasn't any easy choice. We've had hundreds of qualified athletes apply so choosing just 30 people has been quite difficult. 
	There are many factors that we use to determine who makes the team each quarter. Denial can be based on an incomplete application (no links, no answers, etc), too many people in your sport or age group currently on the team, or simply becuase we there were so many people applying you may have just missed the cut. 
	We are going to be opening up the application process again in January so if you are still interested, please apply again.<br><br>
Thanks again for your interest in Team X-1 and best of luck to you.
<br><br>
Best,<br>
X-1 Team Manager<br>
www.x-1.com<br>
";

	return $strBody;
}

function GetProFormAcceptEmail($wufooId, $strPassword) {
	
	$arrEntries = GetProFormEntry($wufooId);
	
	foreach($arrEntries as $o => $entry){
		$strName = $entry->Field2." ".$entry->Field3;
		$strEmail = $entry->Field4;
	}

	$strBody = "Hello ".$strName.",<br> 
	Thanks for filling out the X-1 Pro Form. Your application has been accepted and you can now log in to the site and see your special pricing on all X-1 products. Just go to x-1.com and click the \"Log In\" link at the top of the page. Use the log in info below and you should be all set.
<br>
<h3>Username:".$strEmail."<br>Password:".$strPassword."</h3>
See ya in the water!<br>
X-1<br>
www.x-1.com<br>
";

	return $strBody;
}


function GetProFormRejectEmail($wufooId) {
	$arrEntries = GetProFormEntry($wufooId);
	
	foreach($arrEntries as $o => $entry){
		$strName = $entry->Field2." ".$entry->Field3;
		$strEmail = $entry->Field4;
	}
	
	$strBody = "Hello ".$strName.",<br> 
	Thanks for applying for the X-1 Pro Form. Unfortunately your application was not approved.
	<br><br>
	Best,<br>
	X-1<br>
	www.x-1.com<br>
	";

	return $strBody;
}


/*
Mailchimp Newsletter Code
*/
function addToNewsletter($strEmail, $strFirstName, $strLastName) {

	$api = new MCAPI(MAILCHIMP_API);
	$list_id = "f79a71d601";
	
	$batch[] = array('EMAIL'=>$strEmail, 'FNAME'=>$strFirstName, 'LNAME'=>$strLastName);

	if($api->listBatchSubscribe($list_id, $batch, false) === true) {
		// It worked!	
		return 'Success!';
	}else{
		// An error ocurred, return error message	
		return 'Error: ' . $api->errorMessage;
	}
}

function removeFromNewsletter($strEmail) {

	$api = new MCAPI(MAILCHIMP_API);
	$list_id = "f79a71d601";
	
	$batch[] = array('EMAIL'=>$strEmail);

	if($api->listBatchUnsubscribe($list_id, $batch, true, false, false) === true) {
		// It worked!	
		return 'Success!';
	}else{
		// An error ocurred, return error message	
		return 'Error: ' . $api->errorMessage;
	}
}

/*
  COUPON FUNCTIONS
*/

function BuildCouponCode($wufoo_id) {
				
	$rider = new Rider();
	$rider->loadById($wufoo_id);
	
	//FIRST we delete the old code just in case
	$oldCode = getCoupon($rider->riderId);
	if(!$oldCode == "") {
		deleteCouponCode($oldCode);
	}
	
	$strName = trim(str_replace("'", "", $rider->firstName)).trim(str_replace("'", "", $rider->lastName));
	
	try {
		$strCoupon = cloneCouponCode(strtoupper($strName)."20", 10291);
		
		if(!strstr($strCoupon, "Error")) {
			
			dbExecute("UPDATE team_riders SET coupon = '".$strCoupon."' WHERE rider_id = ".$rider->riderId);
		}
		
		return $strCoupon;
				
	} catch(Exception $e) {
		return "Error. Code Not Create Coupon Code";
	}

}


function BuildVoucherCode($wufoo_id) {
	
	//FIRST we delete the old code just in case
	$oldCode = getVoucher($wufoo_id);
	if(!$oldCode == "") {
		deleteCouponCode($oldCode);
	}
	
	$strCoupon = createComplexCouponCode("RIDER");
	
	try {
		$strCoupon = cloneCouponCode($strCoupon, 3647);
		
		if(!strstr($strCoupon, "Error")) {
			
			dbExecute("UPDATE team_riders SET voucher = '".$strCoupon."' WHERE wufoo_id = ".$wufoo_id);
		}
		
		return $strCoupon;
				
	} catch(Exception $e) {
		return "Error. Code Not Create Voucher Code";
	}

}

function deleteCouponCode($strCouponCode) {
		Mage::app('h2oaudio', 'website');
		try
        {
            $coupon = Mage::getModel('salesrule/rule')->load($strCouponCode, 'coupon_code');
            if ($coupon || $coupon->getId())
            {
                $coupon->delete();
				return true;
            }
        }
        catch (Exception $e)
        {
			return false;
		}
	}

function cloneCouponCode($strCouponCode, $intMasterCoupon) {
	Mage::app('h2oaudio', 'website');
	
	//Switch out the ID to get the coupon you are going to clone
	$masterCoupon = Mage::getModel('salesrule/rule')->load($intMasterCoupon);
	if (!$masterCoupon || ! $masterCoupon->getId())
	{
		return 'Error: Could Not Load Master Coupon';
	}

	try
	{
		$coupon = Mage::getModel('salesrule/rule')->load($strCouponCode, 'coupon_code');
		if (!$coupon || ! $coupon->getId())
		{
			$coupon = $masterCoupon;

			$coupon->setId(null);
			$coupon->setCouponCode($strCouponCode);
			
			$couponNameTemplate = $strCouponCode;
			
			if (empty($couponNameTemplate))
			{
				$coupon->setName($coupon->getCouponCode());
			}
			else
			{
				$coupon->setName(sprintf($couponNameTemplate, $coupon->getCouponCode()));
			}
			//this creates the new code. Hide it when not in use.
			$coupon->save();

			return $strCouponCode;
		}
	}
	catch (Exception $e)
	{
		echo 'Error:', $e->$getMessage(), '<br/>';
	}
}

//function to create 8 digit alphanumeric coupon code
function createComplexCouponCode($strPrefx) {
	$chars = "ABCDEFGHIJKLMNPQRSTUVWXYZ123456789";
	srand((double)microtime()*1000000);
	$i = 0;
	$code = '' ;
	while ($i <= 4)
	{
		$num = rand() % 33;
		$tmp = substr($chars, $num, 1);
		$code = $code . $tmp;
		$i++;
	}
	return $strPrefx."-".$code;
}

function CreatePassword ($length = 8) {
	$password = "";
	$possible = "2346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ";
	$maxlength = strlen($possible);
	
	// check for length overflow and truncate if necessary
	if ($length > $maxlength) {
	  $length = $maxlength;
	}
	
	$i = 0; 
	while ($i < $length) { 
		$char = substr($possible, mt_rand(0, $maxlength-1), 1);
		
		if (!strstr($password, $char)) { 
		$password .= $char;
		$i++;
		}
	}
	return $password;
}

function CreateCustomer($arrEntries, $strTable, $intGroupID = 5) {
	Mage::app('h2oaudio', 'website');

	foreach($arrEntries as $o => $entry){
		$firstname = $entry->Field2;
		$lastname = $entry->Field3;
		$email = $entry->Field4;
		$wufooID = $entry->EntryId;
		$password = CreatePassword();
	}

	// Website and Store details
	$websiteId = Mage::app()->getWebsite()->getId();
	$store = Mage::app()->getStore();
	
	$customer = Mage::getModel("customer/customer");
	$customer->website_id = $websiteId;
	$customer->setStore($store);
	
	try {
		// If new, save customer information
		$customer->firstname = $firstname;
		$customer->lastname = $lastname;
		$customer->email = $email;
		$customer->password_hash = md5($password);
		$customer->group_id = $intGroupID;
		if($customer->save()){
			//SaveMagentoID($wufooID, $customer->getId(), $strTable);
			return $password;
		}else{
			return "An error occured while creating account.";
		}
		
	}catch(Exception $e){
		
		// If customer already exists, initiate login
		if(preg_match('/Customer email already exists/', $e)){
			$customer->loadByEmail($email);
			$customer->group_id = 5;
			
			if($customer->save()){
				//SaveMagentoID($wufooID, $customer->getId(), $strTable);
				return "It looks like you already have an account created. You can log in with the same information.";
			}else{
				return "An error occured while creating account.";
			}
		}
	}
}

function getProductArray() {

	$array = array(
    "INT4-BK-X" => "Interval Waterproof Headphones System",
    "MM-AB1-BK" => "Momentum Sport Armband - Black",
    "MM-AB1-PE" => "Women's Momentum Sport Armband - Gray/Purple",
    "MM-AB1-PK" => "Women's Momentum Sport Armband - Black/Pink",
    "MM-EB1-BK" => "Momentum Ear Bud",
    "MM-IE1-BK" => "Momentum Ultra Light Headphones - Black",
    "MM-IE1-CN" => "Women's Momentum Ultra Light Headphones - Cyan",
    "MM-IE1-PE" => "Women's Momentum Ultra Light Headphones - Purple",
    "MM-IE1-PK" => "Women's Momentum Ultra Light Headphones - Pink",
    "MM-IE1-TL" => "Women's Momentum Ultra Light Headphones - Teal",
    "MM-IE1-WE" => "Women's Momentum Ultra Light Headphones - White",
    "MM-SP1-BK" => "Momentum Sport Headphones - Black",
    "MM-SP1-PK" => "Women's Momentum Sport Headphones - Pink",
    "MM-SP1-PE" => "Women's Momentum Sport Headphones - Purple",
    "MM-SP1-TL" => "Women's Momentum Sport Headphones - Teal",
    "MM-CM1-BK" => "Momentum Custom Headphones - Black",
    "MM-CM1-PK" => "Women's Momentum Custom Headphones - Magenta",
    "SG-MN1-BK" => "Surge Mini Waterproof Sport Headphone",
    "IE2-MBK-X" => "Surge Contact Waterproof Headset",
    "IEN2-BK-X" => "Surge Sportwrap Waterproof Headphones",
    "CB1-BK-X" => "Flex All Sport Waterproof Headphones - Onyx Black",
    "CB1-PK-X" => "Flex All Sport Waterproof Headphones - Power Pink",
    "CB1-RB-X" => "Flex All Sport Waterproof Headphones - Super Hero Blue",
    "TR1-BK-X" => "Trax Custom Fit Sport Headphones - Black",
    "TR1-GN-X" => "Trax Custom Fit Sport Headphones - Green",
    "XB1-BK-X" => "Amphibx Fit Waterproof Armband for Smartphones"
	);
	
	return $array;
}

function getRiderProducts($riderID) {
	
	$strSql = "SELECT product, product_num FROM rider_setup WHERE rider_id = ".$riderID;
	$rs = dbExecute($strSql);
	
	return $rs;
}

function getRiderPhotos($riderID) {
	
	$path = realpath('../team/files/'.$riderID);

	$string = "";
	foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)) as $filename) 
	{
		$path_parts = pathinfo($filename);
	 	
	 	if (substr($path_parts['filename'], 0, 3) === 'tn_') {
	 		$string .= "<div class='profile_img_lib'><div class='profile_thumb'>".
	 		"<img title='Make This Your Featured Profile Image' class='featureImage' id='/team/files/".$riderID."/pf_".substr($path_parts['basename'], 3)."' src='/team/files/".$riderID."/".$path_parts['basename']."' />".
	 		"</div><br/><a style='cursor:pointer;cursor:hand;' onClick='if(confirm(\"Are you sure you want to Delete this?\")) {window.location=\"process.php?action=delete_profile_image&rider_id=".$riderID."&img=".substr($path_parts['basename'], 3)."\";}'><img class='profile_img_delete' src='/images/team/delete_16.png' /></a></div>"; 
	 	}
	}
	
	return $string;
}

function getRiderThumbnails($riderID, $featured_photo) {
	
	$path = realpath('../team/files/'.$riderID);

	$string = "";
	foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)) as $filename) 
	{
		$path_parts = pathinfo($filename);
	 	
	 	if (substr($path_parts['filename'], 0, 3) === 'tn_') {
	 			$string .= "<div class='profile_img_lib'><div class='profile_thumb'>".
	 		"<img class='publicFeatureImage' id='/team/files/".$riderID."/pf_".substr($path_parts['basename'], 3)."' src='/team/files/".$riderID."/".$path_parts['basename']."' />".
	 		"</div></div>"; 
	 		
	 	}
	}
	
	return $string;
}

function GetEmailDuplicates($strEmail) {
	$strSql = "SELECT * FROM team_riders WHERE email = '".$strEmail."'";
	$rs = dbExecute($strSql);
	
	if($rs->RecordCount() > 1) {
			
		echo "<div style='padding:10px;font-size:11px;'><b>Duplicate Emails Found: ".$rs->RecordCount()."</b>";
		
         while (!$rs->EOF) {
			$rider = new Rider();
			$rider->loadByRiderId($rs->fields["rider_id"]);
			
			echo "<br>".$rider->joinDate. " Approved: ".cBoolString($rider->approved)." Opted: ".cBoolString($rider->opt)." Deleted: ".cBoolString($rider->deleted)."<br>";
			
			$rs->MoveNext();
		}
		
		echo "</div>";
		
	} else {
		echo "";
	}
}

function getRiderFeaturedPhoto($riderID) {
	
	$strSql = "SELECT file FROM rider_photos WHERE rider_id = ".$riderID." AND featured = 1";
	$rs = dbExecute($strSql);
	
	return $rs;
}

function selfURL() {
	$s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
	
	$protocol = strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s; 
	
	$port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]); 
	
	return $protocol."://".$_SERVER['SERVER_NAME'].$port.$_SERVER['REQUEST_URI']; 
} 

function strleft($s1, $s2) { 
	return substr($s1, 0, strpos($s1, $s2)); 
}

function remove_querystring_var($url, $key) { 
  $url = preg_replace('/(.*)(?|&)' . $key . '=[^&]+?(&)(.*)/i', '$1$2$4', $url . '&'); 
  $url = substr($url, 0, -1); 
  return $url; 
}

function SaveMagentoID($wufooID, $intID, $strTable) {
		dbExecute("UPDATE ".$strTable." SET magento_id = ".$intID." WHERE wufoo_id = ".$wufooID);
}

function formatPhone($num)
{
    $num = ereg_replace('[^0-9]', '', $num);

    $len = strlen($num);
    if($len == 7)
        $num = preg_replace('/([0-9]{3})([0-9]{4})/', '$1-$2', $num);
    elseif($len == 10)
        $num = preg_replace('/([0-9]{3})([0-9]{3})([0-9]{4})/', '($1) $2-$3', $num);

    return $num;
}

function encrypt($str, $key)
{
	# Add PKCS7 padding.
	$block = mcrypt_get_block_size('des', 'ecb');
	if (($pad = $block - (strlen($str) % $block)) < $block) {
	  $str .= str_repeat(chr($pad), $pad);
	}

	return mcrypt_encrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB);
}

function decrypt($str, $key)
{
	$str = mcrypt_decrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB);

	# Strip padding out.
	$block = mcrypt_get_block_size('des', 'ecb');
	$pad = ord($str[($len = strlen($str)) - 1]);
	if ($pad && $pad < $block && preg_match(
		  '/' . chr($pad) . '{' . $pad . '}$/', $str
											)
	   ) {
	  return substr($str, 0, strlen($str) - $pad);
	}
	return $str;
}

class Rider {

	var $riderId;
	var $wufooId;
	var $email;
	var $firstName;
	var $lastName;
	var $phone;
	var $address1;
	var $address2;
	var $city;
	var $state;
	var $zip;
	var $country;
	var $dob;
	var $age;
	var $gender;
	var $sports;
	var $web;
	var $opt;
	var $joinDate;
	var $voucher;
	var $voucherUsed;
	var $coupon;
	var $approved;
	var $deleted;
	var $featured;
	var $proformOnly;
	var $comments;
	var $magentoId;
	var $level = "1";
	var $rider_desc;
	var $website;
	var $facebook;
	var $twitter;
	var $video;
	var $facebook_group;
	var $achieve;
	
	function __construct()
  	{
  	}
	
	// set user's first name

	public function setEmail($email){$this->email = $email;}
	public function getEmail(){return $this->email;}
	
	public function setFirstName($firstName){$this->firstName = $firstName;}
	public function getFirstName(){return $this->firstName;}
	
	public function setLastName($lastName){$this->lastName = $lastName;}
	public function getLastName(){return $this->lastName;}
	
	public function getPlaylist() {
		$playlistRS = getPlaylistRS($this->riderId);
		
		return $playlistRS;
	}
	
	public function getSetup() {
		$setupRS = getSetupRS($this->riderId);
		
		return $setupRS;
	}

	public function loadById($wufooId) {
		$athleteRS = getAthleteRS($wufooId);
		
		$this->load($athleteRS);
	}
	
	public function loadByRiderId($riderID) {
		$athleteRS = getAthleteByRiderIDRS($riderID);
		
		$this->load($athleteRS);
	}
	
	public function loadByEmail($strEmail) {
		$athleteRS = getAthleteRSbyEmail($strEmail);
		
		$this->load($athleteRS);
	}

	public function load($athleteRS) {
		
		$this->riderId = $athleteRS->fields["rider_id"];
		$this->wufooId = $athleteRS->fields["wufoo_id"];
		$this->email = $athleteRS->fields["email"];
		$this->firstName = $athleteRS->fields["firstname"];
		$this->lastName = $athleteRS->fields["lastname"];
		$this->phone = $athleteRS->fields["phone"];
		$this->address1 = $athleteRS->fields["address1"];
		$this->address2 = $athleteRS->fields["address2"];
		$this->city = $athleteRS->fields["city"];
		$this->state = $athleteRS->fields["state"];
		$this->country = $athleteRS->fields["country"];
		$this->zip = $athleteRS->fields["zip"];
		$this->age = $athleteRS->fields["age"];
		$this->gender = $athleteRS->fields["gender"];
		$this->sports = $athleteRS->fields["sports"];
		$this->web = $athleteRS->fields["web"];
		$this->opt = $athleteRS->fields["opt"];
		$this->joinDate = $athleteRS->fields["join_date"];
		$this->voucher = $athleteRS->fields["voucher"];
		$this->voucherUsed = $athleteRS->fields["voucher_used"];
		$this->coupon = $athleteRS->fields["coupon"];
		$this->approved = $athleteRS->fields["approved"];
		$this->deleted = $athleteRS->fields["deleted"];
		$this->proformOnly = $athleteRS->fields["proform_only"];
		$this->comments = $athleteRS->fields["comments"];
		$this->magentoId = $athleteRS->fields["magento_id"];
		$this->level = $athleteRS->fields["level"];
		$this->rider_desc = $athleteRS->fields["rider_desc"];
		$this->featured = $athleteRS->fields["featured"];
		$this->facebook_group = $athleteRS->fields["facebook_group"];
		$this->achieve = $athleteRS->fields["achieve"];
		
		$socialRS = getRiderSocialRS($this->riderId);
		$this->website = $socialRS->fields["website"];
		$this->facebook = $socialRS->fields["facebook"];
		$this->twitter = $socialRS->fields["twitter"];
		$this->video = $socialRS->fields["video"];
		
	}

	function insert($entry){
	
		$strSql = "INSERT INTO team_riders (wufoo_id, join_date, email, firstname, lastname, phone, address1, address2, city, country, zip, state, age, gender, sports, web) 
		VALUES (".$entry->EntryId.",
				'".$entry->DateCreated."',
				'".$entry->Field4."',
				'".str_replace("'","''",$entry->Field2)."',
				'".str_replace("'","''",$entry->Field3)."',
				'".$entry->Field5."',
				'".str_replace("'","''",$entry->Field6)."',
				'".str_replace("'","''",$entry->Field7)."',
				'".str_replace("'","''",$entry->Field8)."',
				'".str_replace("'","''",$entry->Field11)."',
				'".$entry->Field10."',
				'".$entry->Field9."',
				'".$entry->Field14."',
				'".$entry->Field16."',
				'".str_replace("'","''",$entry->Field18)."',
				'".str_replace("'","''",$entry->Field23)."')";
		
		
		//echo "SQL: ".$strSql."<br>";
		dbExecute($strSql);
	}

	function update($entry){
		
		$strSql = "UPDATE team_riders SET 
		email = '".$entry->Field4."',
		firstname = '".str_replace("'","''",$entry->Field2)."',
		lastname = '".str_replace("'","''",$entry->Field3)."',
		phone = '".$entry->Field5."',
		address1 = '".str_replace("'","''",$entry->Field6)."',
		address2 = '".str_replace("'","''",$entry->Field7)."',
		city = '".str_replace("'","''",$entry->Field8)."',
		country = '".str_replace("'","''",$entry->Field11)."',
		zip = '".$entry->Field10."',
		state = '".$entry->Field9."',
		age = '".$entry->Field14."',
		gender = '".$entry->Field16."',
		sports = '".str_replace("'","''",$entry->Field18)."',
		web = '".str_replace("'","''",$entry->Field23)."'
		WHERE wufoo_id = ".$entry->EntryId;
	
		dbExecute($strSql);
	}
	
	function save(){
		
		$strSql = "UPDATE team_riders SET 
		email = '".$this->email."',
		firstname = '".str_replace("'","''",$this->firstName)."',
		lastname = '".str_replace("'","''",$this->lastName)."',
		phone = '".$this->phone."',
		address1 = '".str_replace("'","''",$this->address1)."',
		address2 = '".str_replace("'","''",$this->address2)."',
		city = '".str_replace("'","''",$this->city)."',
		country = '".str_replace("'","''",$this->country)."',
		zip = '".$this->zip."',
		state = '".$this->state."',
		age = '".$this->age."',
		gender = '".$this->gender."',
		sports = '".str_replace("'","''",$this->sports)."',
		web = '".str_replace("'","''",$this->web)."',
		magento_id = '".$this->magentoId."'
		WHERE wufoo_id = ".$this->wufooId;
		
		dbExecute($strSql);
	}
	
	function delete(){
		
		$strSql = "UPDATE team_riders SET 
		deleted = 1 WHERE wufoo_id = ".$this->wufooId;
		
		dbExecute($strSql);
	}

}



?>