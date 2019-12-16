<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_ApplePay_Api_Action_Refund extends Cybersource_Cybersource_Model_ApplePay_Api_AbstractAdapter
{
    /**
     * The transaction ID
     *
     * @var string
     */
    private $requestId;

    /**
     * Get the order object
     *
     * @var Mage_Sales_Model_Order
     */
    private $order;

    /**
     * The refund amount
     *
     * @var float
     */
    private $refundAmount;

    /**
     * Local cache for the result object
     *
     * @var Cybersource_Cybersource_Model_ApplePay_Api_Result_Capture
     */
    private $result;

    /**
     * Prepare the object for the API call.
     *
     * @param $requestId
     * @param Mage_Sales_Model_Order $order
     * @param $refundAmount
     */
    public function prepare(
        $requestId,
        Mage_Sales_Model_Order $order,
        $refundAmount
    )
    {
        $this->requestId = $requestId;
        $this->order = $order;
        $this->refundAmount = $refundAmount;
    }

    /**
     * Retrieve the request ID/transaction ID
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
     * Get the order object
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
     * Get the amount to refund
     *
     * @return float
     * @throws Exception When expected data does not exist
     */
    protected function getRefundAmount()
    {
        if (!$this->refundAmount) {
            Mage::throwException($this->getHelper()->__('Capture action does not have an amount provided'));
        }
        return $this->refundAmount;
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
        $order = $this->getOrder();

        // Add the addresses, both shipping and billing
        $addressDecorator = $this->getAddressDecorator();
        if ($addressDecorator->isValidAddress($order->getBillingAddress())) {
            $addressDecorator->decorate($order->getBillingAddress(), $payload, 'billTo');
        }
        if ($addressDecorator->isValidAddress($order->getShippingAddress())) {
            $addressDecorator->decorate($order->getShippingAddress(), $payload, 'shipTo');
        }

        $lineItemDecorator = $this->getLineItemDecorator();
        $lineItemDecorator->decorate($order, $payload);

        $ccCreditService = new \stdClass();
        $ccCreditService->run = "true";
        $ccCreditService->captureRequestID = $this->getRequestId();
        $payload->ccCreditService = $ccCreditService;

        $purchaseTotals = new stdClass();
        $purchaseTotals->currency = $order->getStoreCurrencyCode();
        $purchaseTotals->grandTotalAmount = $this->getRefundAmount();
        $payload->purchaseTotals = $purchaseTotals;

        $payload->merchantReferenceCode = $order->getIncrementId();

        return $payload;
    }

    /**
     * Get the decorator for the address
     *
     * @return Cybersource_Cybersource_Model_ApplePay_Api_Action_Decorator_Address
     */
    public function getAddressDecorator()
    {
        return new Cybersource_Cybersource_Model_ApplePay_Api_Action_Decorator_Address();
    }

    /**
     * Get the decorator for adding line items.
     *
     * @return Cybersource_Cybersource_Model_ApplePay_Api_Action_Decorator_LineItems
     */
    public function getLineItemDecorator()
    {
        return new Cybersource_Cybersource_Model_ApplePay_Api_Action_Decorator_LineItems();
    }

    /**
     * Get the result object
     *
     * @return Cybersource_Cybersource_Model_ApplePay_Api_Result_Capture
     */
    public function getResultObject()
    {
        if (!$this->result instanceof Cybersource_Cybersource_Model_ApplePay_Api_Result_Capture) {
            $this->result = new Cybersource_Cybersource_Model_ApplePay_Api_Result_Capture();
        }
        return $this->result;
    }
}
