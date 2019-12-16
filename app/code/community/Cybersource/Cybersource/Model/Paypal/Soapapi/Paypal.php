<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_Paypal_Soapapi_Paypal extends Cybersource_Cybersource_Model_Paypal_Soapapi_Abstract
{
    /**
     * @param Mage_Sales_Model_Quote $quote
     * @return stdClass
     * @throws Exception
     */
    public function payPalEcSetService($quote)
    {
        $this->_storeId = $quote->getStoreId();

        $soapClient = $this->getSoapClient();
        $this->buildPayPalSetServiceRequest($quote);

        $result = $soapClient->runTransaction($this->_request);
        if (!$result || $result->reasonCode != Cybersource_Cybersource_Model_Paypal_Source_Consts::STATUS_ACCEPT) {
            throw new Exception('There is an error initializing PayPalExpress Checkout.');
        }

        return $result;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @param string $paypalToken
     * @return stdClass
     * @throws Exception
     */
    public function payPalEcGetDetailsService($quote, $paypalToken)
    {
        $this->_storeId = $quote->getStoreId();

        $soapClient = $this->getSoapClient();
        $this->buildPayPalGetDetailsRequest($quote, $paypalToken);

        $result = $soapClient->runTransaction($this->_request);
        if (!$result || $result->reasonCode != Cybersource_Cybersource_Model_Paypal_Source_Consts::STATUS_ACCEPT) {
            throw new Exception('There is an error retrieving PayPal details.');
        }

        return $result;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return stdClass
     * @throws Exception
     */
    public function payPalEcDoPaymentService($order)
    {
        $soapClient = $this->getSoapClient();

        $payment = $order->getPayment();
        $paypalInfo = $payment->getAdditionalInformation();

        $this->_request = new stdClass();
        $this->_request->merchantID = Mage::helper('cybersource_core')->getMerchantId();
        $this->_request->merchantReferenceCode = $paypalInfo['merchantReferenceCode'];
        $this->_request->clientLibrary = "PHP";
        $this->_request->clientLibraryVersion = phpversion();

        $payPalEcDoPaymentService = new stdClass();
        $payPalEcDoPaymentService->run = "true";
        $payPalEcDoPaymentService->paypalToken = $paypalInfo['paypalToken'];
        $payPalEcDoPaymentService->paypalPayerId = $paypalInfo['payerId'];
        $payPalEcDoPaymentService->paypalEcSetRequestID = $paypalInfo['requestID'];
        $payPalEcDoPaymentService->paypalEcSetRequestToken = $paypalInfo['requestToken'];
        $payPalEcDoPaymentService->paypalCustomerEmail = $paypalInfo['payerEmail'];
        $this->_request->payPalEcDoPaymentService = $payPalEcDoPaymentService;

        $purchaseTotals = new stdClass();
        $purchaseTotals->currency = $order->getBaseCurrencyCode();
        $purchaseTotals->grandTotalAmount = sprintf('%0.2f', $order->getBaseGrandTotal());
        $this->_request->purchaseTotals = $purchaseTotals;

        $shippingAddressToSend = $this->getAddressToCyberSource($order->getStoreId(), "request_shipping_address");
        $billingAddressToSend = $this->getAddressToCyberSource($order->getStoreId(), "request_bill_address");

        if ($shippingAddressToSend == Cybersource_Cybersource_Model_Paypal_Source_AddressDetails::PAYPAL_ADDRESS) {
            $this->_request->shipTo = $this->getPayPalAddressObject($paypalInfo, $order, false);
        } else if ($shippingAddressToSend == Cybersource_Cybersource_Model_Paypal_Source_AddressDetails::MAGENTO_ADDRESS) {
            $shippingAddress = $order->getShippingAddress();
            if ($shippingAddress->getData()) {
                $this->_request->shipTo = $this->getMagentoAddressObject($shippingAddress, $order, false);
            }
        }

        if ($billingAddressToSend == Cybersource_Cybersource_Model_Paypal_Source_AddressDetails::PAYPAL_ADDRESS) {
            $this->_request->billTo = $this->getPayPalAddressObject($paypalInfo, $order, true);
        } else if ($billingAddressToSend == Cybersource_Cybersource_Model_Paypal_Source_AddressDetails::MAGENTO_ADDRESS) {
            $billingAddress = $order->getBillingAddress();
            if ($billingAddress->getData()) {
                $this->_request->billTo = $this->getMagentoAddressObject($billingAddress, $order, true);
            }
        }
        //$this->_request->item	=$this->addLineItems($order->getAllVisibleItems());
        $result = $soapClient->runTransaction($this->_request);

        if (!$result || $result->reasonCode != Cybersource_Cybersource_Model_Paypal_Source_Consts::STATUS_ACCEPT) {
            throw new Exception('There is an error in processing the payment.');
        }

        return $result;
    }

    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param float $amount
     * @return stdClass
     * @throws Exception
     */
    public function payPalDoCaptureService($payment, $amount)
    {
        $soapClient = $this->getSoapClient();
        $paypalInfo = $payment->getAdditionalInformation();

        $this->_request = new stdClass();
        $this->_request->merchantID = Mage::helper('cybersource_core')->getMerchantId();
        $this->_request->merchantReferenceCode = $paypalInfo['merchantReferenceCode'];
        $this->_request->clientLibrary = "PHP";
        $this->_request->clientLibraryVersion = phpversion();

        $payPalDoCaptureService = new stdClass();
        $payPalDoCaptureService->run = "true";
        $payPalDoCaptureService->completeType = "Complete";
        $payPalDoCaptureService->paypalAuthorizationId = $paypalInfo['authTransactionId'];
        $payPalDoCaptureService->paypalEcDoPaymentRequestID = $paypalInfo['authRequestID'];
        $payPalDoCaptureService->paypalEcDoPaymentRequestToken = $paypalInfo['authRequestToken'];
        $this->_request->payPalDoCaptureService = $payPalDoCaptureService;

        $purchaseTotals = new stdClass();
        $purchaseTotals->currency = $paypalInfo['currency'];
        $purchaseTotals->grandTotalAmount = $amount;
        $this->_request->purchaseTotals = $purchaseTotals;

        $result = $soapClient->runTransaction($this->_request);
        if (!$result || $result->reasonCode != Cybersource_Cybersource_Model_Paypal_Source_Consts::STATUS_ACCEPT) {
            throw new Exception('There is an error in processing the payment.');
        }

        return $result;
    }

    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return stdClass
     * @throws Exception
     */
    public function payPalAuthReversalService($payment)
    {
        $soapClient = $this->getSoapClient();
        $paypalInfo = $paypalInfo = $payment->getAdditionalInformation();

        $this->_request = new stdClass();
        $this->_request->merchantID = Mage::helper('cybersource_core')->getMerchantId();
        $this->_request->merchantReferenceCode = $paypalInfo['merchantReferenceCode'];
        $this->_request->clientLibrary = "PHP";
        $this->_request->clientLibraryVersion = phpversion();

        $payPalAuthReversalService = new stdClass();
        $payPalAuthReversalService->run = "true";
        $payPalAuthReversalService->paypalAuthorizationId = $paypalInfo['authTransactionId'];
        $payPalAuthReversalService->paypalEcDoPaymentRequestID = $paypalInfo['authRequestID'];
        $payPalAuthReversalService->paypalEcDoPaymentRequestToken = $paypalInfo['authRequestToken'];
        $this->_request->payPalAuthReversalService = $payPalAuthReversalService;

        $result = $soapClient->runTransaction($this->_request);
        if (!$result || $result->reasonCode != Cybersource_Cybersource_Model_Paypal_Source_Consts::STATUS_ACCEPT) {
            throw new Exception('There is an error in processing the authorization reversal.');
        }

        return $result;
    }

    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param float $amount
     * @return stdClass
     * @throws Exception
     */
    public function payPalRefundService($payment, $amount)
    {
        $soapClient = $this->getSoapClient();
        $paypalInfo = $paypalInfo = $payment->getAdditionalInformation();

        $this->_request = new stdClass();
        $this->_request->merchantID = Mage::helper('cybersource_core')->getMerchantId();
        $this->_request->merchantReferenceCode = $paypalInfo['merchantReferenceCode'];
        $this->_request->clientLibrary = "PHP";
        $this->_request->clientLibraryVersion = phpversion();

        $payPalRefundService = new stdClass();
        $payPalRefundService->run = "true";
        $payPalRefundService->paypalCaptureId = $paypalInfo['captureTransactionID'];
        $payPalRefundService->paypalDoCaptureRequestID = $paypalInfo['captureRequestID'];
        $payPalRefundService->paypalDoCaptureRequestToken = $paypalInfo['captureRequestToken'];
        $this->_request->payPalRefundService = $payPalRefundService;

        $purchaseTotals = new stdClass();
        $purchaseTotals->grandTotalAmount = $amount;
        $this->_request->purchaseTotals = $purchaseTotals;

        $result = $soapClient->runTransaction($this->_request);
        if (!$result || $result->reasonCode != Cybersource_Cybersource_Model_Paypal_Source_Consts::STATUS_ACCEPT) {
            throw new Exception('There is an error in processing the refund.');
        }

        return $result;
    }
}
