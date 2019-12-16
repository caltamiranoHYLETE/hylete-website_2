<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_SOPWebMobile_Soapapi_Void extends Cybersource_Cybersource_Model_SOPWebMobile_Soapapi_Abstract
{
    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return Cybersource_Cybersource_Model_SOPWebMobile_Soapapi_Void
     * @throws Mage_Core_Exception
     */
	public function process($payment)
	{
	    $amount = $payment->getOrder()->getBaseGrandTotal();
        if ($this->useWebsiteCurrency()) {
            $amount = $payment->getOrder()->getGrandTotal();
        }

        $soapClient = $this->getSoapClient();

        $request = $this->iniRequest($payment->getOrder()->getIncrementId());

        $ccAuthReversalService = new stdClass();
        $ccAuthReversalService->run = "true";
        $ccAuthReversalService->authRequestID = $payment->getAdditionalInformation('authRequestID');
        $ccAuthReversalService->reversalReason = 'incomplete';
        $request->ccAuthReversalService = $ccAuthReversalService;

        $purchaseTotals = new stdClass();
        $purchaseTotals->grandTotalAmount = $amount;
        $request->purchaseTotals = $purchaseTotals;

        $result = $soapClient->runTransaction($request);

        if ($result->reasonCode != Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::STATUS_ACCEPT) {
            throw new Exception('Auth eversal failed with reason code ' . $result->reasonCode);
        }

        $payment->setTransactionId($result->requestID);
        $payment->setShouldCloseParentTransaction(1);
        $payment->setIsTransactionClosed(1);

        $payment->setAdditionalInformation('reversalRequestID', $result->requestID);
        $payment->setAdditionalInformation('reversalRequestToken', $result->requestToken);

        return $result;
	}
}
