<?php

class Cybersource_Cybersource_Model_SOPWebMobile_Soapapi_Fallback extends Cybersource_Cybersource_Model_SOPWebMobile_Soapapi_Abstract
{
    /**
     * @param string $transactionId
     * @return $this
     * @throws Exception
     */
	public function processVoid($transactionId)
	{
	    $request = $this->iniRequest();

        $soapClient = $this->getSoapClient();

        $voidService = new \stdClass();
        $voidService->run = "true";
        $voidService->voidRequestID = $transactionId;
        $request->voidService = $voidService;

        $result = $soapClient->runTransaction($request);

        if ($result->reasonCode != Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::STATUS_ACCEPT) {
            throw new Exception('Failed to void with reason code ' . $result->reasonCode);
        }

		return $this;
	}

    /**
     * @param string $transactionId
     * @param string $amount
     * @return $this
     * @throws Exception
     */
	public function processReversal($transactionId, $amount)
	{
	    $request = $this->iniRequest();

        $soapClient = $this->getSoapClient();

        $ccAuthReversalService = new stdClass();
        $ccAuthReversalService->run = "true";
        $ccAuthReversalService->authRequestID = $transactionId;
        $ccAuthReversalService->reversalReason = 'incomplete';

        $purchaseTotals = new stdClass();
        $purchaseTotals->grandTotalAmount = $amount;

        $request->purchaseTotals = $purchaseTotals;
        $request->ccAuthReversalService = $ccAuthReversalService;

        $result = $soapClient->runTransaction($request);

        if ($result->reasonCode != Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::STATUS_ACCEPT) {
            throw new Exception('Failed to reverse authorization with reason code ' . $result->reasonCode);
        }

		return $this;
	}
}
