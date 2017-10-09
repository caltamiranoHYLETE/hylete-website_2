<?php
require '../../vendor/autoload.php';
use GlobalE\SDK\SDK;

include 'HandleCreateStub.php';

$SDK = new SDK(20, 'USD');
$RequestBody = file_get_contents('coreRequest_SendOrderToMerchant.json');
$HandleCreateStub = new HandleCreateStub();

$response = $SDK->Merchant()->HandleOrderCreation($RequestBody, $HandleCreateStub);