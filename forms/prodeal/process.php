<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

class PBHMember {
    var $firstName;
	var $lastName;
	var $email;
	var $password;
	var $groupID = "5";
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
        $config = include('../config.php');
        $client = new nusoap_client($config['baseUrl'], 'WSDL');
    	$client->timeout = 200;
    	$client->response_timeout = 600;
		$client->setHeaders("<AuthHeader xmlns=\"http://tempuri.org/\"><UserName>".$config['username']."</UserName><Password>".$config['token']."</Password></AuthHeader>");
    	
    	$error = $client->getError();
    	if ($error) {
    	    $data = array();
            $data['success'] = false;
            $data['message'] = $error;
            
            echo json_encode($data);
    	}
    	
    	$jsonReturn = $client->call('CreateProDealMember', array($params), '', '', false, true);
    	
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

    echo json_encode($data);
}

?>