<?php

class Ebizmarts_BakerlooRestful_Model_Api_Pphtoken extends Ebizmarts_BakerlooRestful_Model_Api_Api {

    private $_backend_reactivate_url = "https://pos.ebizmarts.com/admin/paypal-here-reactivate";

    protected $_configPath = 'payment/bakerloo_paypalhere/';

    /**
     * Process GET requests.
     *
     * @return type
     * @throws Exception
     */
    public function get() {

        $this->checkGetPermissions();

//        if(!$this->getStoreId()) {
//            Mage::throwException('Please provide a Store ID.');
//        }
//
//        $store = $this->getStoreId();

        $mode = $this->_getQueryParameter('mode');
        if($mode == null || $mode == ''){
            $mode = Mage::getStoreConfig($this->_configPath . 'api_mode',0);
        }

        $timestamp = Mage::getStoreConfig($this->_configPath . 'timestamp_' . $mode,0);
        $access_token = Mage::getStoreConfig($this->_configPath . 'access_token_' . $mode,0);
        $refresh_token = Mage::getStoreConfig($this->_configPath . 'refresh_token_' . $mode,0);
        $backend_account_id = Mage::getStoreConfig($this->_configPath . 'backend_account_id_' . $mode,0);
        $nowTimestamp = (time()*1000) - 600000; //add 10 min window

        //workaround for older magento versions
        if(!$access_token || $access_token == '') {
            $timestamp = Mage::getStoreConfig($this->_configPath . 'timestamp_' . $mode,1);
            $access_token = Mage::getStoreConfig($this->_configPath . 'access_token_' . $mode,1);
            $refresh_token = Mage::getStoreConfig($this->_configPath . 'refresh_token_' . $mode,1);
            $backend_account_id = Mage::getStoreConfig($this->_configPath . 'backend_account_id_' . $mode,1);
        }

        if($access_token && $access_token != ''){

            if($nowTimestamp > $timestamp){
                //request new token
                $data = array("refresh_token"=>$refresh_token,"backend_account_id"=>$backend_account_id,"store"=>0, "mode"=>$mode);
                $headers = array();
                $response = Mage::helper('bakerloo_restful/http')->POST($this->_backend_reactivate_url, $data, $headers);
                $objResponse = json_decode($response);
                if($objResponse->error && $objResponse->error != ''){
                    Mage::throwException("Access token is old, refresh failed: " . $objResponse->error);
                }
                else {
                    $coreConfig = Mage::getModel('core/config');
                    $coreConfig ->saveConfig($this->_configPath . 'access_token_' . $mode, $objResponse->access_token, 'stores', 0);
                    $access_token = $objResponse->access_token;
                    $coreConfig ->saveConfig($this->_configPath . 'timestamp_' . $mode, $objResponse->timestamp, 'stores', 0);
                    $timestamp = $objResponse->timestamp;
                    Mage::getConfig()->cleanCache();
                }
            }

            $resultArray = array("access_token" => $access_token,
                "timestamp" => $timestamp);

            return $resultArray;
        }else{
            Mage::throwException('Access token was not generated for ' . $mode . ' mode.');
        }
    }

    /**
     * save new token
     *
     */
    public function post() {

        parent::post();

//        if(!$this->getStoreId()) {
//            Mage::throwException('Please provide a Store ID.');
//        }
//        $store = $this->getStoreId();

        $data = $this->getJsonPayload();

        $coreConfig = Mage::getModel('core/config');
        $coreConfig ->saveConfig($this->_configPath . 'access_token_' . $data->mode, $data->access_token, 'default', 0);
        $coreConfig ->saveConfig($this->_configPath . 'refresh_token_' . $data->mode, $data->refresh_token, 'default', 0);
        $coreConfig ->saveConfig($this->_configPath . 'backend_account_id_' . $data->mode, $data->backend_account_id, 'default', 0);
        $coreConfig ->saveConfig($this->_configPath . 'timestamp_' . $data->mode, $data->timestamp, 'default', 0);

        Mage::getConfig()->cleanCache();

        return $this;
    }

    /**
     * delete new token
     *
     */
    public function delete() {

        $this->checkDeletePermissions();

        $mode = $this->_getQueryParameter('mode');
        $store = $this->getStoreId();

        $coreConfig = Mage::getModel('core/config');
        $coreConfig ->deleteConfig($this->_configPath . 'access_token_' . $mode, 'default', 0);
        $coreConfig ->deleteConfig($this->_configPath . 'refresh_token_' . $mode, 'default', 0);
        $coreConfig ->deleteConfig($this->_configPath . 'backend_account_id_' . $mode, 'default', 0);
        $coreConfig ->deleteConfig($this->_configPath . 'timestamp_' . $mode, 'default', 0);

        //in case old code was already saved
        $coreConfig ->deleteConfig($this->_configPath . 'access_token_' . $mode, 'default', $store);
        $coreConfig ->deleteConfig($this->_configPath . 'refresh_token_' . $mode, 'default', $store);
        $coreConfig ->deleteConfig($this->_configPath . 'backend_account_id_' . $mode, 'default', $store);
        $coreConfig ->deleteConfig($this->_configPath . 'timestamp_' . $mode, 'default', $store);

        Mage::getConfig()->cleanCache();

        return $this;
    }

}