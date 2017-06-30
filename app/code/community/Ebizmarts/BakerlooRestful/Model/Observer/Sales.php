<?php

class Ebizmarts_BakerlooRestful_Model_Observer_Sales {

    /**
     * Add discount_amount data from Magento Order to POS Order.
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function addDataToOrder(Varien_Event_Observer $observer) {
        $action = $observer->getEvent()->getControllerAction();

        $request = $action->getRequest();

        $posOrderId = (int)$request->getParam('id');
        $posOrder   = Mage::getModel('bakerloo_restful/order')->load($posOrderId);

        if( $posOrder->getId() and ($posOrder->getId() == $posOrderId) and $posOrder->getOrderId() ) {
            $magentoOrder = Mage::getModel('sales/order')->load($posOrder->getOrderId());

            $posOrder
                ->setDiscountAmount($magentoOrder->getDiscountAmount())
                ->save();
        }

        return $this;
    }

    /**
     * Add transaction data on `sales_order_invoice_pay` evant.
     */
    public function addTransaction(Varien_Event_Observer $observer) {
        $invoice = $observer->getInvoice();
        $method  = $invoice->getOrder()->getPayment()->getMethodInstance();

        if($method->getCode() != 'bakerloo_paypalhere')
            return $this;

        $invoice->getOrder()->getPayment()->setTransactionId($method->getInfoInstance()->getPoNumber());
        $invoice->getOrder()->getPayment()->addTransaction('capture');

        return $this;
    }

    public function sendEmailFix(Varien_Event_Observer $observer) {

        $emailTo = Mage::registry('pos_send_email_to');

        if (version_compare(Mage::getVersion(), '1.9.0.0', '>=') === true or !$emailTo)
            return $this;

        $order = $observer->getEvent()->getOrder();

        //Avoid not sending email if it was already sent.
        $order->setData('email_sent', false);

        if(is_object($emailTo))
            Mage::getModel('bakerloo_restful/api_orders')->setCustomerToOrder($emailTo, $order);
        else
            $order->setData('customer_email', $emailTo);

        return $this;
    }

}