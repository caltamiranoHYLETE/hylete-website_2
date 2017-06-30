<?php

/**
 * HTTP Helper class.
 *
 */
class Ebizmarts_BakerlooRestful_Helper_Http {

    /**
     * POST request to given url.
     *
     * @param string $url
     * @param string $requestBody
     * @param array $requestHeaders
     * @return string
     */
    public function POST($url, $requestBody, $requestHeaders) {
        return $this->request($url, $requestBody, $requestHeaders, true);
    }

    /**
     * GET request to given url.
     *
     * @param string $url
     * @param string $requestBody
     * @param array $requestHeaders
     * @return string
     */
    public function GET($url, $requestBody, $requestHeaders) {
        return $this->request($url, $requestBody, $requestHeaders);
    }

    /**
     * Request to given url.
     *
     * @param string $url
     * @param string $requestBody
     * @param array $requestHeaders
     * @param bool $post
     * @param bool $verifyPeer
     * @param bool $verifyHost
     * @return string
     */
    public function request($url, $requestBody, $requestHeaders, $post = false, $verifyPeer = false, $verifyHost = false) {

        $curlAdapter = new Varien_Http_Adapter_Curl();

        $config = array(
            'timeout'    => 90,
            'verifypeer' => $verifyPeer,
            'verifyhost' => $verifyHost,
            'header'     => false
        );
        $curlAdapter->setConfig($config);

        $UA = isset($requestHeaders['B-User-Agent']) ? $requestHeaders['B-User-Agent'] : null;
        if(!is_null($UA))
            $curlAdapter->addOption(CURLOPT_USERAGENT, $UA);

        if($post)
            $curlAdapter->write(Zend_Http_Client::POST, $url, '1.1', $requestHeaders, $requestBody);
        else
            $curlAdapter->write(Zend_Http_Client::GET, $url, '1.1', $requestHeaders, $requestBody);

        $response = $curlAdapter->read();

        $errorMessage = $curlAdapter->getError();
        if($errorMessage)
            $rawresponse = $errorMessage;
        else
            $rawresponse = $response;

        $curlAdapter->close();

        //For older magentos (1.6.0.0 for example)
        $rawresponseTmp = Zend_Http_Response::extractBody($rawresponse);
        if($rawresponseTmp != "")
            $rawresponse = $rawresponseTmp;

        return $rawresponse;

    }

    public function getJsonPayload($request) {
        $payload = $request->getRawBody();

        $data = json_decode($payload);

        if(!is_object($data)) {
            Mage::throwException("Invalid post data.");
        }

        return $data;
    }
}