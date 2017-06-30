<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require 'app/Mage.php';

if (!Mage::isInstalled()) {
    echo "Application is not installed yet, please complete install wizard first.";
    exit;
}

// Only for urls
// Don't remove this
$_SERVER['SCRIPT_NAME'] = str_replace(basename(__FILE__), 'index.php', $_SERVER['SCRIPT_NAME']);
$_SERVER['SCRIPT_FILENAME'] = str_replace(basename(__FILE__), 'index.php', $_SERVER['SCRIPT_FILENAME']);

//Mage::app('admin');
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID); 

try {
    $order_id = isset($_GET["order_id"]) ? $_GET["order_id"] : "100012416";
    $transact = isset($_GET["transact"]) ? $_GET["transact"] : "472085655";

    $order = Mage::getModel("sales/order")->loadByIncrementId($order_id);
    if( !$order->getId() ){
        echo "No order ID <br>\n"; die();
    }
    echo "Have order <br>\n";

    $dibs = Mage::getModel('dibs/dibs');
    echo "Have dibs/dibs <br>\n";

    $additionaldata = unserialize($order->getPayment()->getAdditionalData());
    if( !$additionaldata ) $additionaldata = array();


    // Direct capture means the order has already been captured
    $status_old = $status = $order->getStatus();
    switch ($dibs->getConfigData('direct_capture')) {
        case 0:
            $state = Mage_Sales_Model_Order::STATE_NEW;
            $status = Icommerce_OrderStatus_Helper_Data::getStatus($dibs->getConfigData('order_status_reserved'), $state);
            $additionaldata['transactionCaptured'] = 'no';
            break;

        case 1:
            $state = Mage_Sales_Model_Order::STATE_PROCESSING;
            $status = Icommerce_OrderStatus_Helper_Data::getStatus($dibs->getConfigData('order_status_captured'), $state);
            $additionaldata['transactionCaptured'] = 'yes';
            break;
        case 2:

            $state = Mage_Sales_Model_Order::STATE_NEW;
            $status = Icommerce_OrderStatus_Helper_Data::getStatus($dibs->getConfigData('order_status_reserved'), $state);
            $additionaldata['transactionCaptured'] = 'no';
            break;
    }

    $additionaldata['dibs_success_received'] = 1;
    $order->getPayment()->setAdditionalData(serialize(($additionaldata)));
    $order->getPayment()->setLastTransId($transact);
    //$order->sendNewOrderEmail();
    $order->setEmailSent(true);
    echo "setAdditionalData <br>\n";

    $msg = "Order fake created" . "<br/>" . "DIBS Order ID" . ": <b>" . $transact . "</b>";
    $order->setState($state, $status, $msg, true);
    echo "order SetState <br>\n";

    $order->save();
    echo "order save() <br>\n";

} catch (Exception $e) {
    Mage::printException($e);
}
