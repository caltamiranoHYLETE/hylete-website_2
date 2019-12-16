<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_BankTransfer_Cron_Status_Banktransfer extends Mage_Core_Model_Abstract
{
    const PROCESSING_LOG = 'cybs_bt_processing.log';

    private $acceptCodes = array(
        Cybersource_Cybersource_Model_BankTransfer_Source_Consts::STATUS_PROCESSOR_PAYMENT_SETTLED
    );

    private $declineCodes = array(
        Cybersource_Cybersource_Model_BankTransfer_Source_Consts::STATUS_PROCESSOR_PAYMENT_FAILED,
        Cybersource_Cybersource_Model_BankTransfer_Source_Consts::STATUS_PROCESSOR_PAYMENT_ABANDONED
    );

    public function execute()
    {
        foreach (Mage::app()->getStores() as $store) {
            Mage::app()->setCurrentStore($store->getId());

            $this->log('Processing pending bank transfer orders for store ' . $store->getName());
            $this->processPendingOrders();
            $this->log('Finished processing bank transfer orders for store ' . $store->getName());
        }
    }

    private function processPendingOrders()
    {
        $orders = $this->getPendingOrders();

        /** @var $order Mage_Sales_Model_Order */
        foreach ($orders as $order) {
            try {
                $response = Mage::getModel('cybersourcebanktransfer/soapapi_banktransfer')->requestCheckStatusService($order);
                $currentStatus = strtolower($response->apCheckStatusReply->paymentStatus);

                if (in_array($currentStatus, $this->acceptCodes)) {
                    $this->invoiceOrder($order);

                    $this->log('Received payment for order #' . $order->getIncrementId(), true);
                    continue;
                }

                if (in_array($currentStatus, $this->declineCodes)) {
                    $order->registerCancellation(
                        'Order was canceled with transaction status: ' . $currentStatus
                    )->save();

                    $this->log('Order #' . $order->getIncrementId() . ' was canceled (' . $currentStatus.').', true);
                    continue;
                }

                $this->log('Order #' . $order->getIncrementId() . ' is ' . $currentStatus);
            } catch (Exception $e) {
                $this->log($e->getMessage(), true);
            }
        }
    }

    protected function getPendingOrders()
    {
        $allowedPaymentCodes = array(
            Cybersource_Cybersource_Model_BankTransfer_Payment_Ideal::CODE,
            Cybersource_Cybersource_Model_BankTransfer_Payment_Eps::CODE,
            Cybersource_Cybersource_Model_BankTransfer_Payment_Giropay::CODE,
            Cybersource_Cybersource_Model_BankTransfer_Payment_Bancontact::CODE,
            Cybersource_Cybersource_Model_BankTransfer_Payment_Sofort::CODE
        );

        $orders = Mage::getModel('sales/order')->getCollection()
            ->addFieldToFilter('store_id', Mage::app()->getStore()->getId())
            ->addFieldToFilter('status', Mage_Sales_Model_Order::STATE_PENDING_PAYMENT)
            ->addFieldToFilter('created_at', array(
                'from' => strtotime('-2 weeks', time()),
                'to' => time(),
                'datetime' => true
            ));

        $result = array();

        foreach ($orders as $order) {
            if (!in_array($order->getPayment()->getMethod(), $allowedPaymentCodes)) {
                continue;
            }
            $result[] = $order;
        }

        return $result;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @throws Exception
     */
    private function invoiceOrder($order)
    {
        $capture = Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE;
        $invoice = $order->prepareInvoice();
        $invoice->setRequestedCaptureCase($capture);
        $invoice->register();

        Mage::getModel('core/resource_transaction')
            ->addObject($invoice)
            ->addObject($invoice->getOrder())
                ->save();
    }

    /**
     * @param string $message
     * @return $this
     */
    protected function log($message)
    {
        Mage::log($message, null, self::PROCESSING_LOG, true);
        return $this;
    }
}

