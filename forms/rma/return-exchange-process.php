<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
function startsWith($haystack, $needle)
{
    return strncmp($haystack, $needle, strlen($needle)) === 0;
}

class HyleteReturn {
    var $orderId;
    var $firstName;
    var $lastName;
    var $email;
    var $phone;
    var $address1;
    var $address2;
    var $city;
    var $state;
    var $postalCode;
    var $notes;
    var $returnItems = array();
	var $isExchange;
	var $isWarranty;
    var $isCreditMemo;
	var $isAdmin;
}

class HyleteReturnItem {
    var $sku;
    var $qty;
    var $returnReason;
	var $warrantyReason;
    var $replacementSku;
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function createReturn() {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $return = new HyleteReturn();

        if(!empty($_POST["refund-or-exchange"]))
        {
            $choice = test_input($_POST["refund-or-exchange"]);
            if($choice == "exchange") {
                $return->isExchange = true;
            } else {
                $return->isExchange = false;
            }

            if($choice == "store_credit") {
                $return->isCreditMemo = true;
            } else {
                $return->isCreditMemo = false;
            }

            if($choice == "warranty") {
				$return->isWarranty = true;
			} else {
				$return->isWarranty = false;
			}
        }

        if(!empty($_POST["orderId"]))
        {
            $return->orderId = test_input($_POST["orderId"]);
        }
        if(!empty($_POST["firstName"]))
        {
            $return->firstName = test_input($_POST["firstName"]);
        }
        if(!empty($_POST["lastName"]))
        {
            $return->lastName = test_input($_POST["lastName"]);
        }
        if(!empty($_POST["email"]))
        {
            $return->email = test_input($_POST["email"]);
        }
        if(!empty($_POST["phone"]))
        {
            $return->phone = test_input($_POST["phone"]);
        }
        if(!empty($_POST["address1"]))
        {
            $return->address1 = test_input($_POST["address1"]);
        }
        if(!empty($_POST["address2"]))
        {
            $return->address2 = test_input($_POST["address2"]);
        }
        if(!empty($_POST["city"]))
        {
            $return->city = test_input($_POST["city"]);
        }
        if(!empty($_POST["state"]))
        {
            $return->state = test_input($_POST["state"]);
        }
        if(!empty($_POST["postalCode"]))
        {
            $return->postalCode = test_input($_POST["postalCode"]);
        }
        if(!empty($_POST["notes"]))
        {
            $return->notes = ""; //test_input($_POST["notes"]);
        }
		if(!empty($_POST["isAdmin"]))
		{
			$return->isAdmin = test_input($_POST["isAdmin"]);
		}

        $skus = [];
        foreach($_POST as $key => $value) {
            //we are going to gather all of the skus that have return quantities
            if(startsWith($key, "qty_")) {
                if((int)$value > 0) {
                    //we have a sku that is being returned so we build a list of skus
                    array_push($skus, substr($key, 4));
                }
            }
        }

        $allItems = array();

        //now we have a list of skus the customer is returning
        foreach($skus as $sku) {
            //now we have the skus we can reference the return reason and exchange fields from this value
            $returnItem = new HyleteReturnItem();
            $returnItem->sku = $sku;

            if(!empty($_POST["qty_".$sku]))
            {
                $returnItem->qty = test_input($_POST["qty_".$sku]);
            }

            if(!empty($_POST["rr_".$sku]))
            {
                $returnItem->returnReason = test_input($_POST["rr_".$sku]);
            }

			if(!empty($_POST["wr_".$sku]))
			{
				$returnItem->warrantyReason = test_input($_POST["wr_".$sku]);
			}

            if(!empty($_POST["exsize_".$sku]))
            {
                $returnItem->replacementSku = test_input($_POST["exsize_".$sku]);
            }

            array_push($allItems, $returnItem);
        }

        $return->returnItems = array("HyleteReturnItem" => $allItems);

        $params = array(
            "hyleteReturn" => $return
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

        $jsonReturn = $client->call('CreateReturnExchange', array($params), '', '', false, true);

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
    createReturn();
} catch(Exception $e) {

    $data = array();
    $data['success'] = false;
    $data['message'] = $e->getMessage();

    echo json_encode($data);
}

?>