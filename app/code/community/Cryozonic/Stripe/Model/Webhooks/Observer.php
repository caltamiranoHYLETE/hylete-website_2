<?php

class Cryozonic_Stripe_Model_Webhooks_Observer
{
    protected function orderAgeLessThan($minutes, $order)
    {
        $created = strtotime($order->getCreatedAt());
        $now = time();
        return (($now - $created) < ($minutes * 60));
    }

    // payment_intent.succeeded, creates an invoice when the payment is captured from the Stripe dashboard
    public function cryozonic_stripe_webhook_payment_intent_succeeded($observer)
    {
        $event = $observer->getEvent();
        $object = $observer->getObject();
        $order = Mage::helper('cryozonic_stripe/webhooks')->loadOrderFromEvent($event);

        // The following can trigger when:
        // 1. A merchant uses the Stripe Dashboard to manually capture a payment intent that was Authorized Only
        // 2. When a normal order is placed at the checkout, in which case we need to ignore this
        // This is scenario 2 which we need to ignore
        if (empty($order) || $this->orderAgeLessThan($minutes = 3, $order))
            throw new Exception("Ignoring", 202);

        $paymentIntentId = $event['data']['object']['id'];
        $captureCase = Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE;
        $params = [
            "amount" => $event['data']['object']['amount_received'],
            "currency" => $event['data']['object']['currency']
        ];

        Mage::helper('cryozonic_stripe')->invoiceOrder($order, $paymentIntentId, $captureCase, $params);
    }

    // charge.refunded, creates a credit memo when the payment is refunded from the Stripe dashboard
    public function cryozonic_stripe_webhook_charge_refunded_card($observer)
    {
        $event = $observer->getEvent();
        $object = $observer->getObject();
        $order = Mage::helper('cryozonic_stripe/webhooks')->loadOrderFromEvent($event);
        Mage::helper('cryozonic_stripe/webhooks')->refund($order, $object);
    }
}
