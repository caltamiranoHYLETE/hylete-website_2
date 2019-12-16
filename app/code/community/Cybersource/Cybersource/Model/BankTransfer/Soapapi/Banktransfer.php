<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_BankTransfer_Soapapi_Banktransfer extends Mage_Core_Model_Abstract
{
    /**
     * @return stdClass
     * @throws Exception
     */
    public function requestIdealBanks()
    {
        $request = $this->iniRequest(Cybersource_Cybersource_Model_BankTransfer_Payment_Ideal::CODE);

        $apOptionsService = new stdClass();
        $apOptionsService->run = "true";
        $request->apOptionsService = $apOptionsService;
        $request->apPaymentType = Cybersource_Cybersource_Model_BankTransfer_Payment_Ideal::CYBERSOURCE_CODE;

        $result = $this->getSoapClient(Cybersource_Cybersource_Model_BankTransfer_Payment_Ideal::CODE)->runTransaction($request);
        if (!$result || $result->reasonCode != Cybersource_Cybersource_Model_BankTransfer_Source_Consts::STATUS_ACCEPT) {
            throw new Exception('Unable to pull iDEAL bank list.');
        }

        return $result;
    }

    /**
     * @return stdClass
     * @throws Exception
     */
    public function requestSale()
    {
        $quote = $this->getQuote();
        $method = $quote->getPayment()->getMethod();

        $request = $this->iniRequest($method, $quote->getReservedOrderId());

        $apSaleService = new stdClass();

        $apSaleService->run = "true";
        $apSaleService->cancelURL = Mage::getUrl('cybersource/bt/cancel', array('_secure' => true));
        $apSaleService->successURL = Mage::getUrl('cybersource/bt/return', array('_secure' => true));
        $apSaleService->failureURL = Mage::getUrl('cybersource/bt/failure', array('_secure' => true));

        $request->apPaymentType = $this->getCybersourcePaymentCode($method);

        if ($method == Cybersource_Cybersource_Model_BankTransfer_Payment_Eps::CODE) {
            if (! $btSwift = $quote->getPayment()->getAdditionalInformation('btSwift')) {
                throw new Exception('Swift code is required.');
            }

            $bankInfo = new stdClass();
            $bankInfo->swiftCode = $btSwift;
            $request->bankInfo = $bankInfo;
        }

        if ($method == Cybersource_Cybersource_Model_BankTransfer_Payment_Ideal::CODE) {
            if (! $btBankId = $quote->getPayment()->getAdditionalInformation('btBankId')) {
                throw new Exception('Bank id is required.');
            }

            $apSaleService->paymentOptionID = $btBankId;
        }

        $request->apSaleService = $apSaleService;
        $request->item = $this->buildLineItems($quote);
        $request->billTo = $this->buildBillingAddress($quote->getBillingAddress());
        if ($shipTo = $this->buildShippingAddress($quote->getShippingAddress())) {
            $request->shipTo = $shipTo;
        }

        $purchaseTotals = new stdClass();
        $purchaseTotals->currency = $quote->getQuoteCurrencyCode();
        $purchaseTotals->grandTotalAmount = $this->formatNumber($quote->getGrandTotal());
        $request->purchaseTotals = $purchaseTotals;

        $storeName = Mage::getStoreConfig('general/store_information/name');

        $invoiceHeader = new stdClass();
        $invoiceHeader->merchantDescriptor = $storeName ? $storeName : 'Online Store';
        $request->invoiceHeader = $invoiceHeader;

        $result = $this->getSoapClient($method)->runTransaction($request);
        if (!$result || $result->reasonCode != Cybersource_Cybersource_Model_BankTransfer_Source_Consts::STATUS_ACCEPT) {
            throw new Exception('An error occured while processing your bank transfer payment.');
        }

        $saleStatus = $result->apSaleReply->paymentStatus;
        if ($saleStatus != Cybersource_Cybersource_Model_BankTransfer_Source_Consts::STATUS_PROCESSOR_PAYMENT_PENDING
            && $saleStatus != Cybersource_Cybersource_Model_BankTransfer_Source_Consts::STATUS_PROCESSOR_PAYMENT_AUTHORIZED
            && $saleStatus != Cybersource_Cybersource_Model_BankTransfer_Source_Consts::STATUS_PROCESSOR_PAYMENT_SETTLED) {

            throw new Exception(
                'An error occured while processing your bank transfer payment. Sale service failed with status: ' . $saleStatus
            );
        }

        return $result;
    }

    /**
     * @param Varien_Object $payment
     * @return stdClass
     * @throws Exception
     */
    public function requestRefund($payment)
    {
        if (! $saleTransactionId = $payment->getAdditionalInformation('saleRequestID')) {
            throw new Exception('Sale transaction id was not found.');
        }

        /** @var $payment Mage_Sales_Model_Order_Payment */
        $order = $payment->getOrder();
        $method = $payment->getMethod();

        $request = $this->iniRequest($method, $order->getIncrementId());

        $apRefundService = new stdClass();
        $apRefundService->run = "true";
        $apRefundService->refundRequestID = $saleTransactionId;
        $request->apRefundService = $apRefundService;

        $request->apPaymentType = $this->getCybersourcePaymentCode($method);

        $purchaseTotals = new stdClass();
        $purchaseTotals->currency = $order->getOrderCurrencyCode();
        $purchaseTotals->grandTotalAmount = $this->formatNumber($order->getGrandTotal());
        $request->purchaseTotals = $purchaseTotals;

        $result = $this->getSoapClient($method)->runTransaction($request);
        if (!$result || $result->reasonCode != Cybersource_Cybersource_Model_BankTransfer_Source_Consts::STATUS_ACCEPT) {
            throw new Exception('An error occured while processing refund.');
        }

        return $result;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return stdClass
     * @throws Exception
     */
    public function requestCheckStatusService($order)
    {
        $btCode = $order->getPayment()->getMethod();
        $request = $this->iniRequest($btCode, $order->getIncrementId());

        if (! $saleTransactionId = $order->getPayment()->getAdditionalInformation('saleRequestID')) {
            throw new Exception('Sale transaction id was not found.');
        }

        $request->apPaymentType = $this->getCybersourcePaymentCode($btCode);

        $apCheckStatusService = new stdClass();
        $apCheckStatusService->run = "true";
        $apCheckStatusService->checkStatusRequestID = $saleTransactionId;
        $request->apCheckStatusService = $apCheckStatusService;

        $result = $this->getSoapClient($btCode)->runTransaction($request);
        if (!$result || $result->reasonCode != Cybersource_Cybersource_Model_BankTransfer_Source_Consts::STATUS_ACCEPT) {
            throw new Exception('Check status service failed.');
        }

        return $result;
    }

    /**
     * @param $btCode
     * @param null|string $orderId
     * @return stdClass
     * @throws Exception
     */
    private function iniRequest($btCode, $orderId = null)
    {
        $request = new stdClass();

        $request->merchantID = Cybersource_Cybersource_Model_BankTransfer_Source_Consts::getSystemConfig($btCode, 'merchant_id');
        $request->merchantReferenceCode = $orderId ? $orderId : Mage::helper('core')->uniqHash();
        $request->clientLibrary = "PHP";
        $request->clientLibraryVersion = phpversion();

        return $request;
    }

    /**
     * @param Mage_Sales_Model_Quote_Address $billing
     * @param null|string $email
     * @return stdClass
     */
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

        // local ipv6 address workaround
        if ($billTo->ipAddress == '::1') {
            $billTo->ipAddress = '127.0.0.1';
        }

        return $billTo;
    }

    /**
     * @param Mage_Sales_Model_Quote_Address $shipping
     * @return bool|stdClass
     */
    private function buildShippingAddress($shipping)
    {
        if (! $shipping || $this->getQuote()->isVirtual()) {
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
     * @param Mage_Sales_Model_Quote $quote
     * @return array
     */
    private function buildLineItems($quote)
    {
        $i = 0;
        $result = array();

        $items = $quote->getAllVisibleItems();

        foreach ($items as $item) {
            ${"item" . $i} = new stdClass();

            ${"item" . $i}->id = $quote->getItemsCount() + $i;
            ${"item" . $i}->unitPrice = $this->formatNumber($item->getPriceInclTax() - $item->getTaxAmount());
            ${"item" . $i}->taxAmount = $this->formatNumber($item->getTaxAmount());
            ${"item" . $i}->totalAmount = $this->formatNumber($item->getRowTotalInclTax());
            ${"item" . $i}->quantity = $this->formatNumber($item->getQty(), 0);
            ${"item" . $i}->productSKU = $item->getSku();
            ${"item" . $i}->productName = $item->getName();

            $result[] = ${"item" . $i};

            if ($shippingAddress = $quote->getShippingAddress()) {
                ${"item" . $i}->taxAmount = $this->formatNumber($shippingAddress->getTaxAmount());
            }

            $i++;
        }

        return $result;
    }

    /**
     * @param string $btCode
     * @return Cybersource_Cybersource_Model_Core_Soap_Client
     */
    private function getSoapClient($btCode)
    {
        $wsdlPath = Mage::helper('cybersource_core')->getWsdlUrl();
        $client = new Cybersource_Cybersource_Model_Core_Soap_Client($wsdlPath);

        $client->setBtCode($btCode);
        $client->setContext(self::class);
        $client->setLogFilename(Cybersource_Cybersource_Helper_BankTransfer_Data::LOGFILE);
        $client->setPreventLogFlag(!Mage::helper('cybersourcebanktransfer')->isDebugMode());

        return $client;
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

    private function getCybersourcePaymentCode($methodCode)
    {
        switch ($methodCode) {
            case Cybersource_Cybersource_Model_BankTransfer_Payment_Sofort::CODE:
                return Cybersource_Cybersource_Model_BankTransfer_Payment_Sofort::CYBERSOURCE_CODE;
            case Cybersource_Cybersource_Model_BankTransfer_Payment_Bancontact::CODE:
                return Cybersource_Cybersource_Model_BankTransfer_Payment_Bancontact::CYBERSOURCE_CODE;
            case Cybersource_Cybersource_Model_BankTransfer_Payment_Giropay::CODE:
                return Cybersource_Cybersource_Model_BankTransfer_Payment_Giropay::CYBERSOURCE_CODE;
            case Cybersource_Cybersource_Model_BankTransfer_Payment_Eps::CODE:
                return Cybersource_Cybersource_Model_BankTransfer_Payment_Eps::CYBERSOURCE_CODE;
            case Cybersource_Cybersource_Model_BankTransfer_Payment_Ideal::CODE:
                return Cybersource_Cybersource_Model_BankTransfer_Payment_Ideal::CYBERSOURCE_CODE;
            default:
                throw new Exception('Unknown bank transfer method: ' . $methodCode);
        }
    }
}
