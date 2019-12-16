<?php

class Cybersource_Cybersource_Model_SOPWebMobile_Soapapi_TokenDetails extends Cybersource_Cybersource_Model_SOPWebMobile_Soapapi_Abstract
{
    /**
     * @param string $token
     * @return stdClass
     * @throws Exception
     */
	public function process($token)
	{
        $soapClient = $this->getSoapClient();

        $request = $this->iniRequest();

        $recurringSubscriptionInfo = new stdClass();
        $recurringSubscriptionInfo->subscriptionID = $token;
        $request->recurringSubscriptionInfo = $recurringSubscriptionInfo;

        $paySubscriptionRetrieveService = new stdClass();
        $paySubscriptionRetrieveService->run = "true";
        $request->paySubscriptionRetrieveService = $paySubscriptionRetrieveService;

        $result = $soapClient->runTransaction($request);

        if ($result->reasonCode != Cybersource_Cybersource_Model_SOPWebMobile_Source_Consts::STATUS_ACCEPT) {
            throw new Exception('Fetching token details failed with reason code: ' . $result->reasonCode);
        }

        return $result->paySubscriptionRetrieveReply;
	}
}
