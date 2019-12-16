<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_ApplePay_Api_Action_Authorize extends Cybersource_Cybersource_Model_ApplePay_Api_AbstractAdapter
{
    /**
     * The current request payload
     *
     * @var Cybersource_Cybersource_Model_ApplePay_Request_Payload
     */
    private $request;

    /**
     * The current order
     *
     * @var Mage_Sales_Model_Order
     */
    private $order;

    /**
     * Local cache for the result
     *
     * @var Cybersource_Cybersource_Model_ApplePay_Api_Result_Authorize
     */
    private $result;

    /**
     * Provide the required data to process the request.
     *
     * @param Cybersource_Cybersource_Model_ApplePay_Request_Payload $request
     * @param Mage_Sales_Model_Order $order
     */
    public function prepare(Cybersource_Cybersource_Model_ApplePay_Request_Payload $request, Mage_Sales_Model_Order $order)
    {
        $this->request = $request;
        $this->order = $order;
    }

    /**
     * Retrieves the request object
     *
     * @return Cybersource_Cybersource_Model_ApplePay_Request_Payload
     * @throws Exception When expected data does not exist
     */
    protected function getRequest()
    {
        if (!$this->request instanceof Cybersource_Cybersource_Model_ApplePay_Request_Payload) {
            Mage::throwException($this->getHelper()->__('Authorize action does not have a request payload provided'));
        }
        return $this->request;
    }

    /**
     * Retrieves the provided order
     *
     * @return Mage_Sales_Model_Order
     * @throws Exception When expected data does not exist
     */
    protected function getOrder()
    {
        if (!$this->order instanceof Mage_Sales_Model_Order) {
            Mage::throwException($this->getHelper()->__('Authorize action does not have an order provided'));
        }
        return $this->order;
    }

    /**
     * Get the decorator that adds the address to the API call
     *
     * @return Cybersource_Cybersource_Model_ApplePay_Api_Action_Decorator_Address
     */
    public function getAddressDecorator()
    {
        return new Cybersource_Cybersource_Model_ApplePay_Api_Action_Decorator_Address();
    }

    /**
     * Get the decorator that adds individual line items to the API call.
     *
     * @return Cybersource_Cybersource_Model_ApplePay_Api_Action_Decorator_LineItems
     */
    public function getLineItemDecorator()
    {
        return new Cybersource_Cybersource_Model_ApplePay_Api_Action_Decorator_LineItems();
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
        $request = $this->getRequest();

        // Add the addresses, both shipping and billing
        $decorator = $this->getAddressDecorator();
        if ($decorator->isValidAddress($order->getBillingAddress())) {
            $decorator->decorate($order->getBillingAddress(), $payload, 'billTo');
            $decorator->decorate($order->getShippingAddress(), $payload, 'shipTo');
        }
        // Add the line items
        $decorator = $this->getLineItemDecorator();
        $decorator->decorate($order, $payload);

        $payload->encryptedPayment = $this->buildEncryptedPayment($this->getRequest());

        $purchaseTotals = new stdClass();
        $purchaseTotals->currency = $order->getStoreCurrencyCode();
        $purchaseTotals->grandTotalAmount = $order->getBaseGrandTotal();
        $payload->purchaseTotals = $purchaseTotals;

        $card = new stdClass();
        $card->cardType = $this->getCardType($request);
        $payload->card = $card;
        $payload->paymentSolution = '001';

        $payload->merchantReferenceCode = $order->getIncrementId();
        $ccAuthService = new \stdClass();
        $ccAuthService->run = "true";
        $payload->ccAuthService = $ccAuthService;

        return $payload;
    }

    /**
     * Build the encryptedPayment node
     *
     * @param Cybersource_Cybersource_Model_ApplePay_Request_Payload $payload
     * @return stdClass
     */
    protected function buildEncryptedPayment(Cybersource_Cybersource_Model_ApplePay_Request_Payload $payload)
    {
        $encryptedPayment = new stdClass();
        $encryptedPayment->descriptor = 'RklEPUNPTU1PTi5BUFBMRS5JTkFQUC5QQVlNRU5U';
        $encryptedPayment->data = base64_encode(json_encode($payload->getPaymentDataNode()));
        $encryptedPayment->encoding = 'Base64';

        return $encryptedPayment;
    }

    /**
     * Get the result object instance
     *
     * @return Cybersource_Cybersource_Model_ApplePay_Api_Result_Authorize
     */
    public function getResultObject()
    {
        if (!$this->result instanceof Cybersource_Cybersource_Model_ApplePay_Api_Result_Authorize) {
            $this->result = new Cybersource_Cybersource_Model_ApplePay_Api_Result_Authorize();
        }
        return $this->result;
    }
}
