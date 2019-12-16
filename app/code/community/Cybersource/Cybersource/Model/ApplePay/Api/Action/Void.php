<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_ApplePay_Api_Action_Void extends Cybersource_Cybersource_Model_ApplePay_Api_AbstractAdapter
{
    /**
     * The request ID
     *
     * @var string
     */
    private $requestId;

    /**
     * The current order object
     *
     * @var Mage_Sales_Model_Order
     */
    private $order;

    /**
     * Local cache for the result object
     *
     * @var Cybersource_Cybersource_Model_ApplePay_Api_Result_Void
     */
    private $result;

    /**
     * Prepare the object for the API call
     *
     * @param $requestId
     * @param Mage_Sales_Model_Order $order
     */
    public function prepare(
        $requestId,
        Mage_Sales_Model_Order $order
    )
    {
        $this->requestId = $requestId;
        $this->order = $order;
    }

    /**
     * Get the transaction ID
     *
     * @return string
     * @throws Exception When expected data does not exist
     */
    protected function getRequestId()
    {
        if (!$this->requestId) {
            Mage::throwException($this->getHelper()->__('Request ID not provided'));
        }
        return $this->requestId;
    }

    /**
     * Retrieves the current order object
     *
     * @return Mage_Sales_Model_Order
     * @throws Exception When expected data does not exist
     */
    protected function getOrder()
    {
        if (!$this->order instanceof Mage_Sales_Model_Order) {
            Mage::throwException($this->getHelper()->__('Capture action does not have an order provided'));
        }
        return $this->order;
    }

    /**
     * Builds payload that will be sent.  By default the request will follow:
     * https://ics2wstest.ic3.com/commerce/1.x/transactionProcessor/CyberSourceTransaction_1.115.xsd
     *
     * @return stdClass
     * @throws Exception When expected data does not exist
     */
    protected function buildRequest()
    {
        $payload = $this->getBasePayload();

        $voidService = new \stdClass();
        $voidService->run = "true";
        $voidService->voidRequestID = $this->getRequestId();
        $payload->voidService = $voidService;

        $payload->merchantReferenceCode = $this->getOrder()->getIncrementId();

        return $payload;
    }

    /**
     * Get the result object
     *
     * @return Cybersource_Cybersource_Model_ApplePay_Api_Result_Void
     */
    public function getResultObject()
    {
        if (!$this->result instanceof Cybersource_Cybersource_Model_ApplePay_Api_Result_Void) {
            $this->result = new Cybersource_Cybersource_Model_ApplePay_Api_Result_Void();
        }
        return $this->result;
    }
}
