<?php
/**
 * Klaviyo Person Api
 *
 * @category   Best Worlds
 * @package    Bestworlds_KlaviyoExtend
 * @author     Best Worlds Team <info@bestworlds.com>
 */
class Bestworlds_KlaviyoExtend_Model_Person extends Klaviyo_Reclaim_Model_KlaviyoApi
{
    var $api_version = '1';
    /**
     * Connect to the Klaviyo API for a given list.
     *
     * @param string $apikey Your Klaviyo apikey
     * @param string $secure Whether or not this should use a secure connection
     */
    function __construct($api_key, $secure=true)
    {
        parent::__construct($api_key, $secure);
        if (is_array($api_key)) {
            $this->api_key = $api_key['api_key'];
        } else {
            $this->api_key = $api_key;
        }
        $this->api_base_url = 'https://a.klaviyo.com/api/v' . $this->api_version . '/person/';
    }

    function personUpdate($personData)
    {
        $personId = $personData['$id'];
        unset($personData['$id']);
        $json_response = $this->callServer('PUT', $personId, $personData);
        if(is_array($json_response) && isset($json_response['errors'])) {
            Mage::log($json_response['errors'], Zend_Log::INFO, Mage::helper('klaviyo_reclaim')->getLogFile());
        }
        return $json_response;
    }

    function callServer($method, $path, $params)
    {
        $this->request_params = $params;

        $this->error_message = '';
        $this->error_code = '';

        $client = new Zend_Http_Client($this->api_base_url . $path. '?api_key=' . $this->api_key);
        $client->setMethod($method);

        $this->_logApiRequest($this->api_base_url . $path. '?api_key=' . $this->api_key);

        if ($method == 'GET') {
            $client->setParameterGet($params);
        } else if ($method == 'POST') {
            $client->setRawData(json_encode($params), 'application/json');
            $this->_logApiRequest(json_encode($params));
        } else if ($method == 'PUT') {
            $client = new Zend_Http_Client($this->api_base_url . $path. '?api_key=' . $this->api_key."&".http_build_query($params));
            $client->setMethod($method);
            $this->_logApiRequest($this->api_base_url . $path. '?api_key=' . $this->api_key."&".http_build_query($params));
        } else if ($method == 'DELETE') {
            $client->setHeaders(array(
                "Content-Type" => "application/json",
                "Accept" => "application/json"));
            $client->setRawData(json_encode($params));
        } else {
            $client->setRawData(http_build_query($params));
        }

        try {
            $response = $client->request();
            //$this->_logApiRequest($response);
            if (!$response->isSuccessful()) {
                $this->error_message = $response->getBody();
                $this->error_code = $response->getStatus();
                $this->_logApiRequest($response->getStatus().": ".$response->getBody());
                return false;
            }
        } catch (Exception $ex) {
            Mage::logException($ex);
            $this->_logApiRequest($ex->getMessage());
            return $ex->getMessage();
        }

        $json_response = Zend_Json::decode($response->getBody());
        $json_response["response_code"] = $response->getStatus();

        if(is_array($json_response) && isset($json_response['errors'])) {
            $this->error_message = $json_response['errors'];
            $this->error_code = $response->getStatus();
            $this->_logApiRequest($response->getStatus().": ".$json_response['errors']);
            return false;
        }
        $this->_logApiRequest($json_response);
        return $json_response;
    }

    /**
     * Log API calls.
     *
     * @param mixed $data
     * @return void
     */
    protected function _logApiRequest($data)
    {
        if (!Mage::getStoreConfigFlag('klaviyoextend/basic/sc_custom_object_debug')) return $this;
        Mage::log($data, Zend_Log::INFO, Mage::helper('klaviyo_reclaim')->getLogFile());
        if (is_array($data)) {
            $data = serialize($data);
        }
        //file_put_contents('var/log/klaviyo_person.log', date('Y-m-d H:i:s').": ".$data."\n", FILE_APPEND);
    }
}
