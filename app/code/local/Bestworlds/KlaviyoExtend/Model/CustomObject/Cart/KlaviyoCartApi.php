<?php
/**
 * Klaviyo Custom Objects Cart Api
 *
 * @category   Best Worlds
 * @package    Bestworlds_KlaviyoExtend
 * @author     Best Worlds Team <info@bestworlds.com>
 */
class Bestworlds_KlaviyoExtend_Model_CustomObject_Cart_KlaviyoCartApi extends Klaviyo_Reclaim_Model_KlaviyoApi
{
    var $api_version = '1';
    var $quote_lifetime;
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
        $this->api_base_url = 'https://a.klaviyo.com/api/v' . $this->api_version . '/custom-objects/Cart/';
        $this->quote_lifetime = Mage::getStoreConfig('checkout/cart/delete_quote_after');
        if ($this->quote_lifetime == '') {
            $this->quote_lifetime = 30;
        }
    }

    protected function getQuoteItemsString($quote)
    {
        $arrItems = Array();
        $useSKU = Mage::getStoreConfigFlag('klaviyoextend/basic/sc_custom_object_use_sku');
        foreach ($quote->getAllVisibleItems() as $item) {
            if ($useSKU) {
                $itemSku = $item->getSku();
                $itemSku = preg_replace('/\,/', '-', $itemSku);
                $arrItems[] = $itemSku . ':' . $item->getQty();
            } else {
                $arrItems[] = $item->getProductId() . ':' . $item->getQty();
            }
        }
        $result = implode(',', $arrItems);
        return ($result?$result:"0");
    }

    function cartList()
    {
        $params = array();
        $json_response = $this->callServer('GET', '', $params);
        if(is_array($json_response) && isset($json_response['errors'])) {
            Mage::log($json_response['errors'], Zend_Log::INFO, Mage::helper('klaviyo_reclaim')->getLogFile());
        }
        return $json_response;
    }

    function cartUpdate($quote)
    {
        if (!$quote->getCustomerEmail()) return $this;
        $quote->setTotalsCollectedFlag(false)->collectTotals();
        $params = array();
        $cartLifetime = $this->quote_lifetime * 86400;
        $cartExpiration = date("Y-m-d", strtotime($quote->getUpdatedAt()) + $cartLifetime);
        $params['cart_expiration'] = $cartExpiration;
        $params['cart_id'] = $quote->getId();
        $params['$id'] = $quote->getId();
        $params['cart_url'] = Mage::getUrl('checkout/cart');
        $params['$email'] = $quote->getCustomerEmail();
        $params['external_customer_id'] = (int)$quote->getCustomerId();
        $params['cart_value'] = $quote->getBaseSubtotal();
        $params['klaviyo_updated'] = date('Y-m-d\TH:i:s\Z');
        $params['product_ids_and_quantities'] = $this->getQuoteItemsString($quote);
        $json_response = $this->callServer('PUT', $quote->getId(), $params);
        if(is_array($json_response) && isset($json_response['errors'])) {
            Mage::log($json_response['errors'], Zend_Log::INFO, Mage::helper('klaviyo_reclaim')->getLogFile());
        }
        return $json_response;
    }

    function cartDelete($quote)
    {
        if (!$quote->getCustomerEmail()) return $this;
        $params = array();
        $json_response = $this->callServer('DELETE', $quote->getId(), $params);
        if(is_array($json_response) && isset($json_response['errors'])) {
            Mage::log($json_response['errors'], Zend_Log::INFO, Mage::helper('klaviyo_reclaim')->getLogFile());
        }
        return $json_response;
    }

    function callServer($method, $path, $params) {
        $this->request_params = $params;

        $this->error_message = '';
        $this->error_code = '';

        $client = new Zend_Http_Client($this->api_base_url . $path. '/?api_key=' . $this->api_key);
        $client->setMethod($method);

        $this->_logApiRequest($this->api_base_url . $path. '/?api_key=' . $this->api_key);
        $this->_logApiRequest($method);
        $this->_logApiRequest($path);
        $this->_logApiRequest($params);

        if ($method == 'GET') {
            $client->setParameterGet($params);
        } else if ($method == 'POST' || $method == 'PUT') {
            $client->setRawData(json_encode(array("record" => $params)), 'application/json');
            $this->_logApiRequest(json_encode(array("record" => $params)));
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
            $this->_logApiRequest($response);
            if (!$response->isSuccessful()) {
                $this->error_message = $response->getBody();
                $this->error_code = $response->getStatus();
                return false;
            }
        } catch (Exception $ex) {
            Mage::logException($ex);
            return $ex->getMessage();
        }

        $json_response = Zend_Json::decode($response->getBody());

        if(is_array($json_response) && isset($json_response['errors'])) {
            $this->error_message = $json_response['errors'];
            $this->error_code = $response->getStatus();
            return false;

        }

        return $json_response;
    }

    /**
     * Log API calls.
     *
     * @param mixed $data
     * @return void
     */
    protected function _logApiRequest($data) {
        if (!Mage::getStoreConfigFlag('klaviyoextend/basic/sc_custom_object_debug')) return $this;
        Mage::log($data, Zend_Log::INFO, Mage::helper('klaviyo_reclaim')->getLogFile());
    }
}