<?php
require '../../vendor/autoload.php';
use GlobalE\SDK\SDK;
use GlobalE\SDK\API\Common;

// ##### OrderStatusDetails START #####
$OrderStatusDetailsJson = json_decode(file_get_contents('OrderStatusDetails.json'),true);

$OrderStatusDetails = new Common\Request\OrderStatusDetails();
$OrderStatus = new Common\Request\OrderStatus();
$OrderStatusReason = new Common\Request\OrderStatusReason();

foreach ($OrderStatusDetailsJson['OrderStatus'] as $key => $value) {
    $OrderStatus->{"set$key"}($value);
}
foreach ($OrderStatusDetailsJson['OrderStatusReason'] as $key => $value) {
    $OrderStatusReason->{"set$key"}($value);
}
unset($OrderStatusDetailsJson['OrderStatus'],$OrderStatusDetailsJson['OrderStatusReason']);
foreach ($OrderStatusDetailsJson as $key => $value) {
    $OrderStatusDetails->{"set$key"}($value);
}

$OrderStatusDetails->setOrderStatus($OrderStatus);
$OrderStatusDetails->setOrderStatusReason($OrderStatusReason);
// ##### OrderStatusDetails END #####


// ##### Products START #####
$ProductsJson = json_decode(file_get_contents('Products.json'),true);
$Product = new Common\Request\Product();
foreach ($ProductsJson[0] as $key => $value) {
    $Product->$key = $value;
}
$Products = array($Product);
// ##### Products END #####

// ##### Parcels START #####
$Parcel = new Common\Request\Parcel();
$Parcel->setParcelCode('123');
$Parcels = array($Parcel);
// ##### Parcels END #####

$SDK = new SDK(20, 'USD');
$response = $SDK->Admin()->UpdateOrderDispatch($Products,$OrderStatusDetails,$Parcels);


var_dump($response);