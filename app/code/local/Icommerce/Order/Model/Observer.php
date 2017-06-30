<?php

class Icommerce_Order_Model_Observer
{

    public function __construct()
    {

    }

    protected function dispatch($event_name, $order)
    {
        if (!$order) {
            // How do we know the last order...?
            // $order = ...;
        }
        if ($order && $order instanceof Varien_Object) {
            $incrementId = $order->getIncrementId();
        } else {
            $incrementId = '';
        }
        Icommerce_Default::logAppend('icorder->dispatch(): ' . $event_name . ' ' . $incrementId, 'var/icorder.log');
        Mage::dispatchEvent($event_name, array("order" => $order));
    }

    /**
     * Order has been submitted and paid by customer
     *
     * @param $order
     */
    public function dispatchSuccess($order)
    {
        $this->dispatch("ic_order_success", $order);
    }

    /**
     * Order has been submitted and paid by customer
     *
     * @param $order
     */
    public function dispatchCaptured($order)
    {
        $this->dispatch("ic_order_captured", $order);
    }

    /**
     * Order has been cancelled by customer
     *
     * @param $order
     */
    public function dispatchCancelled($order)
    {
        $this->dispatch("ic_order_cancel", $order);
    }

    /**
     * Order has been aborted by customer
     *
     * @param $order
     */
    public function dispatchAborted($order)
    {
        $this->dispatch("ic_order_abort", $order);
    }

    /**
     * XXX 3rd party is invoking a callback function - do we need this ?
     *
     * @param $order
     */
    public function dispatchCallback($order)
    {
        $this->dispatch("ic_order_callback", $order);
    }

    /**
     * Hook for orders that are paid with non 3:rd party payment methods to emit ic_order_success
     *
     * @param $observer
     * @return $this
     */
    public function onSaveOrderAfter($observer)
    {
        $e = $observer->getEvent();
        $order = $e->getData("order");
        $quote = $e->getData("quote");

        // Extract payment method
        $payments = $order->getPaymentsCollection();
        $method = "";
        // # Could there be more than 1 payment?
        foreach ($payments as $p) {
            $method = $p->getData("method");
        }

        //to be able to export all orders we set methods that should be excluded instead, the excluded methods dispatch this event on their own
        /*$methods_no_3rd_party = array(
            "invoicecost" => true,
            "invoicecost2" => true,
            "bankpayment" => true,
            "kreditor_invoice" => true,
            "kreditor_partpayment" => true,
            "checkmo" => true,
            "purchaseorder" => true
        );*/
        $excluded_payment_methods = array(
            'dibs' => true, 'auriga' => true,
            'payson' => true,
            'paypal_standard' => true,
            // AR: added for paypal pro payment
            'paypal_direct' => true,
            'hosted_pro' => true,
            'c3worldpayglobal' => true,
            'klarnacheckout' => true,
            'adyen_hpp' => true,
        );

        if ($method && !isset($excluded_payment_methods[$method])) {
            $this->dispatchSuccess($order);
        }

        return $this;
    }

    public function onSaveQuoteAfter($observer)
    {
        // This check is for compatibility reason, we don't want to completely remove this method
        if (!$this->getConfigDataFlag('paypal_dispatch_success_on_return')) {
            return;
        }

        /* @var $helper Mage_Core_Helper_Http */
        $helper = Mage::helper('core/http');
        $requestUri = $helper->getRequestUri(true);

        if (is_numeric(strpos($requestUri, '/paypal/standard/success/'))) {
            $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
            if ($orderId) {
                $order = Mage::getModel('sales/order')->load($orderId);
                if ($order) {
                    $this->dispatchSuccess($order);
                }
            }
        }
    }

