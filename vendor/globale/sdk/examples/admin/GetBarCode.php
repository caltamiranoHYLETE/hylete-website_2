<?php
require '../../vendor/autoload.php';
use GlobalE\SDK\SDK;
use GlobalE\SDK\Core;

$SDK = new SDK(20, 'USD');
$SDK->Browsing()->OnPageLoad();

Core\Settings::set('MerchantID','44'); // ID of LRDirect
$response = $SDK->Admin()->GetBarCode('GE92800774GB');
$BarCode = $response->getMessage();

?>

<a href="<?=$BarCode?>" target="_blank"> Bar Code </a>
