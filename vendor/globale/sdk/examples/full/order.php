<?php

require '../../vendor/autoload.php';
include '../merchant/HandleCreateStub.php';

use GlobalE\SDK\SDK;
use GlobalE\SDK\Models;
$sdk = new SDK();
?>

<?php

if(!empty($_GET['action'])) {
    $getAPIResponseData = file_get_contents("php://input");
    $parsedData = json_decode($getAPIResponseData);
    if(!empty($getAPIResponseData)) {
        $HandleCreateStub = new HandleCreateStub();
        switch ($_GET['action']) {
            case "create":
                $response = $sdk->Merchant()->HandleOrderCreation($getAPIResponseData, $HandleCreateStub);
                break;
            case "update":
                $response = $sdk->Merchant()->HandleOrderStatusUpdate($getAPIResponseData, $HandleCreateStub);
                break;
            case "payment":
                $response = $sdk->Merchant()->HandleOrderPayment($getAPIResponseData, $HandleCreateStub);
                break;
        }
    }else{
        echo "Received an empty request";
    }
    header('Content-type: application/json');
    die();
}
?>