<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Helper_Atp_Api extends Mage_Core_Helper_Data
{
    const LOG_FILE = 'cybs_atp.log';

    const TYPE_LOGIN = 'login';
    const TYPE_CREATION = 'account_creation';
    const TYPE_UPDATE = 'account_update';

    const XML_NAMESPACE = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';

    const CONFIG_ACTION_ON_ERROR = 'customer/atp/action_on_error';
    const CONFIG_ENABLED = 'customer/atp/enabled';

    protected $validTypes = array(
        self::TYPE_LOGIN,
        self::TYPE_CREATION,
        self::TYPE_UPDATE,
    );

    /**
     * Calls the Cybersource API
     *
     * @param string $type The typer of request (login, create, update)
     * @param $referenceCode
     * @return Cybersource_Cybersource_Model_Atp_Result
     */

    public function validate($type, $referenceCode, $observerData)
    {
        if (!in_array($type, $this->validTypes)) {
            Mage::throwException('Invalid ATP request');
        }
        $sessionId = Mage::getSingleton('customer/session')->getEncryptedSessionId();
        $merchantId = Mage::helper('cybersource_core')->getMerchantId();
        $fingerprintId = $merchantId . $sessionId;
        $data = $observerData->toArray();
        $customerData = ($data['event']->getModel() != "") ? $data['event']->getModel() : $data['event']->getCustomer();
        $customerEmail = $customerData->getEmail();
        $customerFirstName = $customerData->getFirstname();
        $customerLastName = $customerData->getLastname();
        $payload = new stdClass();
        $payload->partnerSolutionID = '3IM74O3P';
        $payload->customerID = ($customerData->getId() != "") ? $customerData->getId() : $this->__("New");
        $payload->merchantID = $merchantId;
        $payload->merchantReferenceCode = $referenceCode;
        $payload->customerFirstName = $customerFirstName;
        $payload->customerLastName = $customerLastName;
        $payload->emailAddress = $customerEmail;
        $payload->billTo = new stdClass();
        $payload->billTo->firstName = $customerFirstName;
        $payload->billTo->lastName = $customerLastName;
        $payload->billTo->email = $customerEmail;
        if ($customerData->getId() != "") {
            $customerInfo = Mage::getModel('customer/customer')->load($customerData->getId());
            $billingaddress = $customerInfo->getDefaultBillingAddress();
            $shippingaddress = $customerInfo->getDefaultShippingAddress();

            if ($billingaddress && !empty($billingaddress->getData())) {
                $payload->billTo->street1 = $billingaddress->street;
                $payload->billTo->city = $billingaddress->city;
                $payload->billTo->state = $billingaddress->region;
                $payload->billTo->postalCode = $billingaddress->postcode;
                $payload->billTo->country = $billingaddress->country_id;
                $payload->billTo->company = $billingaddress->company;
                $payload->billTo->phoneNumber = $billingaddress->telephone;
            }
            if ($shippingaddress && !empty($shippingaddress->getData())) {
                $payload->shipTo = new stdClass();
                $payload->shipTo->street1 = $shippingaddress->street;
                $payload->shipTo->city = $shippingaddress->city;
                $payload->shipTo->state = $shippingaddress->region;
                $payload->shipTo->postalCode = $shippingaddress->postcode;
                $payload->shipTo->country = $shippingaddress->country_id;
                $payload->shipTo->company = $shippingaddress->company;
                $payload->shipTo->phoneNumber = $shippingaddress->telephone;
            }
        }

        $dmeService = new stdClass();
        $dmeService->run = 'true';
        $dmeService->eventType = $type;

        $payload->dmeService = $dmeService;

        $payload->deviceFingerprintID = $fingerprintId;

        $client = $this->getClient(
            Mage::helper('cybersource_core')->getWsdlUrl(),
            Mage::helper('cybersource_core')->getMerchantId(),
            Mage::helper('cybersource_core')->getSoapKey()
        );

        try {
            $result = $client->runTransaction($payload);
            $request = $client->__getLastRequest();
            Mage::log($request, Zend_Log::DEBUG, self::LOG_FILE);
            $response = $client->__getLastResponse();
            Mage::log($response, Zend_Log::INFO, self::LOG_FILE);
        } catch (Exception $e) {
            Mage::log($e->getMessage(), Zend_Log::ERR, self::LOG_FILE);
            $request = $client->__getLastRequest();
            Mage::log($request, Zend_Log::DEBUG, self::LOG_FILE);
            $response = $client->__getLastResponse();
            Mage::log($response, Zend_Log::ERR, self::LOG_FILE);
            $result = new stdClass();
            $result->decision = Mage::getStoreConfig(self::CONFIG_ACTION_ON_ERROR);
        }


        return new Cybersource_Cybersource_Model_Atp_Result($result);
    }

    /**
     * Create the Soap params
     *
     * @param $opts
     * @return array
     */
    protected function getSoapParams($opts)
    {
        return array(
            'encoding' => 'UTF-8',
            'verifypeer' => false,
            'verifyhost' => false,
            'soap_version' => SOAP_1_1,
            'trace' => 1,
            'exceptions' => 1,
            "connection_timeout" => 180,
            'stream_context' => stream_context_create($opts)
        );
    }


    /**
     * Return some sweet stream options
     *
     * @return array
     */
    protected function getStreamOptions()
    {
        return array(
            'ssl' => array(
                'verify_peer' => true,
                'verify_peer_name' => true
            ),
            'http' => array(
                'user_agent' => 'PHP/Magento 1 Soap Client'
            )
        );
    }

    /**
     * Create a Soap header based on the configuration provided.
     *
     * @param $payload
     * @param $type
     * @param null $nodeName
     * @return SoapVar
     */
    protected function buildSoapHeader($payload, $type, $nodeName = null)
    {
        return new \SoapVar(
            $payload,
            $type,
            null,
            self::XML_NAMESPACE,
            $nodeName,
            self::XML_NAMESPACE
        );
    }

    /**
     * @param $wsdl
     * @param $merchantId
     * @param $soapKey
     * @return SoapClient
     * @throws Exception
     */
    protected function getClient($wsdl, $merchantId, $soapKey)
    {

        $opts = $this->getStreamOptions();
        $params = $this->getSoapParams($opts);
        $client = new \SoapClient($wsdl, $params);

        $soapUsername = $this->buildSoapHeader($merchantId, XSD_STRING);
        $soapPassword = $this->buildSoapHeader($soapKey, XSD_STRING);

        $auth = new \stdClass();
        $auth->Username = $soapUsername;
        $auth->Password = $soapPassword;

        $soapAuth = $this->buildSoapHeader($auth, SOAP_ENC_OBJECT, 'UsernameToken');

        $token = new \stdClass();
        $token->UsernameToken = $soapAuth;
        $soapToken = $this->buildSoapHeader($token, SOAP_ENC_OBJECT, 'UsernameToken');
        $security = $this->buildSoapHeader($soapToken, SOAP_ENC_OBJECT, 'Security');

        $header = new \SoapHeader(self::XML_NAMESPACE, 'Security', $security, true);
        $client->__setSoapHeaders(array($header));

        return $client;
    }


}
