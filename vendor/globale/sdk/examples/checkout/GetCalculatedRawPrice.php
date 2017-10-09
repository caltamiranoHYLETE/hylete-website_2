<?php

require '../../vendor/autoload.php';
use GlobalE\SDK\SDK;
use GlobalE\SDK\API\Common;

$_SERVER['REMOTE_ADDR']= '150.217.16.251'; //italy

$SDK = new SDK(20, 'USD');
$SDK->Browsing()->OnPageLoad();

$rawData1 = new Common\Request\RawPriceRequestData();
$rawData1->setOriginalListPrice(120);
$rawData1->setIsFixedPrice(false);

$rawData2 = new Common\Request\RawPriceRequestData();
$rawData2->setOriginalListPrice(75);
$rawData2->setIsFixedPrice(true);

$result = $SDK->Checkout()->GetCalculatedRawPrice(array($rawData1,$rawData2), false, false, false);
print_r($result);