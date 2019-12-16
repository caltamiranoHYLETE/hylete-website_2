<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_Core_Cron_ConversionReport extends Mage_Core_Model_Abstract
{
    const TEST_ENDPOINT_URL = 'https://ebctest.cybersource.com/ebctest/ConversionDetailReportRequest.do';
    const PROD_ENDPOINT_URL = 'https://ebc.cybersource.com/ebc/ConversionDetailReportRequest.do';

    const CONVERSION_ACCEPT_STATUS = 'ACCEPT';
    const CONVERSION_REJECT_STATUS = 'REJECT';

    const CONVERSION_LOG = 'cybs_conversion.log';

    protected $allowedPaymentMethods = array(
        Cybersource_Cybersource_Model_ApplePay_Pay::CODE,
        Cybersource_Cybersource_Model_Paypal_Paypal::CODE,
        Cybersource_Cybersource_Model_VisaCheckout_Pay::CODE,
        Cybersource_Cybersource_Model_SOPWebMobile_Payment_Cc::CODE,
        Cybersource_Cybersource_Model_SOPWebMobile_Payment_Echeck::CODE
    );

    public function execute()
    {
        foreach (Mage::app()->getStores() as $store) {
            $appEmulation = Mage::getSingleton('core/app_emulation');
            $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($store->getId());

            if (! Mage::helper('cybersource_core')->getIsReportEnabled()) {
                continue;
            }

            $this->log('On-Demand Report processing started for store ' . $store->getName());
            $this->processPendingOrders();
            $this->log('On-Demand Report processing finished for store ' . $store->getName());

            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
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

        $reportParams = $this->getReportParams();
        $reportParams = http_build_query($reportParams);

        $reportUrl = Mage::helper('cybersource_core')->getIsTestMode() ? self::TEST_ENDPOINT_URL : self::PROD_ENDPOINT_URL;

        try {
            $ch = curl_init($reportUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $reportParams);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $response = curl_exec($ch);

            if (! simplexml_load_string($response)) {
                $this->log("Response is invalid xml: " . $response);
                return $this;
            }

            $responseXml = new SimpleXMLElement($response);
            if (empty($responseXml->Conversion)) {
                $this->log('There are no orders to convert.');
                return $this;
            }

            foreach ($responseXml as $conversion) {
                if (empty($conversion['MerchantReferenceNumber'])) {
                    $this->log('No Merchant Reference Number provided.', true);
                    continue;
                }

                $orderId = (string) $conversion['MerchantReferenceNumber'];
                if (! in_array($orderId, $pendingOrderIds)) {
                    continue;
                }

                $decision = (string) $conversion->NewDecision;
                $this->resolvePendingOrder($orderId, $decision);
            }
        } catch (Exception $e) {
            $this->log('Error: ' . $e->getMessage(), true);
        }
    }

    protected function getReportParams()
    {
        return array(
            'merchantID' => Mage::helper('cybersource_core')->getMerchantId(),
            'username' => Mage::helper('cybersource_core')->getReportUsername(),
            'password' => Mage::helper('cybersource_core')->getReportPassword(),
            'startDate' => gmdate('Y-m-d', strtotime('-1 day')),
            'startTime' => gmdate('H:i:s', strtotime('+1 hour')),
            'endDate' => gmdate('Y-m-d'),
            'endTime' => gmdate('H:i:s')
        );
    }

    protected function getPendingOrderIds()
    {
        $orders = Mage::getModel('sales/order')->getCollection()
            ->addFieldToFilter('store_id', Mage::app()->getStore()->getId())
            ->addFieldToFilter('state', Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW)
            ->addFieldToFilter('status', Mage_Sales_Model_Order::STATUS_FRAUD)
            ->addFieldToFilter('created_at', array(
                'from' => strtotime('-2 weeks'),
                'to' => time(),
                'datetime' => true
            ));

        $orderIds = array();

        foreach ($orders as $order) {
            if (! in_array($order->getPayment()->getMethod(), $this->allowedPaymentMethods)) {
                continue;
            }
            $orderIds[] = $order->getIncrementId();
        }

        return $orderIds;
    }

    /**
     * @param string $orderId
     * @param string $decision
     * @return $this
     * @throws Exception
     */
    protected function resolvePendingOrder($orderId, $decision)
    {
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
        if (! $order->getId()) {
            $this->log('Order #' . $orderId . ' was not found.', true);
            return $this;
        }

        if ($decision == self::CONVERSION_ACCEPT_STATUS) {

            // just remove fraud status for echeck, do not accept payment
            if ($order->getPayment()->getMethod() == Cybersource_Cybersource_Model_SOPWebMobile_Payment_Echeck::CODE) {
                $order->setStatus(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW)->save();
                $this->log('ECheck order #' . $orderId . ' was marked as not fraudulent.', true);

                return $this;
            }

            $order->getPayment()
                ->setNotificationResult(1)
                ->setTransactionId($order->getPayment()->getLastTransId())
                ->registerPaymentReviewAction('accept', false);

            $order->save();

            $this->log('Order #' . $orderId . ' was accepted.', true);
            return $this;
        }

        if ($decision == self::CONVERSION_REJECT_STATUS) {
            $order->getPayment()
                ->setNotificationResult(1)
                ->setTransactionId($order->getPayment()->getLastTransId())
                ->registerPaymentReviewAction('deny', false);

            $order->save();

            $this->log('Order #' . $orderId . ' was declined.', true);
            return $this;
        }

        $this->log('Unknown decision (' . $decision . ') for order #' . $orderId, true);

        return $this;
    }

    /**
     * @param string $message
     * @param bool $force
     * @return $this
     */
    protected function log($message, $force = false)
    {
        if (!Mage::helper('cybersource_core')->getIsReportLogEnabled() && !$force) {
            return $this;
        }

        Mage::log($message, null, self::CONVERSION_LOG, $force);

        return $this;
    }
}
