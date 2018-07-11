<?php

class TroopID_Connect_Helper_Oauth extends Mage_Core_Helper_Abstract {

    const ENDPOINT_PRODUCTION = "https://api.id.me";

    const AUTHORIZE_PATH    = "/oauth/authorize";
    const TOKEN_PATH        = "/oauth/token";
    const AFFILIATIONS_PATH = "/api/public/v2/affiliations.json";

    const API_ORIGIN = "MAGENTO-IDME";

    private function getConfig() {
        return Mage::helper("troopid_connect");
    }

    private function getCallbackUrl() {
        return Mage::getUrl("troopid/authorize/callback", array(
            "_type"     => Mage_Core_Model_Store::URL_TYPE_WEB,
            "_secure"   => Mage::app()->getFrontController()->getRequest()->isSecure(),
            "_nosid"    => true
        ));
    }

    public function getAuthorizeUrl($scope = "military") {

        $params = array(
            "client_id"     => $this->getConfig()->getKey("client_id"),
            "redirect_uri"  => $this->getCallbackUrl(),
            "scope"         => $scope,
            "response_type" => "code",
            "display"       => "popup"
        );

        return $this->getDomain() . self::AUTHORIZE_PATH . "?" . $this->toQuery($params);
    }

    public function getAccessToken($code) {

        $config = $this->getConfig();
        $client = new Zend_Http_Client();

        $client->setUri($this->getDomain() . self::TOKEN_PATH);
        $client->setHeaders(array(
            "X-API-ORIGIN" => self::API_ORIGIN
        ));

        $client->setParameterPost(array(
            "client_id"     => trim($config->getKey("client_id")),
            "client_secret" => trim($config->getKey("client_secret")),
            "redirect_uri"  => $this->getCallbackUrl(),
            "code"          => $code,
            "grant_type"    => "authorization_code"
        ));

        try {
            $response = $client->request("POST");
        } catch (Zend_Http_Client_Exception $e) {
            return null;
        }

        if ($response->isError())
            return null;

        $json = Zend_Json::decode($response->getBody());

        return $json["access_token"];
    }


    public function getProfileData($token, $scope) {

        if (empty($token))
            return null;

        if (empty($scope))
            $scope = "military";

        $endpoints = array(
            "military"  => "/api/public/v2/military.json",
            "student"   => "/api/public/v2/student.json",
            "responder" => "/api/public/v2/responder.json",
            "teacher"   => "/api/public/v2/teacher.json"
        );

        $client = new Zend_Http_Client();
        $client->setUri($this->getDomain() . $endpoints[$scope]);
        $client->setParameterGet(array(
            "access_token" => $token
        ));

        $client->setHeaders(array(
            "X-API-ORIGIN" => self::API_ORIGIN
        ));

        try {
            $response = $client->request("GET");
        } catch (Zend_Http_Client_Exception $e) {
            return null;
        }

        if ($response->isError())
            return null;

        $json = Zend_Json::decode($response->getBody());

        return $json;
    }

    public function getAffiliations() {
        $config = $this->getConfig();
        $client = new Zend_Http_Client($this->getDomain() . self::AFFILIATIONS_PATH);
        $client->setParameterGet(array(
            "client_id" => $config->getKey("client_id")
        ));

        $client->setHeaders(array(
            "X-API-ORIGIN" => self::API_ORIGIN
        ));

        $response   = $client->request("GET");
        $values     = array();

        if ($response->isSuccessful())
            $values = Zend_Json::decode($response->getBody());

        return $values;
    }

    private function getDomain() {
        return self::ENDPOINT_PRODUCTION;
    }

    private function toQuery($params) {
        $output = array();

        foreach ($params as $key => $value) {
            $output[]= $key . "=" . urlencode($value);
        }

        return join("&", $output);
    }


}