    /**
     * This observer is for the Paypal payment methods and that ic_order_success is correctly dispatched.
     * When a payment is done in Paypal, the IPN sends a message to /paypal/ipn/ and updates the order and sets the
     * status to 'processing' if it's ok.
     * To get this observer to work, the customer must set the Paypal IPN to active and enter the url,
     * www.site.com/paypal/ipn/ in the Paypal admin.
     *
     * @param $observer
     * @return mixed
     */
    public function onSalesOrderSaveAfter($observer)
    {
        /* @var $helper Mage_Core_Helper_Http */
        $helper = Mage::helper('core/http');

        $requestUri = $helper->getRequestUri(true);
        if (strpos($requestUri, '/paypal/ipn/') === false) {
            return;
        }

        $event = $observer->getEvent();

        /* @var $order Mage_Sales_Model_Order */
        $order = $event->getOrder();
        if ($order == null) {
            return;
        }

        $payment = $order->getPayment();
        if ($payment == null) {
            return;
        }

        $status = $order->getStatus();

        $paypalMethods = array(
            'paypal_standard' => true, 'paypal_direct'    => true,
            'hosted_pro'      => true, 'paypal_express'   => true,
            'paypaluk_direct' => true, 'paypaluk_express' => true,
            'verisign'        => true, 'paypal_billing_agreement' => true,
            'payflow_link'    => true,
        );
        $paymentMethod = $payment->getMethod();

        if (isset($paypalMethods[$paymentMethod]) && strtolower($status) == 'processing') {
            $this->dispatchSuccess($order);
        }
    }

    public function getConfigDataFlag($field)
    {
        return Mage::getStoreConfigFlag('icorder/payments/' . $field, Mage::app()->getStore());
    }

    public function salesOrderCancel(Varien_Event_Observer $observer)
    {
        $treatanswer = false;
        $payment = $observer->getEvent()->getPayment();

        // Throwing the exception below, for orders where we're still pending payment.. causes the
        // cancel action to fail at all times. Filter out that case.
        // If we're still in payment pending, it makes no sense to cancel the payment, we have none.
        // This is not possible for Klarna, but for simplicity we put it here (applies to other
        // hosted payment methods though).
        $order = $payment->getOrder();
        if ($order && $order->getState() == 'pending_payment') {
            $oid = $order->getData('increment_id');
            Icommerce_Default::logAppend("Skipping tryCancel on order that is still pending payment ($oid)", 'var/ic_order-cancel-skip.log');
            return;
        }

        if ($this->getConfigDataFlag('cancel')) {
            $paymentMethodCode = $payment->getMethod();
            switch ($paymentMethodCode) {
                case 'dibs':
                    $dibs = Mage::getModel('dibs/dibs');
                    if ($dibs && method_exists($dibs, 'tryCancel')) {
                        $ares = $dibs->tryCancel($payment);
                        $treatanswer = true;
                    }
                    break;
                case 'kreditor_invoice':
                case 'klarnacheckout':
                    $klarna = Mage::getModel('kreditor/klarna_invoice');
                    if ($klarna && method_exists($klarna, 'tryCancel')) {
                        $ares = $klarna->tryCancel($payment);
                        $treatanswer = true;
                    }
                    break;
                case 'kreditor_partpayment':
                    $klarna = Mage::getModel('kreditor/klarna_partpayment');
                    if ($klarna && method_exists($klarna, 'tryCancel')) {
                        $ares = $klarna->tryCancel($payment);
                        $treatanswer = true;
                    }
                    break;
                case 'resursbank':
                    $resursbank = Mage::getModel('resursbank/resursbank');
                    if ($resursbank && method_exists($resursbank, 'tryCancel')) {
                        $ares = $resursbank->tryCancel($payment);
                        $treatanswer = true;
                    }
                    break;
                case 'resursinvoice':
                case 'resurscard':
                case 'resursinstallment':
                    $resursModelMap = array(
                        'resursinvoice'     => 'resursinvoice/resursBankInvoice',
                        'resursinstallment' => 'resursinvoice/resursBankInstallment',
                        'resurscard'        => 'resursinvoice/resursBankCard',
                    );
                    $resursbank = isset($resursModelMap[$paymentMethodCode]) ? Mage::getModel($resursModelMap[$paymentMethodCode]) : null;
                    if ($resursbank && method_exists($resursbank, 'tryCancel')) {
                        $ares = $resursbank->tryCancel($payment);
                        $treatanswer = true;
                    }
                    break;
            }
        }
        if ($treatanswer) {
            if ($ares[0] == 0) {
                if ($ares[1] != "") {
                    Mage::getSingleton('adminhtml/session')->addSuccess($ares[1]);
                }
            } elseif ($ares[0] > 1) {
//              Mage::getSingleton('adminhtml/session')->addWarning($ares[1]);
                throw new Mage_Core_Exception($ares[1]);
            } elseif ($ares[0] < 0) {
//              Mage::getSingleton('adminhtml/session')->addError($ares[1]);
                throw new Mage_Core_Exception($ares[1]);
            }
            $payment
                    ->getOrder()
                    ->addStatusToHistory($payment
                    ->getOrder()
                    ->getStatus(), $ares[1]);
        }
    }

