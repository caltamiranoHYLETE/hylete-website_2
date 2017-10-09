<?php
session_start();
require '../../vendor/autoload.php';
use GlobalE\SDK\SDK;
use GlobalE\SDK\Api\Common;
use GlobalE\SDK\Models\Common\Request;

$_SERVER['REMOTE_ADDR'] = '212.143.40.246';


define('GlobalE_ENV','dev');
$SDK = new SDK(20, 'USD');

$sendCartRequest = json_decode(file_get_contents('SendCartRequest.json'),true);

$shippingDetails = new Common\Address();
$billingDetails = new Common\Address();
$shippingOptionsList = new Common\ShippingOption();
$Product = new Request\Product();

foreach ($sendCartRequest['shippingDetails'] as $key => $value) {
    $shippingDetails->{"set$key"}($value);
}
foreach ($sendCartRequest['billingDetails'] as $key => $value) {
    $billingDetails->{"set$key"}($value);
}
foreach ($sendCartRequest['shippingOptionsList'] as $key => $value) {
    $shippingOptionsList->{"set$key"}($value);
}
foreach ($sendCartRequest['ProductsList'][0] as $key => $value) {
    $Product->{"set$key"}($value);
}

$sendCartRequest = new Request\SendCart();
$sendCartRequest->setShippingDetails($shippingDetails);
$sendCartRequest->setBillingDetails($billingDetails);
$sendCartRequest->setShippingOptionsList($shippingOptionsList);
$sendCartRequest->setProductsList(array($Product));

$SDK->Browsing()->OnPageLoad();
$response = $SDK->Checkout()->SendCart($sendCartRequest);
var_dump($response);
