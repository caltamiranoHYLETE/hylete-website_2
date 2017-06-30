<?php

class Ebizmarts_BakerlooRestful_Model_Order extends Mage_Core_Model_Abstract {

    public function _construct() {
        $this->_init('bakerloo_restful/order');
    }

    /**
     * Retrieve data
     *
     * @param   string $key
     * @param   mixed $index
     * @return unknown
     */
    public function getData($key = '', $index = null) {

        //->getData()
        if(empty($key)) {
            if (array_key_exists('json_payload', $this->_data) && empty($this->_data['json_payload']) && !empty($this->_data['json_payload_enc'])) {
                $this->_data['json_payload'] = Mage::helper('core')->decrypt($this->getJsonPayloadEnc());
            }

            if (array_key_exists('json_request_headers', $this->_data) && empty($this->_data['json_request_headers']) && !empty($this->_data['json_request_headers_enc'])) {
                $this->_data['json_request_headers'] = Mage::helper('core')->decrypt($this->getJsonRequestHeadersEnc());
            }

            if (array_key_exists('customer_signature', $this->_data) && empty($this->_data['customer_signature']) && !empty($this->_data['customer_signature_enc'])) {
                $this->_data['customer_signature'] = Mage::helper('core')->decrypt($this->getCustomerSignatureEnc());
            }
        }
        else {
            if ('json_payload'===$key) {
                if (empty($this->_data['json_payload']) && !empty($this->_data['json_payload_enc'])) {
                    $this->_data['json_payload'] = Mage::helper('core')->decrypt($this->getJsonPayloadEnc());
                }
            }
            if ('json_request_headers'===$key) {
                if (empty($this->_data['json_request_headers']) && !empty($this->_data['json_request_headers_enc'])) {
                    $this->_data['json_request_headers'] = Mage::helper('core')->decrypt($this->getJsonRequestHeadersEnc());
                }
            }
            if ('customer_signature'===$key) {
                if (empty($this->_data['customer_signature']) && !empty($this->_data['customer_signature_enc'])) {
                    $this->_data['customer_signature'] = Mage::helper('core')->decrypt($this->getCustomerSignatureEnc());
                }
            }
        }

        return parent::getData($key, $index);

    }

    public function getHttpHeaders() {
        $headers = array();
        $jsonHeaders = json_decode($this->getJsonRequestHeaders());

        foreach(Mage::helper('bakerloo_restful')->allPossibleHeaders() as $_h) {
            if(isset($jsonHeaders->{$_h})) {
                array_push($headers, "$_h: {$jsonHeaders->{$_h}}");
            }
        }

        array_push($headers, "B-Order-Id: {$this->getId()}");

        return $headers;
    }

    public function setRealCreatedAtToParent() {

        if(!$this->getOrderId())
            $this->load($this->getId());

        $realCreatedAt = $this->getCreatedAt();
        Mage::getModel('sales/order')->load($this->getOrderId())
            ->setCreatedAt($realCreatedAt)
            ->save();
    }

}