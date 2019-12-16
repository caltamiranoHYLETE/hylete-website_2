<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_SOPWebMobile_Soapapi_Capture extends Cybersource_Cybersource_Model_SOPWebMobile_Soapapi_Abstract
{
    const CC_CAPTURE_SERVICE_TOTAL_COUNT = 99;

    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param float $amount
     * @return Cybersource_Cybersource_Model_SOPWebMobile_Soapapi_Capture
     * @throws Mage_Core_Exception
     */
	public function process($payment, $amount)
	{
        $soapClient = $this->getSoapClient();

        $request = $this->iniRequest($payment->getOrder()->getIncrementId());

        $order = $payment->getOrder();
        $isCustomCurrencyAmount = false;
        $currency = $payment->getOrder()->getBaseCurrencyCode();

        if ($this->useWebsiteCurrency()) {
            $currency = $payment->getOrder()->getOrderCurrencyCode();
            foreach ($order->getInvoiceCollection() as $invoice) {
                if ($invoice->getRequestedCaptureCase() == Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE) {
                    $amount = $this->formatNumber($invoice->getGrandTotal());
                    $isCustomCurrencyAmount = true;
                    break;
                }
            }
        }

        if (! $authRequestId = $payment->getAdditionalInformation('authRequestID')) {
            throw new Exception('Auth transaction was not found.');
        }

        $ccCaptureService = new stdClass();

        $captureSequence = $order->getInvoiceCollection()->getSize() + 1;
        $isPartialCapture = ($isCustomCurrencyAmount ? $order->getGrandTotal() : $order->getBaseGrandTotal()) > $amount;
        $isFinalCapture = ($isCustomCurrencyAmount ? $order->getTotalDue() : $order->getBaseTotalDue()) <= $amount;

        if ($isPartialCapture) {
            $ccCaptureService->totalCount = static::CC_CAPTURE_SERVICE_TOTAL_COUNT;
            $ccCaptureService->sequence = $isFinalCapture ? static::CC_CAPTURE_SERVICE_TOTAL_COUNT : $captureSequence;
        }

        $ccCaptureService->run = "true";
        $ccCaptureService->authRequestID = $authRequestId;
        $ccCaptureService->authRequestToken = $payment->getAdditionalInformation('authRequestToken');
        $request->ccCaptureService = $ccCaptureService;

        $request->billTo = $this->buildBillingAddress($order->getBillingAddress(), $payment->getOrder()->getCustomerEmail());
        if ($shippingAddress = $this->buildShippingAddress($payment->getOrder()->getShippingAddress())) {
            $request->shipTo = $shippingAddress;
        }

        $purchaseTotals = new stdClass();
        $purchaseTotals->currency = $currency;
        $purchaseTotals->grandTotalAmount = $amount;
        $request->purchaseTotals = $purchaseTotals;

        $result = $soapClient->runTransaction($request);

        if (! in_array($result->reasonCode, $this->successCodeList)) {
            throw new Exception('Capture failed with reason code: ' . $result->reasonCode);
        }

        $this->processSoapResponse($payment, $result);

        $payment->setAdditionalInformation('captureTransactionID', $result->requestID);
        $payment->setAdditionalInformation('captureRequestID', $result->requestID);
        $payment->setAdditionalInformation('captureRequestToken', $result->requestToken);

        return $result;
	}
}
