<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

abstract class Cybersource_Cybersource_Model_ApplePay_Api_AbstractAdapter
{
    /**
     * The XML namespace
     */
    const XML_NAMESPACE = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';

    /**
     * The log designation for a request
     */
    const LOG_TYPE_REQUEST = 'request';

    /**
     * The log designation for a response
     */
    const LOG_TYPE_RESPONSE = 'response';

    /**
     * The log designation for an error
     */
    const LOG_TYPE_ERROR = 'error';

    /**
     * The soap client
     *
     * @var SoapClient
     */
    private $client;

    /**
     * The Apple Pay merchant ID
     *
     * @var string
     */
    private $merchantId;

    /**
     * The Cybersource transaction key
     *
     * @var string
     */
    private $transactionKey;

    /**
     * The Cybersource WSDL
     *
     * @var string
     */
    private $wsdl;

    /**
     * Has the object been configured?
     *
     * @var bool
     */
    private $configured = false;

    /**
     * A list of all the card types
     *
     * @var array
     */
    private $cardType = array(
        'amex' => "003",
        'discover' => "004",
        'mastercard' => "002",
        'visa' => "001",
        'jcb' => "001",
    );

    /**
     * @param $merchantId
     * @param $transactionKey
     * @param $wsdl
     * @param $client SoapClient
     */
    public function configure($merchantId, $transactionKey, $wsdl, SoapClient $client = null)
    {
        $this->merchantId = $merchantId;
        $this->transactionKey = $transactionKey;
        $this->wsdl = $wsdl;
        $this->client = $client;
        $this->configured = true;
    }

    /**
     * Verify that the object has been configured properly
     *
     * @throws Exception When adapter has not been configured
     */
    protected function verifyConfigured()
    {
        if (!$this->configured) {
            Mage::throwException($this->getHelper()->__('Cybersource Apple Pay has not been configured yet'));
        }
    }

    /**
     * Get the Apple Pay merchant ID
     *
     * @return string
     */
    public function getMerchantId()
    {
        return $this->merchantId;
    }

    /**
     * Get the Cybersource transaction key
     *
     * @return string
     */
    public function getTransactionKey()
    {
        return $this->transactionKey;
    }

    /**
     * Get the general payload object, used by all of the transactions
     *
     * @return stdClass
     */
    protected function getBasePayload()
    {
        $payload = new stdClass();
        $payload->partnerSolutionID = '3IM74O3P';
        // Set the merchant ID
        $payload->merchantID = $this->merchantId;
        return $payload;
    }

    /**
     *
     * @param $callApi boolean Whether or not to actually call the API
     * @return Cybersource_Cybersource_Model_ApplePay_Api_Result_AbstractResult
     * @throws Exception On any number of errors
     */
    public function send($callApi = true)
    {
        Mage::dispatchEvent(
            'cybersource_applepay_build_before',
            array(
                'adapter' => $this
            )
        );

        $requestIdentifier = uniqid('', true); // used so you can easily grep log files for related requests
        $payload = $this->buildRequest();
        $this->logPayload($requestIdentifier, self::LOG_TYPE_REQUEST, $payload);

        try {
            Mage::dispatchEvent(
                'cybersource_applepay_transaction_before',
                array(
                    'payload' => $payload,
                    'adapter' => $this
                )
            );
            if (!$callApi) {
                return $this->getResultObject();
            }
            // Make the Soap call
            $result = $this->getClient()->runTransaction($payload);

            // Log the result
            $this->logPayload($requestIdentifier, self::LOG_TYPE_RESPONSE, $result);

            // Set the result in the result object
            $this->getResultObject()->setApiResult($result);

            Mage::dispatchEvent(
                'cybersource_applepay_transaction_success',
                array(
                    'payload' => $payload,
                    'adapter' => $this,
                    'result' => $result
                )
            );
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $client = $this->getClient();
            $headers = $client->__getLastRequestHeaders();
            $response = $client->__getLastRequest();
            $this->logPayload($requestIdentifier, self::LOG_TYPE_ERROR,
                array(
                    'message' => $message,
                    'headers' => $headers,
                    'response' => $response
                )
            );
            Mage::dispatchEvent(
                'cybersource_applepay_transaction_failure',
                array(
                    'payload' => $payload,
                    'adapter' => $this,
                    'exception' => $e
                )
            );

        }
        return $this->getResultObject();
    }

    /**
     * Build the API structure
     *
     * @return stdClass
     */
    protected abstract function buildRequest();

    /**
     * Log the payload, json_encoding() it
     *
     * @param $requestIdentifier
     * @param $type
     * @param $payload
     */
    protected function logPayload($requestIdentifier, $type, $payload)
    {
        $result = json_encode($payload);
        $logEntry = sprintf('%s - %s: %s', $requestIdentifier, $type, $result);
        $this->getHelper()->log($logEntry, Zend_Log::NOTICE);
    }

    /**
     * Retrieve the Apple Pay helpers
     *
     * @return Cybersource_Cybersource_Helper_ApplePay_Data
     */
    public function getHelper()
    {
        return Mage::helper(Cybersource_Cybersource_Helper_ApplePay_Data::GROUP_NAME);
    }

    /**
     * Get the card type based off of the name
     *
     * @param Cybersource_Cybersource_Model_ApplePay_Request_Payload $payload
     * @return string
     * @throws Exception When the card provided does not match a supported network
     */

    protected function getCardType(Cybersource_Cybersource_Model_ApplePay_Request_Payload $payload)
    {
        $cardType = $payload->getCardNetwork();
        $cardType = strtolower($cardType);
        if (isset($this->cardType[$cardType])) {
            return $this->cardType[$cardType];
        }
        Mage::throwException($this->getHelper()->__('Invalid card network: %s',  $payload->getCardNetwork()));
    }

    /**
     * Retrieves the result object
     *
     * @return Cybersource_Cybersource_Model_ApplePay_Api_Result_AbstractResult
     */
    abstract public function getResultObject();

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
     * Create a SoapClient.
     *
     * @return SoapClient
     * @throws Exception
     */
    protected function getClient()
    {
        if (!$this->client instanceof SoapClient) {
            $opts = $this->getStreamOptions();
            $params = $this->getSoapParams($opts);

            try {
                $client = new \SoapClient($this->wsdl, $params);

                $soapUsername = $this->buildSoapHeader($this->merchantId, XSD_STRING);
                $soapPassword = $this->buildSoapHeader($this->transactionKey, XSD_STRING);

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
                $this->client = $client;

            } catch (\Exception $e) {
                Mage::logException($e);
                throw $e;
            }
        }
        return $this->client;
    }
}
