<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

function getTableData() {
	if ($_SERVER["REQUEST_METHOD"] == "POST") {

		$orderId = "";
		if(!empty($_POST["orderId"]))
		{
			$orderId = test_input($_POST["orderId"]);
		}

		$ignoreClearance = "";
		if(!empty($_POST["ignoreClearance"]))
		{
			$ignoreClearance = test_input($_POST["ignoreClearance"]);
		}

		$isAdmin = "";
		if(!empty($_POST["isAdmin"]))
		{
			$isAdmin = test_input($_POST["isAdmin"]);
		}

    	/* Set your parameters for the request */
    	$params = array(
    		"orderId" => $orderId, "ignoreClearance" => $ignoreClearance, "isAdmin"=>$isAdmin
    	);

    	require '../lib/nusoap/nusoap.php';
		$config = include('../config.php');
		$client = new nusoap_client($config['baseUrl'], 'WSDL');
    	$client->timeout = 200;
    	$client->response_timeout = 600;
		$client->setHeaders("<AuthHeader xmlns=\"http://tempuri.org/\"><UserName>".$config['username']."</UserName><Password>".$config['token']."</Password></AuthHeader>");

    	$jsonReturn = $client->call('GetReturnProductTable', array($params), '', '', false, true);
    	
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
	getTableData();
} catch(Exception $e) {
	
	$data = array();
	$data['success'] = false;
    $data['message'] = $e->getMessage();

    echo json_encode($data);
}


?>