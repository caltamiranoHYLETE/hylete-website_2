<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

function checkAccount() {
	if ($_SERVER["REQUEST_METHOD"] == "POST") {

		$orderId = "";
		if(!empty($_POST["orderId"]))
		{
			$orderId = test_input($_POST["orderId"]);
		}

		$email = "";
		if(!empty($_POST["email"]))
		{
			$email = test_input($_POST["email"]);
		}

		$comments = "";
		if(!empty($_POST["comments"]))
		{
			$comments = test_input($_POST["comments"]);
		}

		$firstName = "";
		if(!empty($_POST["firstName"]))
		{
			$firstName = test_input($_POST["firstName"]);
		}

		$lastName = "";
		if(!empty($_POST["lastName"]))
		{
			$lastName = test_input($_POST["lastName"]);
		}

    	/* Set your parameters for the request */
    	$params = array(
    		"orderId" => $orderId, "email" => $email, "comments"=>$comments, "firstName"=>$firstName, "lastName"=>$lastName
    	);

    	require '../lib/nusoap/nusoap.php';
		$config = include('../config.php');
		$client = new nusoap_client($config['baseUrl'], 'WSDL');
    	$client->timeout = 200;
    	$client->response_timeout = 600;
		$client->setHeaders("<AuthHeader xmlns=\"http://tempuri.org/\"><UserName>".$config['username']."</UserName><Password>".$config['token']."</Password></AuthHeader>");

    	$jsonReturn = $client->call('QueueReturnSupportEmail', array($params), '', '', false, true);
    	
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
	checkAccount();
} catch(Exception $e) {
	
	$data = array();
	$data['success'] = false;
    $data['message'] = $e->getMessage();

    echo json_encode($data);
}


?>