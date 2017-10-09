<?php
require '../../vendor/autoload.php';
use GlobalE\SDK\SDK;
use GlobalE\SDK\Models\Common;

$_SERVER['REMOTE_ADDR'] = '212.143.40.246';

$ProductsJson = json_decode(file_get_contents('Products.json'),true);
$Product = new Common\Request\Product();

foreach ($ProductsJson[0] as $key => $value) {
    $Product->$key = $value;
}


$SDK = new SDK(20, 'USD');
$Products = array($Product);
$SDK->Browsing()->OnPageLoad();
$response = $SDK->Admin()->SaveProductsList($Products);


var_dump($response);