<?php

//ensure you are getting error output for debug
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

$email = "test@hylete.com"; //$_POST["email"];
$password = "wilco99"; // $_POST["password"]; // not the same as magento password

require_once("/app/Mage.php");
umask(0);
Mage::app()->setCurrentStore(1); 
Mage::app('default');


?>