<?php
/**
 * Subscribe Pro - Subscriptions Management Extension
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to commercial source code license of SUBSCRIBE PRO INC.
 *
 * @category  SubscribePro
 * @package   SubscribePro_Autoship
 * @author    Subscribe Pro Support <support@subscribepro.com>
 * @copyright 2009-2020 SUBSCRIBE PRO INC. All Rights Reserved.
 * @license   http://www.subscribepro.com/terms-of-service/ Subscribe Pro Terms of Service
 * @link      http://www.subscribepro.com/
 *
 */

require_once "Mage/Adminhtml/controllers/Sales/Order/InvoiceController.php";

class SubscribePro_Autoship_Adminhtml_Sales_Order_InvoiceController extends Mage_Adminhtml_Sales_Order_InvoiceController
{

    /**
     * Override - Save invoice
     */
    public function saveAction()
    {
        // Call the parent saveAction()
        parent::saveAction();

        // Lookup order
        $orderId = $this->getRequest()->getParam('order_id');
        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->load($orderId);
        $payment = $order->getPayment();

        // Check if automatic reauth of partial capture is turned on?
        if (Mage::getStoreConfig('payment/subscribe_pro/reauthorize_partial_capture', $order->getStore()) == '1') {
            /** @var SubscribePro_Autoship_Helper_Payment $paymentHelper */
            $paymentHelper = Mage::helper('payment');
            // Check for SP pay method
            if($paymentHelper->isSubscribeProCreditCardMethod($payment->getMethod())) {
                // Only process for authorize only orders
                /** @var SubscribePro_Autoship_Model_Payment_Method_Cc $methodInstance */
                $methodInstance = $payment->getMethodInstance();
                // Check if this order / payment eligible for reauth
                if ($methodInstance->canReauthorize($order->getPayment())) {
                    // Now reauthorize this order
                    Mage::helper('autoship/payment')->reauthorizeOrder($order);
                }
            }
        }
    }

}
