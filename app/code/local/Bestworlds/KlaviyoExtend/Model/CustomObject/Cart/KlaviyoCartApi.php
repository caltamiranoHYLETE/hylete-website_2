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
        $params['product_ids_and_quantities'] = $this->getQuoteItemsString($quote);
        if ($params['product_ids_and_quantities'] != "0") {
            $params['cart_expiration'] = $cartExpiration;
        } else {
            //Yesterday value, to avoid sending email notifications from Klaviyo about empty carts
            $params['cart_expiration'] = date("Y-m-d",strtotime("-1 days"));
        }
        $params['cart_id'] = $quote->getId();
        $params['$id'] = $quote->getId();
        $params['cart_url'] = Mage::getUrl('checkout/cart');
        $params['$email'] = $quote->getCustomerEmail();
        $params['external_customer_id'] = (int)$quote->getCustomerId();
        $params['cart_value'] = $quote->getBaseSubtotal();
        $params['klaviyo_updated'] = date('Y-m-d\TH:i:s\Z');
        $json_response = $this->callServer('PUT', $quote->getId(), $params);
        if(is_array($json_response)) {
            if (isset($json_response['errors'])) {
                Mage::log($json_response['errors'], Zend_Log::INFO, Mage::helper('klaviyo_reclaim')->getLogFile());
            } else {
                if ($json_response['response_code'] == 201) {
                    //When cart is created, the Klaviyo customer ID is not returned, then is necessary to do a second update call to get it.
                    $this->cartUpdate($quote);
                    return $json_response;
                }
                $personData = array(
                    '$id' => $json_response['klaviyo_customer_id'],
                    'cart_expiration' => $cartExpiration
                );
                if ($params['product_ids_and_quantities'] == "0") {
                    //When the cart is emptied or the order is created
                    $personData['cart_expiration'] = "0000-00-00";
                }
                $klaviyoPerson = Mage::getModel('klaviyoextend/person', array('api_key' => $this->api_key));
                Mage::getSingleton('checkout/session')->setKlaviyiPersonId($personData['$id']);
                $klaviyoPerson->personUpdate($personData);
            }
        }
        return $json_response;
    }

    function cartDelete($quote)
    {
        if (!$quote->getCustomerEmail()) return $this;
        $params = array();
        $json_response = $this->callServer('DELETE', $quote->getId(), $params);
        if(is_array($json_response)) {
            if (isset($json_response['errors'])) {
                Mage::log($json_response['errors'], Zend_Log::INFO, Mage::helper('klaviyo_reclaim')->getLogFile());
            } else {
                $personData = array(
                    '$id' => Mage::getSingleton('checkout/session')->getKlaviyiPersonId(),
                    'cart_expiration' => "0000-00-00"
                );
                $klaviyoPerson = Mage::getModel('klaviyoextend/person', array('api_key' => $this->api_key));
                $klaviyoPerson->personUpdate($personData);
            }
        }
        return $json_response;
    }

    function callServer($method, $path, $params)
    {
        $this->request_params = $params;

        $this->error_message = '';
        $this->error_code = '';

        $client = new Zend_Http_Client($this->api_base_url . $path. '/?api_key=' . $this->api_key);
        $client->setMethod($method);

        $this->_logApiRequest($this->api_base_url . $path. '/?api_key=' . $this->api_key);

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