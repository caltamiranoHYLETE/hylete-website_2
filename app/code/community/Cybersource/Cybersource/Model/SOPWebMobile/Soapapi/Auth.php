<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_SOPWebMobile_Soapapi_Auth extends Cybersource_Cybersource_Model_SOPWebMobile_Soapapi_Abstract
{
    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param stdClass $token
     * @param bool $isMit
     * @return Cybersource_Cybersource_Model_SOPWebMobile_Soapapi_Auth
     * @throws Mage_Core_Exception
     */
	public function process($payment, $token, $isMit = false)
	{
        $soapClient = $this->getSoapClient();

        $request = $this->iniRequest($payment->getOrder()->getIncrementId());

        $ccAuthService = new stdClass();
        $ccAuthService->run = "true";
        $request->ccAuthService = $ccAuthService;

        $currency = $payment->getOrder()->getBaseCurrencyCode();
        $amount = $this->formatNumber($payment->getOrder()->getBaseGrandTotal());

        if ($this->useWebsiteCurrency()) {
            $currency = $payment->getOrder()->getOrderCurrencyCode();
            $amount = $this->formatNumber($payment->getOrder()->getGrandTotal());
        }

        $request->item = $this->buildLineItems($payment->getOrder()->getAllVisibleItems());

        $purchaseTotals = new stdClass();
        $purchaseTotals->currency = $currency;
        $purchaseTotals->grandTotalAmount = $amount;
        $request->purchaseTotals = $purchaseTotals;

        $this->buildTokenDetails($request, $token, $isMit);

        // skip PA for admin
        if (!$isMit) {
            $this->buildPayerAuthService($request);
        }

        $result = $soapClient->runTransaction($request);

        if (! in_array($result->reasonCode, $this->successCodeList)) {
            throw new Exception('Auth transaction failed with reason code: ' . $result->reasonCode);
        }

        $this->processSoapResponse($payment, $result);

        // update token after MIT
        if ($isMit) {
            $networkTransactionId = $result->ccAuthReply->paymentNetworkTransactionID;
            $token->model->setNetworkTransactionId(
                $networkTransactionId ? $networkTransactionId : 0
            )->save();
        }

        $payment->setAdditionalInformation('authTransactionID', $result->requestID);
        $payment->setAdditionalInformation('authRequestID', $result->requestID);
        $payment->setAdditionalInformation('authRequestToken', $result->requestToken);

        return $result;
	}
}
