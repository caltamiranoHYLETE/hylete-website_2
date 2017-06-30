<?php

class Ebizmarts_BakerlooPayment_Model_PayPalHere extends Ebizmarts_BakerlooPayment_Model_Method_Abstract {

    protected $_code          = "bakerloo_paypalhere";
    protected $_infoBlockType = 'bakerloo_payment/info_paypalhere';

    private $_invoiceServiceUrls = array(
        'live'    => 'https://svcs.paypal.com/Invoice/',
        'sandbox' => 'https://svcs.sandbox.paypal.com/Invoice/',
    );

    private $_merchantApiNVPUrls = array(
        'live'    => 'https://api-3t.paypal.com/nvp',
        'sandbox' => 'https://api-3t.sandbox.paypal.com/nvp',
    );

    private $_merchantApiVersion = 109;

    private $_sandboxAppId = 'APP-80W284485P519543T';

    private $_liveAppId = null; //For live, you must use the ID that matches the app's credentials.

    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  Mage_Payment_Model_Method_Purchaseorder
     */
    public function assignData($data) {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }

        $this->getInfoInstance()->setPoNumber($data->getData('payReference'));
        return $this;
    }

    public function GetTransactionDetails($transactionId) {

        $endpointUrl = $this->getMerchantEndpoint($this->getConfigData('api_mode'));

        $authHeaders           = $this->getAuthHeader();
        $appIdHeader           = $this->getAppIdHeader();
        $requestResponseFormat = $this->getRequestFormat();

        $headers = array_merge($authHeaders, $appIdHeader, $requestResponseFormat);

        $parameters = array(
                            "METHOD"        => "GetTransactionDetails",
                            "VERSION"       => $this->_merchantApiVersion,
                            "USER"          => $this->getConfigData('api_username'),
                            "PWD"           => $this->getConfigData('api_password'),
                            "SIGNATURE"     => $this->getConfigData('api_signature'),
                            "TRANSACTIONID" => $transactionId,
                    );

        $response = Mage::helper('bakerloo_restful/http')->POST($endpointUrl, http_build_query($parameters), $headers);

        return $response;
    }

    /**
     * Get invoice details from API
     *
     * @param string $invoiceId Invoice #.
     */
    public function GetInvoiceDetails($invoiceId) {

        $authHeaders           = $this->getAuthHeader();
        $appIdHeader           = $this->getAppIdHeader();
        $requestResponseFormat = $this->getRequestFormat();

        $headers = array_merge($authHeaders, $appIdHeader, $requestResponseFormat);

        $endpointUrl = $this->getInvoiceEndpoint($this->getConfigData('api_mode')) . 'GetInvoiceDetails';

        $jsonPayload = '{
                        "requestEnvelope":
                        {
                        "errorLanguage":"en_US",
                        "detailLevel":"ReturnAll"
                        },
                        "invoiceID":"' . $invoiceId . '"
                        }';

        $response = Mage::helper('bakerloo_restful/http')->POST($endpointUrl, $jsonPayload, $headers);

        return $response;
    }

    /**
     * Return headers for Authenticate requests.
     *
     * @return array
     */
    public function getAuthHeader() {
        $headers = array(
            'X-PAYPAL-SECURITY-USERID: ' . $this->getConfigData('api_username'),
            'X-PAYPAL-SECURITY-PASSWORD: ' . $this->getConfigData('api_password'),
            'X-PAYPAL-SECURITY-SIGNATURE: ' . $this->getConfigData('api_signature'),
        );

        return $headers;
    }

    /**
     * Return header for APP-ID.
     *
     * @return array
     */
    public function getAppIdHeader() {
        //return array('X-PAYPAL-APPLICATION-ID: ' . $this->_sandboxAppId);
        return array();
    }

    /**
     * Return headers for request and response format, JSON is default.
     *
     * @param  string $requestFormat
     * @param  string $responseFormat
     * @return array
     */
    public function getRequestFormat($requestFormat = 'JSON', $responseFormat = 'JSON') {
        $headers = array(
            'X-PAYPAL-REQUEST-DATA-FORMAT: ' . $requestFormat,
            'X-PAYPAL-RESPONSE-DATA-FORMAT: ' . $responseFormat,
        );

        return $headers;
    }

    /**
     * Return service endpoint url for Invoice operations.
     *
     * @param  string $mode sandbox or live.
     * @return string The Url.
     */
    public function getInvoiceEndpoint($mode = 'sandbox') {

        if(empty($mode)) {
            $mode = 'sandbox';
        }

        return (string)$this->_invoiceServiceUrls[$mode];
    }

    /**
     * Return service endpoint url for Merchant operations.
     *
     * @param  string $mode sandbox or live.
     * @return string The Url.
     */
    public function getMerchantEndpoint($mode = 'sandbox') {

        if(empty($mode)) {
            $mode = 'sandbox';
        }

        return (string)$this->_merchantApiNVPUrls[$mode];
    }
}