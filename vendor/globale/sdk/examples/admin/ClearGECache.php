<?php
require '../../vendor/autoload.php';
use GlobalE\SDK\SDK;
use GlobalE\SDK\Core;

$SDK = new SDK(20, 'USD');

$scanDir = scandir(Core\Settings::get('Cache.Path'));
echo 'Cache dir before clear cache: <br />';
var_dump($scanDir);


$response = $SDK->Admin()->ClearGECache();
var_dump($response);

$scanDir = scandir(Core\Settings::get('Cache.Path'));
echo 'Cache dir after clear cache: <br />';
var_dump($scanDir);