    public function salesPaymentRefund(Varien_Event_Observer $observer)
    {
        $treatanswer = false;
        $creditmemo = $observer->getEvent()->getCreditmemo();
        if ($this->getConfigDataFlag('refund')) {

            $payment = $observer->getEvent()->getPayment();
            $baseAmount = $creditmemo->getBaseGrandTotal();
            $amount = $creditmemo->getGrandTotal();
            if ($creditmemo->getCustomerBalanceRefundFlag()) {
                $amount = $amount - $creditmemo->getCustomerBalanceTotalRefunded();
                $baseAmount = $baseAmount - $creditmemo->getBaseCustomerBalanceTotalRefunded();
            }

            if ($amount<=0) return;

            $paymentMethodCode = $payment->getMethod();
            switch ($paymentMethodCode) {
                case 'dibs':
                    $dibs = Mage::getModel('dibs/dibs');
                    if ($dibs && method_exists($dibs, 'tryRefund')) {
                        $ares = $dibs->tryRefund($payment, $baseAmount);
                        $treatanswer = true;
                    }
                    break;
                case 'kreditor_invoice':
                case 'klarnacheckout':
                    $klarna = Mage::getModel('kreditor/klarna_invoice');
                    if ($klarna && method_exists($klarna, 'tryRefund')) {
                        $ares = $klarna->tryRefund($payment, $amount);
                        $treatanswer = true;
                    }
                    break;
                case 'kreditor_partpayment':
                    $klarna = Mage::getModel('kreditor/klarna_partpayment');
                    if ($klarna && method_exists($klarna, 'tryRefund')) {
                        $ares = $klarna->tryRefund($payment, $amount);
                        $treatanswer = true;
                    }
                    break;
                case 'resursbank':
                    $resursbank = Mage::getModel('resursbank/resursbank');
                    if ($resursbank && method_exists($resursbank, 'tryRefund')) {
                        $ares = $resursbank->tryRefund($payment, $amount);
                        $treatanswer = true;
                    }
                    break;
                case 'resursinvoice':
                case 'resurscard':
                case 'resursinstallment':
                    $resursModelMap = array(
                        'resursinvoice'     => 'resursinvoice/resursBankInvoice',
                        'resursinstallment' => 'resursinvoice/resursBankInstallment',
                        'resurscard'        => 'resursinvoice/resursBankCard',
                    );
                    $resursbank = isset($resursModelMap[$paymentMethodCode]) ? Mage::getModel($resursModelMap[$paymentMethodCode]) : null;
                    if ($resursbank && method_exists($resursbank, 'tryRefund')) {
                        $payment->setCreditmemo($creditmemo);
                        $ares = $resursbank->tryRefund($payment, $amount);
                        $treatanswer = true;
                    }
                    break;
            }
        }
        if ($treatanswer) {
            if ($ares[0] == 0) {
                Mage::getSingleton('adminhtml/session')->addSuccess($ares[1]);
            } elseif ($ares[0] > 1) {
//              Mage::getSingleton('adminhtml/session')->addWarning($ares[1]);
                throw new Mage_Core_Exception($ares[1]);
            } elseif ($ares[0] < 0) {
//              Mage::getSingleton('adminhtml/session')->addError($ares[1]);
                throw new Mage_Core_Exception($ares[1]);
            }
            $payment
                    ->getOrder()
                    ->addStatusToHistory($payment
                    ->getOrder()
                    ->getStatus(), $ares[1]);
        }
    }
}
