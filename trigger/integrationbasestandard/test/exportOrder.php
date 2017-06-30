<?php

/*
 *  WARNING, THIS FILE IS ONLY FOR TEST.
 */

header('Content-Type: text/plain; charset=utf-8');
ini_set('memory_limit', '1024M');
chdir('../../..');

require 'app/Mage.php';

if (!Mage::isInstalled()) {
    echo "Application is not installed yet, please complete install wizard first.";
    exit;
}

$_SERVER['SCRIPT_NAME'] = str_replace(basename(__FILE__), 'index.php', $_SERVER['SCRIPT_NAME']);
$_SERVER['SCRIPT_FILENAME'] = str_replace(basename(__FILE__), 'index.php', $_SERVER['SCRIPT_FILENAME']);

Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$result = new Varien_Object();
$result->setStatus(false);

$orderId = Mage::app()->getRequest()->getParam("orderid");
if(isset($orderId) && (int)$orderId > 0) {
    $object = new Varien_Object();
    $object->setEntityId($orderId);
    Mage::app()->dispatchEvent("integrationbase_process_queue_order", array("queue" => $object, "result" => $result));
}

echo "Order exported: " . ($result->getStatus() == true ? "true" : "false");
