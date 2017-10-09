<?php
require '../../vendor/autoload.php';

define('GlobalE_ENV','dev');

use GlobalE\SDK\SDK;
use GlobalE\SDK\API\Common;

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

$SDK = new SDK(20, 'USD');
$response = $SDK->Admin()->UpdateOrderStatus($OrderStatusDetails);


var_dump($response);