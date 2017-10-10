<?php
$xml = simplexml_load_file("config.xml");

$queryString = decryptQueryString($_SERVER["QUERY_STRING"], "crNwgmQtv0");

//echo "QS: ".$queryString."<br>";
//echo "QSL: ".strLen($queryString);

if(strLen($queryString) < 50) {
    $queryString = $_SERVER["QUERY_STRING"];
}

//Set defaults
$strId = "";
$email = "";
$firstName = "";
$lastName = "";
$eventId = "";
$athleteId = "";
$eventName = "";
$error = "";
$showStandard = true;

//first let's see if we can get the query string unencrypted
parse_str($queryString, $output);
if(isset($output)) {
	
	if(array_key_exists ("id", $output)) {
		$strId = $output['id'];
	}
	
	if(array_key_exists ("email", $output)) {
		$email = $output['email'];
	}
	
	if(array_key_exists ("first", $output)) {
		$firstName = $output['first'];
	}
	
	if(array_key_exists ("last", $output)) {
		$lastName = $output['last'];
	}
    
    if(array_key_exists ("eventId", $output)) {
        $eventId = $output['eventId'];
    }
    
    if(array_key_exists ("athleteId", $output)) {
        $athleteId = $output['athleteId'];
    }
    
    if(array_key_exists ("eventName", $output)) {
        $eventName = $output['eventName'];
    }
    
}

if($strId == "") {
	$strId = GET("id");
}

if($strId == "") {
	//we don't have an ID so we look at the URL
	//echo $_SERVER["REQUEST_URI"]."<br><br>";
	$url = strtok($_SERVER["REQUEST_URI"],'?');
	$url = strtolower(trim($url, '/'));
    
	$nodes = $xml->xpath('//partners/partner/path[.="'.$url.'"]/parent::*');
} else{
	$nodes = $xml->xpath('//partners/partner[@id="'.$strId.'"]');

    //maybe there was an ID issue
    if(empty($nodes)) {
        $url = strtok($_SERVER["REQUEST_URI"],'?');
        $url = strtolower(trim($url, '/'));
    
        $nodes = $xml->xpath('//partners/partner/path[.="'.$url.'"]/parent::*');
    }
}

if(!empty($nodes)) {
    $partner = $nodes[0];
    
    $name = $partner->name;
    $pageTitle = $partner->pageTitle;
    $banner = $partner->banner;
    $theme = $partner->theme;
    
    if($eventName == "") {
      $title = $partner->title;
    } else {
      $title = "Welcome ".$eventName." Athletes";
    }
    
    $intro = $partner->introtext;
    $step1 = $partner->step1;
    $step2 = $partner->step2;
    $step3 = $partner->step3;
    $step4 = $partner->step4;
    $step5 = $partner->step5;
    $groupId = $partner->mageGroupId;
    $netsuiteId = $partner->netsuiteId;
    $redirect = $partner->redirect;
    $mailChimpList = $partner->mailchimpList;
    $mailChimpGroupId = $partner->mailchimpGroupId;
    $mailChimpGroupName = $partner->mailchimpGroupName;
    $useCoupon = $partner->useCoupon;
    $personalCode = $partner->personalCode;
    $cloneCode = $partner->cloneCode;
    $codePrefix = $partner->codePrefix;
    $genericCode = $partner->genericCode;
    $codeName = $partner->codeName;
    $showReferral= $partner->showReferral;
    $showGym = $partner->showGym;
    $showStandard = $partner->showStandard;
    
    //Set Page Variables
    if($email == null && $email == "") {
    	$email = "email address";
    }
    
    if($firstName == null && $firstName == "") {
    	$firstName = "first name";
    }
    
    if($lastName == null && $lastName == "") {
    	$lastName = "last name";
    }
    
} else {
    $error = true;
}

?>