<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_Model_Core_Soap_Client extends SoapClient
{
    /**
     * @var bool
     */
    private $preventLogFlag = false;

    /**
     * @var string
     */
    private $logFilename = 'cybs.log';

    /**
     * @var string
     */
    private $context = self::class;

    /**
     * @var string|null
     */
    private $btCode = null;

    /**
     * @param bool $preventLog
     * @return $this
     */
    public function setPreventLogFlag($preventLog = true)
    {
        $this->preventLogFlag = $preventLog;
        return $this;
    }

    /**
     * @param string $filename
     * @return $this
     */
    public function setLogFilename($filename)
    {
        $this->logFilename = $filename;
        return $this;
    }

    /**
     * @param string $context
     * @return $this
     */
    public function setContext($context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * @param string $btCode
     * @return $this
     */
    public function setBtCode($btCode)
    {
        $this->btCode = $btCode;
        return $this;
    }

    /**
     * Sends request to cybersource
     * @param string $request
     * @param string $location
     * @param string $action
     * @param int $version
     * @param int $oneWay
     * @return string
     */
    public function __doRequest($request, $location, $action, $version, $oneWay = 0)
    {
        $user = Mage::helper('cybersource_core')->getMerchantId();
        $password = Mage::helper('cybersource_core')->getSoapKey();

        if ($this->btCode) {
            // trying to get bt credentials with fallback to general
            $btUser = Mage::getStoreConfig('payment/' . $this->btCode . '/merchant_id');
            $btPassword = Mage::getStoreConfig('payment/' . $this->btCode . '/soapkey');
            if ($btUser && $btPassword) {
                $user = $btUser;
                $password = $btPassword;
            }
        }

        $origRequest = $request;

        $soapHeader = "<SOAP-ENV:Header xmlns:SOAP-ENV=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:wsse=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\"><wsse:Security SOAP-ENV:mustUnderstand=\"1\"><wsse:UsernameToken><wsse:Username>$user</wsse:Username><wsse:Password Type=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText\">$password</wsse:Password></wsse:UsernameToken></wsse:Security></SOAP-ENV:Header>";
        $requestDOM = new DOMDocument('1.0');
        $soapHeaderDOM = new DOMDocument('1.0');
        $requestDOM->loadXML($request);
        $soapHeaderDOM->loadXML($soapHeader);
        $node = $requestDOM->importNode($soapHeaderDOM->firstChild, true);
        $requestDOM->firstChild->insertBefore(
        $node, $requestDOM->firstChild->firstChild);
        $request = $requestDOM->saveXML();

        $response = parent::__doRequest($request, $location, $action, $version, $oneWay);

        $this->log(
            array(
                'context' => $this->context,
                'request' => $origRequest,
                'response' => $response
            )
        );

        return $response;
    }

    /**
     * @param string|array $message
     * @return $this
     */
    private function log($message)
    {
        if ($this->preventLogFlag) {
            return $this;
        }

        if (is_array($message)) {
            $message = var_export($message, true);
        }

        Mage::log($message, null, $this->logFilename, true);

        return $this;
    }
}
