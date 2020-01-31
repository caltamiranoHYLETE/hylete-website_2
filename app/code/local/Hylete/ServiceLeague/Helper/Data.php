<?php
class Hylete_ServiceLeague_Helper_Data extends Mage_Core_Helper_Abstract
{


    protected $_baseUrl;

    public function __construct()
    {
        $this->_baseUrl = urlencode(Mage::getBaseUrl());
    }

    public function saveResponse($response){

        $serviceLeagueResource = Mage::getModel('serviceleague/verifier');
        try {
            $serviceLeagueResource->setResponse($response);
            $serviceLeagueResource->save();
            Mage::log("set resposne in session", null, 'govx-auth.log');
            Mage::getSingleton('serviceleague/verifier')->setResponse($response);
            Mage::log("gov x user data saved", null, 'govx-auth.log');


        }
        catch (exception $e) {
            Mage::log("error while saving response", null, 'govx-auth.log');
            Mage::log($e, null, 'govx-auth.log');
        }

    }

    public function createAccountFromResponse($response)
    {

    }
    function getAccessToken($code){
        Mage::log("get access token using code", null, 'govx-auth.log');
        Mage::log($code, null, 'govx-auth.log');

        $curl = curl_init();
        $redirectUri = $this->_baseUrl. "govx-auth/index/code";
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://auth.govx.com/oauth/token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "client_secret=puZ8gMukdNjPnxBQwOEDFOOA3FJeKyePYBAPCCyz4sw%3D&redirect_uri=$redirectUri&grant_type=authorization_code&code=$code&client_id=c3071849-15db-e911-80e9-14187735bc96",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/x-www-form-urlencoded"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);


        if ($err) {
            Mage::log($err, null, 'govx-auth.log');

        } else {
            Mage::log("response get access token", null, 'govx-auth.log');
            Mage::log($response, null, 'govx-auth.log');
            $response = json_decode($response, true);
            $token = $response['access_token'];
            if($token == null){
                return false;
            }
            $userData = $this->getUserData($token);
            return $userData;
        }
    }
    public function getUserData($token){
        Mage::log("in user data token below", null, 'govx-auth.log');
        Mage::log($token, null, 'govx-auth.log');
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://auth.govx.com/api/data",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Accept: */*",
                "Accept-Encoding: gzip, deflate",
                "Authorization: Bearer $token",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Cookie: _pxvid=934c4af4-3181-11ea-ba2b-0242ac120007; ai_user=tUEnk|2020-01-07T19:11:58.172Z; _ga=GA1.2.615827829.1578424318; _gid=GA1.2.1072465278.1578424318; __insp_wid=797306955; __insp_nv=true; __insp_targlpu=aHR0cHM6Ly9hdXRoLmdvdnguY29tL29hdXRoL3Rva2VuP3Jlc3BvbnNlX3R5cGU9Y29kZSZzdGF0ZT0mY2xpZW50X2lkPWMzMDcxODQ5LTE1ZGItZTkxMS04MGU5LTE0MTg3NzM1YmM5NiZzY29wZT0mcmVkaXJlY3RfdXJpPWh0dHAlM0ElMkYlMkZhMDRjMjA2My5uZ3Jvay5pbyUyRmdvdngtYXV0aA%3D%3D; __insp_targlpt=R292WCBWZXJpZmljYXRpb24%3D; __insp_norec_sess=true; ai_session=4oKhC|1578424320352|1578424398927.2; __insp_slim=1578424398961",
                "Host: auth.govx.com",
                "Postman-Token: 70492579-b85f-4d7b-9161-785322662fff,3a4b3341-e020-4e89-8adb-5a7ff37600d1",
                "User-Agent: PostmanRuntime/7.20.1",
                "cache-control: no-cache"
            ),
        ));

        Mage::log("get USER DATA", null, 'govx-auth.log');

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {

            Mage::log( "error getting user data", 'govx-auth.log');
            Mage::log( $err, 'govx-auth.log');
        } else {
            Mage::log($response, null, 'govx-auth.log');
            Mage::log("sendresponse to helper", null, 'govx-auth.log');
            $this->saveResponse($response);
            return $response;
        }
    }
    public function checkIfCustomerExist($customerData){
        Mage::log("checkIfCustomerExist", null, 'govx-auth.log');
        Mage::log($customerData, null, 'govx-auth.log');
        $customerData = json_decode($customerData,true);
        $customer = Mage::getModel("customer/customer");
        $customer->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
        $customer->loadByEmail($customerData['userProfile']['email']);
        if ($customer->getId()) {
            Mage::log("checkIfCustomerExist = yes", null, 'govx-auth.log');
            $customer->setGroupId(9);
            $customer->save();
            return true;
        }
        Mage::log("checkIfCustomerExist = no", null, 'govx-auth.log');
        return false;

    }

}
	 