<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_ApplePay_Pay_Authorize extends Cybersource_Cybersource_Model_ApplePay_Pay_AbstractApiCall
{
    /**
     * Authorize the transaction through the Magento payment mechanism
     *
     * @param Varien_Object $payment
     * @return $this
     * @throws \Exception on insufficient information received
     */
    public function authorize(Varien_Object $payment)
    {
        if ($payment instanceof Mage_Sales_Model_Order_Payment) {
            $token = $payment->getAdditionalInformation('token');
            if (!is_array($token)) {
                $message = 'Token data is not an array: %s';
                $this->getHelper()->log(sprintf($message, gettype($token)), Zend_Log::ERR);
                Mage::throwException($this->getHelper()->__($message, gettype($token)));
            }
            $request = new Cybersource_Cybersource_Model_ApplePay_Request_Payload($token);
            $order = $payment->getOrder();
            if ($order instanceof Mage_Sales_Model_Order) {
                $result = $this->callAuthorize($request, $order);
                if (!$result->isSuccess()) {
                    $allowSimulator = $this->getHelper()->getAllowSimulator();
                    $isSimulator = $request->isSimulator();
                    // Transaction failed and the transaction is not a simulated request
                    if (!($allowSimulator && $isSimulator)) {
                        $this->getHelper()->log('Unable to process payment: ' . print_r($result->getRawResult(), true));
                        Mage::throwException($this->getHelper()->__('Unable to process payment'));
                    }
                    $payload = new stdClass();
                    $payload->decision = Cybersource_Cybersource_Model_ApplePay_Api_Result_Authorize::RESULT_DECISION_ACCEPT;
                    $payload->reasonCode = 100;
                    $payload->requestID = uniqid();
                    $result->setApiResult($payload);
                }
                $payment->setAdditionalInformation('response', $result->getRawResult());
                $payment->setCcType($result->getNetwork());
                $payment->setCcExpMonth($result->getExpirationMonth());
                $payment->setCcExpYear($result->getExpirationYear());
                $payment->setIsTransactionClosed(0);
                $payment->setLastTransId($result->getRequestId());
                $payment->setCcOwner($request->getDisplayName());
                $payment->setCybersourcesopSaveToken('true');
                $payment->setCybersourceToken($result->getRequestToken());
                $payment->setTransactionId($result->getRequestId());
                $reviewRequired = $result->isReviewRequired();
                if ($reviewRequired) {
                    $payment->setIsTransactionPending(true);
                    $payment->setIsFraudDetected(true);
                }
                return $this;
            }
        }
        $message = 'authorize() called, but had insufficient payment information';
        $this->getHelper()->log($message, Zend_Log::ERR);
        Mage::throwException($this->getHelper()->__($message));
    }

    /**
     * @param Cybersource_Cybersource_Model_ApplePay_Request_Payload $request
     * @param Mage_Sales_Model_Order $order
     * @return Cybersource_Cybersource_Model_ApplePay_Api_Result_Authorize
     * @throws Exception on malformed request
     */
    protected function callAuthorize(Cybersource_Cybersource_Model_ApplePay_Request_Payload $request, Mage_Sales_Model_Order $order)
    {
        $isValid = $request->isValid();
        $allowSimulator = $this->getHelper()->getAllowSimulator();
        $isSimulator = $request->isSimulator();
        if ($isValid || ($allowSimulator && $isSimulator)) {
            $adapter = $this->getFactory()->factory(Cybersource_Cybersource_Model_ApplePay_Api_Factory::TYPE_AUTHORIZE);
            if ($adapter instanceof Cybersource_Cybersource_Model_ApplePay_Api_Action_Authorize) {
                $adapter->prepare($request, $order);
                $result = $adapter->send($isValid && !$isSimulator);
                return $result;
            }
        }
        $message = 'Unable to process request due to malformed request';
        $this->getHelper()->log($message . ': ' . print_r($request->getParams()), Zend_Log::CRIT);
        Mage::throwException($this->getHelper()->__($message));
    }
}
