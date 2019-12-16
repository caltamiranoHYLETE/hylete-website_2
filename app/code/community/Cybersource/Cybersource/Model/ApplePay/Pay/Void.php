<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_ApplePay_Pay_Void extends Cybersource_Cybersource_Model_ApplePay_Pay_AbstractApiCall
{
    /**
     * Void the payment
     *
     * @param Varien_Object $payment
     * @return $this
     * @throws Exception When insufficient payment information has been provided
     */
    public function void(Varien_Object $payment)
    {
        if ($payment instanceof Mage_Sales_Model_Order_Payment) {
            $response = $payment->getAdditionalInformation('response');
            $requestId = $response['requestID'];
            $order = $payment->getOrder();
            if ($order instanceof Mage_Sales_Model_Order) {
                $result = $this->callVoid($requestId, $order);
                $this->handleResult($result, $payment, $order);
                return $this;
            }
        }
        $message = 'capture() called, but had insufficient payment information';
        $this->getHelper()->log($message, Zend_Log::ERR);
        Mage::throwException($this->getHelper()->__($message));
    }

    /**
     * Parse the result from the API
     *
     * @param Cybersource_Cybersource_Model_ApplePay_Api_Result_AbstractResult $result
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param Mage_Sales_Model_Order $order
     * @throws Exception When unable to boid the transaction
     */
    protected function handleResult(
        Cybersource_Cybersource_Model_ApplePay_Api_Result_AbstractResult $result,
        Mage_Sales_Model_Order_Payment $payment,
        Mage_Sales_Model_Order $order
    ) {
        if (!$result->isSuccess()) {
            $order->addStatusHistoryComment($this->getHelper()->__('Unable to void transaction'));
            $this->getHelper()->log('Unable to void: ' . print_r($result->getRawResult(), true));
            Mage::throwException($this->getHelper()->__('Unable to void transaction'));
        }
        if (!$result instanceof Cybersource_Cybersource_Model_ApplePay_Api_Result_Capture) {
            $order->addStatusHistoryComment($this->getHelper()->__('ERROR: Unknown return type'));
        }
    }

    /**
     * Actually call the API adapter
     *
     * @param $requestId
     * @param Mage_Sales_Model_Order $order
     * @return Cybersource_Cybersource_Model_ApplePay_Api_Result_AbstractResult
     * @throws Exception When the request ID is missing
     */
    protected function callVoid($requestId, Mage_Sales_Model_Order $order)
    {
        if ($requestId) {
            $adapter = $this->getFactory()->factory(Cybersource_Cybersource_Model_ApplePay_Api_Factory::TYPE_VOID);
            if ($adapter instanceof Cybersource_Cybersource_Model_ApplePay_Api_Action_Void) {
                $adapter->prepare($requestId, $order);
                $result = $adapter->send();
                return $result;
            }
        }
        $message = 'Unable to process request.  The request ID is missing';
        $this->getHelper()->log($message . ': ' . print_r($requestId), Zend_Log::CRIT);
        Mage::throwException($this->getHelper()->__($message));
    }
}
