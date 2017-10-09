<?php
define('GlobalE_ENV','dev');
require '../../vendor/autoload.php';

use GlobalE\SDK\SDK;
use GlobalE\SDK\API\Common;
$SDK = new SDK();
$_SERVER['REMOTE_ADDR']= '77.185.208.234'; //germany
//$_SERVER['REMOTE_ADDR']= '150.217.16.251'; //italy

$SDK->Browsing()->OnPageLoad();

$symbols = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
	'0','1','2','3','4','5','6','7','8','9');

$products = array();
for($i=0;$i<50;$i++){
	$ProductCode = $symbols[rand(0,35)].$symbols[rand(0,35)].$symbols[rand(0,35)].$symbols[rand(0,35)].$symbols[rand(0,35)].$symbols[rand(0,35)];
	$Price = rand(20, 100);
	$IsFixedPrice = rand(0,1) === 1;

	$product = new Common\Request\ProductRequestData();
	$product->setProductCode($ProductCode);
	$product->setOriginalListPrice($Price);
	$product->setOriginalSalePrice($Price);
	$product->setIsFixedPrice($IsFixedPrice);
	$products[] = $product;
}


$time0 = microtime(true);
$result = $SDK->Browsing()->GetProductsInformation($products,true);
$time1 = microtime(true) - $time0;
echo 'GetProductsInformation first run: '.$time1.' seconds';

echo '<br />';

$time0 = microtime(true);
$result = $SDK->Browsing()->GetProductsInformation($products,true);
$time1 = microtime(true) - $time0;
echo 'GetProductsInformation second run: '.$time1.' seconds';



