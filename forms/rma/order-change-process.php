<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

function getOrderLocation() {
	if ($_SERVER["REQUEST_METHOD"] == "POST") {

		$orderId = "";
    	if(!empty($_POST["orderId"])) 
    	{
    	  $orderId = test_input($_POST["orderId"]);

			//if this is an amazon order we can add dashes
			//11386976547125015 == 113-8697654-7125015
			if(strlen($orderId) == 17) {
				$str = substr($orderId,0,3);
				$str = $str."-";
				$str = $str.substr($orderId,3,7);
				$str = $str."-";
				$str = $str.substr($orderId,10,7);

				$orderId = $str;
			}
    	}

		$email = "";
		if(!empty($_POST["email"]))
		{
			$email = test_input($_POST["email"]);
		}

    	/* Set your parameters for the request */
    	$params = array(
    		"orderId" => $orderId, "email" => $email
    	);
    
    	require '../lib/nusoap/nusoap.php';
		$config = include('../config.php');
		$client = new nusoap_client($config['baseUrl'], 'WSDL');
    	$client->timeout = 200;
    	$client->response_timeout = 600;
		$client->setHeaders("<AuthHeader xmlns=\"http://tempuri.org/\"><UserName>".$config['username']."</UserName><Password>".$config['token']."</Password></AuthHeader>");

    	$jsonReturn = $client->call('GetOrderChangeData', array($params), '', '', false, true);
    	
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
	getOrderLocation();
} catch(Exception $e) {
	
	$data = array();
	$data['success'] = false;
    $data['message'] = $e->getMessage();

    echo json_encode($jsonReturn);
}


?>