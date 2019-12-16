<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_ApplePay_Api_Action_Capture extends Cybersource_Cybersource_Model_ApplePay_Api_AbstractAdapter
{
    /**
     * The request ID for the transaction.
     *
     * @var string
     */
    private $requestId;

    /**
     * The current order
     *
     * @var Mage_Sales_Model_Order
     */
    private $order;

    /**
     * The amount to capture
     *
     * @var float
     */
    private $captureAmount;

    /**
     * Local cache for the result object
     *
     * @var Cybersource_Cybersource_Model_ApplePay_Api_Result_Capture
     */
    private $result;

    /**
     * Prepare the object to make the API call
     *
     * @param $requestId
     * @param Mage_Sales_Model_Order $order
     * @param $captureAmount
     */
    public function prepare(
        $requestId,
        Mage_Sales_Model_Order $order,
        $captureAmount
    )
    {
        $this->requestId = $requestId;
        $this->order = $order;
        $this->captureAmount = $captureAmount;
    }

    /**
     * Retrieve the transaction ID
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
     * Retrieved the provided order
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
     * Retrieve the provided capture amount
     *
     * @return float
     * @throws Exception When expected data does not exist
     */
    protected function getCaptureAmount()
    {
        if (!$this->captureAmount) {
            Mage::throwException($this->getHelper()->__('Capture action does not have an amount provided'));
        }
        return $this->captureAmount;
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

        $ccCaptureService = new \stdClass();
        $ccCaptureService->run = "true";
        $ccCaptureService->authRequestID = $this->getRequestId();
        $payload->ccCaptureService = $ccCaptureService;

        $purchaseTotals = new stdClass();
        $purchaseTotals->currency = $order->getStoreCurrencyCode();
        $purchaseTotals->grandTotalAmount = $this->getCaptureAmount();
        $payload->purchaseTotals = $purchaseTotals;

        $payload->merchantReferenceCode = $order->getIncrementId();

        return $payload;
    }

    /**
     * Retrieve the decorator for adding the address to the API call.
     *
     * @return Cybersource_Cybersource_Model_ApplePay_Api_Action_Decorator_Address
     */
    public function getAddressDecorator()
    {
        return new Cybersource_Cybersource_Model_ApplePay_Api_Action_Decorator_Address();
    }

    /**
     * Get the decorator for providing line items to the API
     *
     * @return Cybersource_Cybersource_Model_ApplePay_Api_Action_Decorator_LineItems
     */
    public function getLineItemDecorator()
    {
        return new Cybersource_Cybersource_Model_ApplePay_Api_Action_Decorator_LineItems();
    }

    /**
     * Build the line items to match the complexType name="Item" at:
     * https://ics2wstest.ic3.com/commerce/1.x/transactionProcessor/CyberSourceTransaction_1.115.xsd
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return array
     */
    protected function buildLineItems(Mage_Sales_Model_Quote $quote)
    {
        $items = $quote->getAllVisibleItems();
        $buyerVat = $quote->getCustomer()->getTaxvat();
        $lineItems = array();
        $taxConfig = Mage::helper('tax')->getConfig();
        /** @var $taxConfig Mage_Tax_Model_Config */
        $taxAfterDiscount = $taxConfig->applyTaxAfterDiscount();

        foreach ($items as $i => $item) {
            /** @var $item Mage_Sales_Model_Quote_Item */
            $lineItem = new \stdClass();
            $quantity = (int)$item->getQty();
            $unitPrice = (float)$item->getBasePrice();
            if ($taxAfterDiscount) {
                $product = $item->getProduct();
                $unitPrice = $product->getPriceModel()->getBasePrice($product, $quantity);
            }

            $sku = $item->getProduct()->getSku();
            $productName = $item->getProduct()->getName();

            $taxCode = $this->getTaxCode($item->getProduct());

            $lineItem->id = $i;
            $lineItem->unitPrice = $unitPrice;
            $lineItem->quantity = (string)$quantity;
            $lineItem->productCode = $taxCode;
            $lineItem->productName = $productName;
            $lineItem->productSKU = $sku;
            $lineItem->currency = $item->getQuote()->getBaseCurrencyCode();
            if ($buyerVat) {
                $lineItem->buyerRegistration = $buyerVat;
            }

            $lineItems[] = $lineItem;
        }

        return $lineItems;
    }

    /**
     * Retrieves the result object
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
