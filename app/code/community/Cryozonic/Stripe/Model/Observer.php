<?php

class Cryozonic_Stripe_Model_Observer
{
    public function updateOrderState($observer)
    {
        $payment = $observer->getPayment();
        if (empty($payment) || $payment->getMethod() != 'cryozonic_stripe')
            return;

        if ($payment->getAdditionalInformation('stripe_outcome_type') == "manual_review")
        {
            $order = $payment->getOrder();
            $order->setHoldBeforeState($order->getState());
            $order->setHoldBeforeStatus($order->getStatus());
            $order->setState(Mage_Sales_Model_Order::STATE_HOLDED)
                ->setStatus($order->getConfig()->getStateDefaultStatus(Mage_Sales_Model_Order::STATE_HOLDED));
            $comment = Mage::helper("cryozonic_stripe")->__("Order placed under manual review by Stripe Radar");
            $order->addStatusToHistory(false, $comment, false);
            $order->save();
        }
    }

    public function updateStripeCustomer($observer)
    {
        $customer = $observer->getPayment()->getOrder()->getCustomer();
        if (!$customer) return;
        $customerId = $customer->getId();
        $customerEmail = $customer->getEmail();

        if (!empty($customerId) && !empty($customerEmail))
        {
            try
            {
                $resource = Mage::getSingleton('core/resource');
                $connection = $resource->getConnection('core_write');
                $fields = array();
                $fields['customer_id'] = $customerId;
                $guestSelect = $connection->quoteInto('customer_email=?', $customerEmail) . ' and ' . $connection->quoteInto('session_id=?', Mage::getSingleton("core/session")->getEncryptedSessionId());
                $result = $connection->update('cryozonic_stripesubscriptions_customers', $fields, $guestSelect);
            }
            catch (\Exception $e) {}
        }
    }

    public function sales_order_payment_place_end($observer)
    {
        $this->updateOrderState($observer);
        $this->updateStripeCustomer($observer);
    }

    public function sales_order_invoice_pay($observer)
    {
        $store = Mage::app()->getStore();

        // In the admin area, there is a checkbox dictating whether to send an invoice or not
        if ($store->isAdmin())
            return;

        $shouldSendInvoice = $store->getConfig('payment/cryozonic_stripe/email_invoice');
        if (!$shouldSendInvoice)
            return;

        try
        {
            $invoice = $observer->getEvent()->getInvoice();
            $invoice->save();
            $invoice->sendEmail(true, '');
        }
        catch (Exception $e)
        {
            Mage::logException($e);
        }
    }

    public function sales_order_invoice_cancel($observer)
    {
        $invoice = $observer->getEvent()->getInvoice();
        if (empty($invoice))
            return;

        $payment = $invoice->getOrder()->getPayment();
        if (strpos($payment->getMethod(), 'cryozonic_') === 0)
        {
            $payment->getMethodInstance()->cancel($payment);
        }
    }
}
