<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_VisaCheckout_Soapapi_Visacheckout extends Mage_Core_Model_Abstract
{
    const CC_CAPTURE_SERVICE_TOTAL_COUNT = 99;

    public function requestDataService($vcOrderId)
    {
        $request = $this->iniRequest();

        $soapClient = $this->getSoapClient();

        // response may contain full PAN
        $soapClient->setPreventLogFlag();

        $vcDataService = new stdClass();
        $vcDataService->run = "true";
        $request->getVisaCheckoutDataService = $vcDataService;

        if ($this->useWebsiteCurrency()) {
            $amount = $this->getQuote()->getGrandTotal();
            $currencyCode = $this->getQuote()->getQuoteCurrencyCode();
        } else {
            $amount = $this->getQuote()->getBaseGrandTotal();
            $currencyCode = $this->getQuote()->getBaseCurrencyCode();
        }

        $purchaseTotals = new stdClass();
        $purchaseTotals->currency = $currencyCode;
        $purchaseTotals->grandTotalAmount = $amount;
        $request->purchaseTotals = $purchaseTotals;

        $vcService = new stdClass();
        $vcService->orderID = $vcOrderId;
        $request->vc = $vcService;

        $result = $soapClient->runTransaction($request);
        if (!$result || $result->reasonCode != Cybersource_Cybersource_Model_VisaCheckout_Source_Consts::STATUS_ACCEPT) {
            throw new Exception('Unable to retrieve VC data.');
        }

        return $result;
    }

    /**
     * @param Varien_Object $payment
     * @return mixed
     * @throws Exception
     */
    public function requestAuthorization($payment)
    {
        if (! $vcOrderId = $payment->getAdditionalInformation('vcOrderId')) {
            throw new Exception('VC order id is undefined.');
        }

        /** @var $order Mage_Sales_Model_Order */
        $order = $payment->getOrder();

        $request = $this->iniRequest($order->getIncrementId());

        $soapClient = $this->getSoapClient();

        $ccAuthService = new stdClass();
        $ccAuthService->run = "true";
        $ccAuthService->commerceIndicator = "internet";
        $request->ccAuthService = $ccAuthService;

        if ($shipTo = $this->buildShippingAddress($order->getShippingAddress())) {
            $request->shipTo = $shipTo;
        }

        $request->billTo = $this->buildBillingAddress($order->getBillingAddress(), $this->getQuote()->getCustomerEmail());
        $request->item = $this->buildLineItems($order);

        if ($this->useWebsiteCurrency()) {
            $amount = $order->getGrandTotal();
            $currencyCode = $order->getOrderCurrencyCode();
        } else {
            $amount = $order->getBaseGrandTotal();
            $currencyCode = $order->getBaseCurrencyCode();
        }

        $purchaseTotals = new stdClass();
        $purchaseTotals->currency = $currencyCode;
        $purchaseTotals->grandTotalAmount = $amount;
        $request->purchaseTotals = $purchaseTotals;

        $vc = new stdClass();
        $vc->orderID = $vcOrderId;
        $request->vc = $vc;

        $decisionManager = new stdClass();
        $decisionManager->enabled = Mage::helper('cybersource_core')->getIsDmEnabled() ? "true" : "false";
        $request->decisionManager = $decisionManager;

        $request->merchantDefinedData = $this->buildMerchantDefinedFields($order);

        return $soapClient->runTransaction($request);
    }

    public function requestCapture($payment, $amount)
    {
        if (! $vcOrderId = $payment->getAdditionalInformation('vcOrderId')) {
            throw new Exception('VC order id is undefined.');
        }

        if (! $authRequestId = $payment->getAdditionalInformation('authRequestID')) {
            throw new Exception('Auth request was not found.');
        }

        /** @var $order Mage_Sales_Model_Order */
        $order = $payment->getOrder();

        $request = $this->iniRequest($order->getIncrementId());

        $soapClient = $this->getSoapClient();

        $ccCaptureService = new stdClass();
        $ccCaptureService->run = "true";
        $ccCaptureService->authRequestID = $authRequestId;

        $currencyCode = $order->getBaseCurrencyCode();
        $isCustomCurrencyAmount = false;

        if ($this->useWebsiteCurrency()) {
            /** @var $invoice Mage_Sales_Model_Order_Invoice */
            foreach ($order->getInvoiceCollection() as $invoice) {
                if ($invoice->getRequestedCaptureCase() == Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE) {
                    $amount = $this->formatNumber($invoice->getGrandTotal());
                    $currencyCode = $invoice->getOrderCurrencyCode();
                    $isCustomCurrencyAmount = true;
                    break;
                }
            }
        }

        $captureSequence = $order->getInvoiceCollection()->getSize() + 1;
        $isPartialCapture = ($isCustomCurrencyAmount ? $order->getGrandTotal() : $order->getBaseGrandTotal()) > $amount;
        $isFinalCapture = ($isCustomCurrencyAmount ? $order->getTotalDue() : $order->getBaseTotalDue()) <= $amount;

        if ($isPartialCapture) {
            $ccCaptureService->totalCount = static::CC_CAPTURE_SERVICE_TOTAL_COUNT;
            $ccCaptureService->sequence = $isFinalCapture ? static::CC_CAPTURE_SERVICE_TOTAL_COUNT : $captureSequence;
        }

        $request->ccCaptureService = $ccCaptureService;

        if ($shipTo = $this->buildShippingAddress($order->getShippingAddress())) {
            $request->shipTo = $shipTo;
        }

        $request->billTo = $this->buildBillingAddress($order->getBillingAddress());
        $request->item = $this->buildLineItems($order);

        $purchaseTotals = new stdClass();
        $purchaseTotals->currency = $currencyCode;
        $purchaseTotals->grandTotalAmount = $amount;
        $request->purchaseTotals = $purchaseTotals;

        $vc = new stdClass();
        $vc->orderID = $vcOrderId;
        $request->vc = $vc;

        return $soapClient->runTransaction($request);
    }


    public function requestSale($payment, $amount)
    {
        if (! $vcOrderId = $payment->getAdditionalInformation('vcOrderId')) {
            throw new Exception('VC order id is undefined.');
        }

        /** @var $order Mage_Sales_Model_Order */
        $order = $payment->getOrder();

        $request = $this->iniRequest($order->getIncrementId());

        $soapClient = $this->getSoapClient();

        $ccAuthService = new stdClass();
        $ccAuthService->run = "true";
        $ccAuthService->commerceIndicator = "internet";
        $request->ccAuthService = $ccAuthService;

        $ccCaptureService = new stdClass();
        $ccCaptureService->run = "true";
        $request->ccCaptureService = $ccCaptureService;

        if ($shipTo = $this->buildShippingAddress($order->getShippingAddress())) {
            $request->shipTo = $shipTo;
        }

        $request->billTo = $this->buildBillingAddress($order->getBillingAddress(), $this->getQuote()->getCustomerEmail());
        $request->item = $this->buildLineItems($order);

        $currencyCode = $order->getBaseCurrencyCode();

        if ($this->useWebsiteCurrency()) {
            $amount = $order->getGrandTotal();
            $currencyCode = $order->getOrderCurrencyCode();
        }

        $purchaseTotals = new stdClass();
        $purchaseTotals->currency = $currencyCode;
        $purchaseTotals->grandTotalAmount = $amount;
        $request->purchaseTotals = $purchaseTotals;

        $vc = new stdClass();
        $vc->orderID = $vcOrderId;
        $request->vc = $vc;

        $decisionManager = new stdClass();
        $decisionManager->enabled = Mage::helper('cybersource_core')->getIsDmEnabled() ? "true" : "false";
        $request->decisionManager = $decisionManager;

        $request->merchantDefinedData = $this->buildMerchantDefinedFields($order);

        return $soapClient->runTransaction($request);
    }

    public function requestRefund($payment, $amount)
    {
        if (! $vcOrderId = $payment->getAdditionalInformation('vcOrderId')) {
            throw new Exception('VC order id is undefined.');
        }

        if (! $captureRequestId = $payment->getAdditionalInformation('captureRequestID')) {
            throw new Exception('Capture transaction was not found.');
        }

        /** @var $order Mage_Sales_Model_Order */
        $order = $payment->getOrder();

        $request = $this->iniRequest($order->getIncrementId());

        $soapClient = $this->getSoapClient();

        $currencyCode = $order->getBaseCurrencyCode();

        if ($this->useWebsiteCurrency()) {
            /** @var $creditMemo Mage_Sales_Model_Order_Creditmemo */
            $creditMemo = $payment->getCreditmemo();
            $amount = $this->formatNumber($creditMemo->getGrandTotal());
            $currencyCode = $order->getOrderCurrencyCode();
        }

        $ccCreditService = new stdClass();
        $ccCreditService->run = "true";
        $ccCreditService->captureRequestID = $captureRequestId;
        $ccCreditService->captureRequestToken = $payment->getAdditionalInformation('captureRequestToken');
        $request->ccCreditService = $ccCreditService;

        $request->billTo = $this->buildBillingAddress($order->getBillingAddress());

        $purchaseTotals = new stdClass();
        $purchaseTotals->currency = $currencyCode;
        $purchaseTotals->grandTotalAmount = $amount;
        $request->purchaseTotals = $purchaseTotals;

        $vc = new stdClass();
        $vc->orderID = $vcOrderId;
        $request->vc = $vc;

        return $soapClient->runTransaction($request);
    }

    public function requestVoid($payment)
    {
        if (! $vcOrderId = $payment->getAdditionalInformation('vcOrderId')) {
            throw new Exception('VC order id is undefined.');
        }

        if (! $authRequestId = $payment->getAdditionalInformation('authRequestID')) {
            throw new Exception('Auth request was not found.');
        }

        /** @var $order Mage_Sales_Model_Order */
        $order = $payment->getOrder();

        $request = $this->iniRequest($order->getIncrementId());

        $soapClient = $this->getSoapClient();

        $ccAuthReversalService = new stdClass();
        $ccAuthReversalService->run = "true";
        $ccAuthReversalService->authRequestID = $authRequestId;
        $request->ccAuthReversalService = $ccAuthReversalService;

        if ($this->useWebsiteCurrency()) {
            $amount = $order->getGrandTotal();
            $currencyCode = $order->getOrderCurrencyCode();
        } else {
            $amount = $order->getBaseGrandTotal();
            $currencyCode = $order->getBaseCurrencyCode();
        }

        $purchaseTotals = new stdClass();
        $purchaseTotals->currency = $currencyCode;
        $purchaseTotals->grandTotalAmount = $amount;
        $request->purchaseTotals = $purchaseTotals;

        $vc = new stdClass();
        $vc->orderID = $vcOrderId;
        $request->vc = $vc;

        return $soapClient->runTransaction($request);
    }

    public function canVoid(Varien_Object $payment)
    {
        $paymentInfo = $payment->getAdditionalInformation();
        return !isset($paymentInfo['captureTransactionID'])
            && !isset($paymentInfo['reversalRequestID']);
    }

    protected function iniRequest($orderId = null)
    {
        $request = new stdClass();

        $request->merchantID = Mage::helper('cybersource_core')->getMerchantId();
        $request->merchantReferenceCode = $orderId ? $orderId : Mage::helper('core')->uniqHash();
        $request->clientLibrary = "PHP";
        $request->clientLibraryVersion = phpversion();
        $request->paymentSolution = 'visacheckout';

        return $request;
    }

    private function buildBillingAddress($billing, $email = null)
    {
        if (! $email) {
            $email = $billing->getEmail();
        }

        $billTo = new stdClass();

        $billTo->company = substr($billing->getCompany(), 0, 40);
        $billTo->firstName = $billing->getFirstname();
        $billTo->lastName = $billing->getLastname();
        $billTo->street1 = $billing->getStreet(1);
        $billTo->street2 = $billing->getStreet(2);
        $billTo->city = $billing->getCity();
        $billTo->state = $billing->getRegion();
        $billTo->postalCode = $billing->getPostcode();
        $billTo->country = $billing->getCountry();
        $billTo->phoneNumber = $this->cleanPhoneNum($billing->getTelephone());
        $billTo->email = ($email ? $email : Mage::getStoreConfig('trans_email/ident_general/email'));
        $billTo->ipAddress = Mage::helper('core/http')->getRemoteAddr();

        return $billTo;
    }

    private function buildShippingAddress($shipping)
    {
        if (! $shipping) {
            return false;
        }

        $shipTo = new stdClass();

        $shipTo->company = substr($shipping->getCompany(), 0, 40);
        $shipTo->firstName = $shipping->getFirstname();
        $shipTo->lastName = $shipping->getLastname();
        $shipTo->street1 = $shipping->getStreet(1);
        $shipTo->street2 = $shipping->getStreet(2);
        $shipTo->city = $shipping->getCity();
        $shipTo->state = $shipping->getRegion();
        $shipTo->postalCode = $shipping->getPostcode();
        $shipTo->country = $shipping->getCountry();
        $shipTo->phoneNumber = $this->cleanPhoneNum($shipping->getTelephone());

        return $shipTo;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    protected function buildLineItems($order)
    {
        $i = 0;
        $result = array();

        $items = $order->getAllVisibleItems();

        /** @var $item Mage_Sales_Model_Order_Item */
        foreach ($items as $item) {
            ${"item" . $i} = new stdClass();
            ${"item" . $i}->id = $i;

            if ($this->useWebsiteCurrency()) {
                ${"item" . $i}->unitPrice = $this->formatNumber($item->getPriceInclTax() - $item->getTaxAmount());
                ${"item" . $i}->taxAmount = $this->formatNumber($item->getTaxAmount());
            } else {
                ${"item" . $i}->unitPrice = $this->formatNumber($item->getBasePrice());
                ${"item" . $i}->taxAmount = $this->formatNumber($item->getBaseTaxAmount());
            }

            ${"item" . $i}->quantity = $this->formatNumber($item->getQtyOrdered(), 0);
            ${"item" . $i}->productSKU = $item->getSku();
            ${"item" . $i}->productName = $item->getName();

            $result[] = ${"item" . $i};

            if ($shippingAddress = $order->getShippingAddress()) {
                $taxAmount = $this->useWebsiteCurrency() ? $shippingAddress->getTaxAmount() : $shippingAddress->getBaseTaxAmount();
                ${"item" . $i}->taxAmount = $this->formatNumber($taxAmount);
            }

            $i++;
        }

        return $result;
    }

    private function buildMerchantDefinedFields($order)
    {
        if (! Mage::helper('cybersource_core')->getIsDmMerchantDataEnabled()) {
            return $this;
        }

        $result = new stdClass();
        $definedFields = array();

        /** @var $order Mage_Sales_Model_Order */
        $orderData = $order->getData();

        try {
            $definedFields = Zend_Serializer::unserialize(Mage::helper('cybersource_core')->getDmMerchantDataFields());
        } catch (Exception $e) { }

        foreach ($definedFields as $key => $value) {

            $customMddValue = false;

            if (strpos($value['value'], '^') === 0) {
                $customMddValue = $this->processCustomMddField($order, $value['value']);
            }

            if (!$customMddValue && !isset($orderData[$value['value']])) {
                continue;
            }

            $merchantField = 'field' . $value['mdd'];
            $mddValue = $customMddValue ? $customMddValue : $orderData[$value['value']];

            $result->$merchantField = $mddValue;
        }

        return $result;
    }

    private function getSoapClient()
    {
        $wsdlPath = Mage::helper('cybersource_core')->getWsdlUrl();
        $client = new Cybersource_Cybersource_Model_Core_Soap_Client($wsdlPath);

        $client->setContext(self::class);
        $client->setLogFilename(Cybersource_Cybersource_Helper_VisaCheckout_Data::LOGFILE);
        $client->setPreventLogFlag(!Mage::helper('cybersourcevisacheckout')->isDebugMode());

        return $client;
    }

    /**
     * @return bool
     */
    private function useWebsiteCurrency()
    {
        $defaultCurrencyType = Cybersource_Cybersource_Model_VisaCheckout_Source_Consts::getSystemConfig('default_currency');
        return $defaultCurrencyType == Cybersource_Cybersource_Model_VisaCheckout_Source_Currency::DEFAULT_CURRENCY;
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    private function getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    /**
     * @param string|int $num
     * @param int $pre
     * @return string
     */
    private function formatNumber($num, $pre = 2)
    {
        return number_format($num, $pre, '.', '');
    }

    /**
     * @param string $phoneNumberIn
     * @return string|mixed
     */
    private function cleanPhoneNum($phoneNumberIn)
    {
        $filtered = preg_replace("/[^0-9]/","", $phoneNumberIn);

        if (strlen($filtered) < 6) {
            return '000000000';
        } else {
            return $filtered;
        }
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param string $command
     * @return string
     */
    private function processCustomMddField($order, $command)
    {
        $explodedCommand = explode(':', $command);
        $action = $explodedCommand[0];
        $params = $explodedCommand[1];

        switch ($action) {
            case '^isGuest':
                return $order->getCustomerId() ? 'N' : 'Y';
            case '^shippingMethod':
                return $order->getShippingDescription();
            case '^raw':
                return $params;
        }

        return false;
    }
}

    

