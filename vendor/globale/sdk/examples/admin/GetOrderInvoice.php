<?php
require '../../vendor/autoload.php';
use GlobalE\SDK\SDK;
use GlobalE\SDK\Core;
use GlobalE\SDK\Models\Common;

$SDK = new SDK(20, 'USD');

Core\Settings::set('MerchantGUID','xxxxxxx');
$OrderIds = array('GE92799028GB','GE92798543GB');

$response = $SDK->Admin()->GetOrderInvoice($OrderIds);
/**
 * @var Common\Invoice
 */
$Invoice = $response->getData();

array_map('header',$Invoice->getHeaders());

echo $Invoice->getBody();
