<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

class PBHMember {
    var $firstName;
	var $lastName;
	var $email;
	var $password;
	var $groupID;
	var $parentID;
	var $affiliateName;
    var $eventID;
    var $athleteID;
    var $eventName;

	var $gymOwner;
	var $gymName;
	var $gymMembers;
	var $gymPhone;
}

class Partner {
	var $mcListID;
    var $mcGroupID;
    var $mcGroupName;
    var $useCoupon = false;
	var $createCode = false;
	var $cloneCode;
	var $codePrefix;
	var $genericCode;
}

class AuthStruct {
    var $authID;
	var $authKey;
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

function createPartner() {
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
	
    	if(!empty($_POST["redirect"])) 
    	{
    	  $redirect = test_input($_POST["redirect"]);
    	}
    	
    	$member = new PBHMember();
    
    	if(!empty($_POST["groupID"])) 
    	{
    	  $member->groupID = test_input($_POST["groupID"]);
    	}
    	
    	if(!empty($_POST["txtFirstName"])) 
    	{
    	  $member->firstName = test_input($_POST["txtFirstName"]);
    	}
    	
    	if(!empty($_POST["txtLastName"])) 
    	{
    	  $member->lastName = test_input($_POST["txtLastName"]);
    	}
    	
    	if(!empty($_POST["txtEmail"])) 
    	{
    	  $member->email = test_input($_POST["txtEmail"]);
    	}
    	
    	if(!empty($_POST["defaultPassword"])) 
    	{
    	  $member->password = test_input($_POST["defaultPassword"]);
    	}
    	
    	if(!empty($_POST["partnerName"])) 
    	{
    	  $member->affiliateName = test_input($_POST["partnerName"]);
    	}
    	
    	if(!empty($_POST["partnerID"])) 
    	{
    	  $member->parentID = test_input($_POST["partnerID"]);
    	}

		//only if SmartReferral is turned on we trump the values from before
		if(!empty($_POST["affiliateName"]))
		{
			$member->affiliateName = test_input($_POST["affiliateName"]);
		}
		if(!empty($_POST["autocomplete2value"]))
		{
			$member->parentID = test_input($_POST["autocomplete2value"]);
		}

        if(!empty($_POST["eventID"])) 
        {
          $member->eventID = test_input($_POST["eventID"]);
        }
        
        if(!empty($_POST["eventName"])) 
        {
          $member->eventName = test_input($_POST["eventName"]);
        }
        
        if(!empty($_POST["athleteID"])) 
        {
          $member->athleteID = test_input($_POST["athleteID"]);
        }

		if(!empty($_POST["gymOwner"]))
		{
			$member->gymOwner = test_input($_POST["gymOwner"]);
		}

		if(!empty($_POST["gymName"]))
		{
			$member->gymName = test_input($_POST["gymName"]);
		}

		if(!empty($_POST["gymMembers"]))
		{
			$member->gymMembers = test_input($_POST["gymMembers"]);
		}

		if(!empty($_POST["gymPhone"]))
		{
			$member->gymPhone = test_input($_POST["gymPhone"]);
		}
    	
    	//PARTNER INFO//
    	$partner = new Partner();

		if(!empty($_POST["partnerName"]))
		{
			$partner->name = test_input($_POST["partnerName"]);
		}

    	if(!empty($_POST["mcList"])) 
    	{
    	  $partner->mcListID = test_input($_POST["mcList"]);
    	}
        
        if(!empty($_POST["mcGroupId"])) 
        {
          $partner->mcGroupID = test_input($_POST["mcGroupId"]);
        }
        
        if(!empty($_POST["mcGroupName"])) 
        {
          $partner->mcGroupName = test_input($_POST["mcGroupName"]);
        }
        
        if(!empty($_POST["useCoupon"])) 
        {
          $stringToTest = test_input($_POST["useCoupon"]);
          $partner->useCoupon = $stringToTest === 'true'? true: false;
        }
    	
    	if(!empty($_POST["createCode"])) 
    	{
    	  $stringToTest = test_input($_POST["createCode"]);
    	  $partner->createCode = $stringToTest === 'true'? true: false;
    	}
    	
    	if(!empty($_POST["cloneCode"])) 
    	{
    	  $partner->cloneCode = test_input($_POST["cloneCode"]);
    	}
    	
    	if(!empty($_POST["codePrefix"])) 
    	{
    	  $partner->codePrefix = test_input($_POST["codePrefix"]);
    	}

		if(!empty($_POST["codeName"]))
		{
			$partner->codeName = test_input($_POST["codeName"]);
		}
    	
    	if(!empty($_POST["genericCode"])) 
    	{
    	  $partner->genericCode = test_input($_POST["genericCode"]);
    	}
    
    	$authStruct = new AuthStruct();
    	$authStruct->authID = "hylete";
    	$authStruct->authKey = "349869c5-17d3-4da2-b5db-cadb7f2c611f";
    	
    	/* Set your parameters for the request */
    	$params = array(
    		"authStruct" => $authStruct,
    	  	"member" => $member,
    	  	"partner" => $partner
    	);
    
    	require '../lib/nusoap/nusoap.php';
    	//$client = new nusoap_client('http://localhost:60601/hyletePBHService.asmx?WSDL', 'WSDL');
    	$client = new nusoap_client('https://pbhservice.hylete.com/hyletePBHService.asmx?WSDL', 'WSDL');
    	$client->timeout = 200;
    	$client->response_timeout = 600;
    	
    	$error = $client->getError();
    	if ($error) {
    	    $data = array();
            $data['success'] = false;
            $data['message'] = $error;
            
            echo json_encode($data);
    	}
    	
    	$jsonReturn = $client->call('CreatePartnerMember', array($params), '', '', false, true);
    	
    	$error = $client->getError();
    	if ($error) {
    	    $data = array();
            $data['success'] = false;
            $data['message'] = $error;
            
            echo json_encode($data);
            
    	} else{
    	    echo json_encode($jsonReturn);
    	}
	}
}


try {
	createPartner();
} catch(Exception $e) {
	
	$data = array();
	$data['success'] = false;
    $data['message'] = $e->getMessage();

    echo json_encode($jsonReturn);
}


?>