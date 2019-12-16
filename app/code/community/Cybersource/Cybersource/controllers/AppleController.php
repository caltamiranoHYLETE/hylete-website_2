<?php
/**
 * © 2018 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its
 * subsidiaries, “CyberSource”) furnishes this code under the applicable agreement between the
 * reader of this document (“You”) and CyberSource (“Agreement”). You may use this code only in
 * accordance with the terms of the Agreement. The copyrighted code is licensed to You for use only
 * in strict accordance with the Agreement. You should read the Agreement carefully before using the code.
 */

class Cybersource_Cybersource_AppleController extends Mage_Core_Controller_Front_Action
{
    /**
     * Retrieve a merchant request from the Apple Pay Session object's request.
     */
    public function ingestAction()
    {
        try {
            $payload = $this->retrieveMerchantPayload($this->getRequest()->getParam('validationURL'));
        } catch (Exception $e) {
            Mage::logException($e);
            $payload = $this->__('Unable to initial merchant connection');
            $this->getResponse()->setHttpResponseCode(500);
        }

        $this->getResponse()->setBody(json_encode($payload));
        $this->getResponse()->setHeader('content-type', 'application/json');
    }

    /**
     * Get the payment request based off of the quote object and pass the results to the browser
     */
    public function paymentrequestAction()
    {
        $helper = $this->getHelper();
        $quote = $helper->getQuote();
        if ($quote instanceof Mage_Sales_Model_Quote && $quote->hasItems()) {
            $request = $helper->buildPaymentRequest($quote);
            $this->getResponse()->setBody(json_encode($request));
            $this->getResponse()->setHeader('content-type', 'application/json');
        } else {
            $this->getResponse()->setBody(json_encode($this->__('Shopping cart not found')));
            $this->getResponse()->setHttpResponseCode(404);
        }
    }

    /**
     * Get the Apple Pay helper
     *
     * @return Cybersource_Cybersource_Helper_ApplePay_Data
     */
    private function getHelper()
    {
        return Mage::helper(Cybersource_Cybersource_Helper_ApplePay_Data::GROUP_NAME);
    }

    /**
     * Retrieve the merchant request from Apple
     *
     * @param $validationUrl
     * @return Zend_Http_Response
     */
    private function retrieveMerchantPayload($validationUrl)
    {
        $curl = new Varien_Http_Adapter_Curl();
        $curl->setOptions(array(
            CURLOPT_SSLCERT          => Mage::helper(Cybersource_Cybersource_Helper_ApplePay_Data::GROUP_NAME)->getCertificateFilename(),
            CURLOPT_SSLKEY        => Mage::helper(Cybersource_Cybersource_Helper_ApplePay_Data::GROUP_NAME)->getCertificateFilename(),
            CURLOPT_SSL_VERIFYHOST  => false,
            CURLOPT_SSL_VERIFYPEER  => true,
        ));
        $payload = array(
            'merchantIdentifier' => Mage::helper(Cybersource_Cybersource_Helper_ApplePay_Data::GROUP_NAME)->getMerchantIdentifier(),
            'domainName' => Mage::helper(Cybersource_Cybersource_Helper_ApplePay_Data::GROUP_NAME)->getDomainName(),
            'displayName' => Mage::helper(Cybersource_Cybersource_Helper_ApplePay_Data::GROUP_NAME)->getStoreName()
        );
        if (empty($payload['merchantIdentifier']) || empty($payload['domainName']) || empty($payload['displayName'])) {
            Mage::throwException($this->__('Missing required configuration information'));
        }
        $body = json_encode($payload);
        $curl->write(
            'POST',
            $validationUrl,
            1.1,
            array(
                'content-type' => 'application/json'
            ),
            $body
        );

        $httpResponse = $curl->read();
        $this->getHelper()->log($httpResponse, Zend_Log::DEBUG);
        $response = Zend_Http_Response::fromString($httpResponse);
        $response = json_decode($response->getBody(), true);
        return $response;
    }

}
