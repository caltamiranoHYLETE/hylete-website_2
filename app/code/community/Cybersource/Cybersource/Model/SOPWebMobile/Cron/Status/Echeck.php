<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_SOPWebMobile_Cron_Status_Echeck extends Mage_Core_Model_Abstract
{
    const TEST_ENDPOINT_URL = 'https://ebctest.cybersource.com/ebctest/DownloadReport';
    const PROD_ENDPOINT_URL = 'https://ebc.cybersource.com/ebc/DownloadReport';

    const PROCESSING_LOG = 'cybs_echeck_processing.log';

    protected $_config;

    public function execute()
    {
        foreach (Mage::app()->getStores() as $store) {
            Mage::app()->setCurrentStore($store->getId());
            $this->_config = Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::getSystemConfig();

            $this->log('Processing ECheck transactions for store ' . $store->getName());
            $this->processPendingOrders();
            $this->log('Finished processing ECheck transactions for store ' . $store->getName());
        }
    }

    /**
     * @return $this
     */
    protected function processPendingOrders()
    {
        if (! $pendingOrderIds = $this->getPendingOrderIds()) {
            return $this;
        }

        try {
            // test mode
            if (Mage::helper('cybersource_core')->getIsTestMode()) {
                foreach ($pendingOrderIds as $orderId) {
                    $this->resolvePendingOrder($orderId, $this->_config['echeck_test_event_type']);
                }
                return $this;
            }

            $authHeader = array(
                'Authorization: Basic ' . base64_encode($this->_config['username'] . ':' . $this->_config['password'])
            );

            $ch = curl_init($this->getReportUrl());
            curl_setopt($ch, CURLOPT_HTTPHEADER, $authHeader);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

            $response = curl_exec($ch);

            if (! preg_match('/^Payment Events Report/', $response)) {
                $this->log("Invalid response: " . $response);
                return $this;
            }

            $reportData = $this->parseCsvFile($response);

            foreach ($reportData as $refId => $reportRow) {
                if (! in_array($refId, $pendingOrderIds)) {
                    continue;
                }

                $this->resolvePendingOrder($refId, $reportRow[4]);
            }
        } catch (Exception $e) {
            $this->log('Error: ' . $e->getMessage(), true);
        }
    }

    protected function getPendingOrderIds()
    {
        $orders = Mage::getModel('sales/order')->getCollection()
            ->addFieldToFilter('store_id', Mage::app()->getStore()->getId())
            ->addFieldToFilter('state', Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW)
            ->addFieldToFilter('created_at', array(
                'from' => strtotime('-2 weeks'),
                'to' => time(),
                'datetime' => true
            ));

        $orderIds = array();

        foreach ($orders as $order) {
            if ($order->getPayment()->getMethod() != Cybersource_Cybersource_Model_SOPWebMobile_Payment_Echeck::CODE) {
                continue;
            }
            $orderIds[] = $order->getIncrementId();
        }

        return $orderIds;
    }

    /**
     * @param string $orderId
     * @param string $status
     * @return $this
     * @throws Exception
     */
    protected function resolvePendingOrder($orderId, $status)
    {
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
        if (! $order->getId()) {
            $this->log('Order #' . $orderId . ' was not found.', true);
            return $this;
        }

        $acceptEventTypes = explode(',', $this->_config['echeck_accept_event_type']);
        $rejectEventTypes = explode(',', $this->_config['echeck_reject_event_type']);

        if (in_array(strtolower($status), array_map('strtolower', $acceptEventTypes))) {
            $order->getPayment()
                ->setNotificationResult(1)
                ->setTransactionId($order->getPayment()->getLastTransId())
                ->registerPaymentReviewAction('accept', false);

            $order->save();

            $this->log('Order #' . $orderId . ' was accepted.', true);
            return $this;
        }

        if (in_array(strtolower($status), array_map('strtolower', $rejectEventTypes))) {
            $order->getPayment()
                ->setNotificationResult(1)
                ->setTransactionId($order->getPayment()->getLastTransId())
                ->registerPaymentReviewAction('deny', false);

            $order->save();

            $this->log('Order #' . $orderId . ' was declined.', true);
            return $this;
        }

        $this->log('Status is: ' . $status . ' (order #' . $orderId . ')', true);

        return $this;
    }

    /**
     * @param string|null $reportDate
     * @return string
     */
    private function getReportUrl($reportDate = null)
    {
        $merchantId = Mage::helper('cybersource_core')->getMerchantId();

        $endpointUrl = Mage::helper('cybersource_core')->getIsTestMode() ? self::TEST_ENDPOINT_URL : self::PROD_ENDPOINT_URL;
        $reportDate = $reportDate ? date('Y/m/d', strtotime($reportDate)) : date('Y/m/d', strtotime('-1 day'));

        return "{$endpointUrl}/{$reportDate}/{$merchantId}/PaymentEventsReport.csv";
    }

    /**
     * Expected CSV format
     * 0 request_id,
     * 1 merchant_id,
     * 2 merchant_ref_number,
     * 3 payment_type,
     * 4 event_type,
     * 5 event_date,
     * 6 trans_ref_no,
     * 7 merchant_currency_code,
     * 8 merchant_amount,
     * 9 consumer_currency_code,
     * 10 consumer_amount,
     * 11 fee_currency_code,
     * 12 fee_amount,processor_message
     *
     * @param string $response
     * @return array
     */
    private function parseCsvFile($response)
    {
        $data = array();

        $lines = explode("\n", $response);
        for ($j = 2; $j < count($lines); $j++) {
            if (!empty($lines[$j])) {
                $reportRow = explode(",", $lines[$j]);
                $data[$reportRow[2]] = $reportRow;
            }
        }
        return $data;
    }

    /**
     * @param string $message
     * @return $this
     */
    private function log($message)
    {
        Mage::log($message, null, self::PROCESSING_LOG, true);
        return $this;
    }
}
