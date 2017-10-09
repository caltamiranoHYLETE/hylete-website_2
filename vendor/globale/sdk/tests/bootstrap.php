<?php
$filename = __DIR__ .'/../../../autoload.php';

$loader = require $filename;
date_default_timezone_set("Europe/London");
$_SERVER['REMOTE_ADDR'] = '212.143.40.246';
$loader->addPsr4('GlobalE\\Test\\', __DIR__ );
define('GlobalE_ENV','test');