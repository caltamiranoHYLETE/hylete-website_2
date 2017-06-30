<?php

class Ebizmarts_BakerlooRestful_Model_Quote extends Mage_Core_Model_Abstract {

    public function _construct() {
        $this->_init('bakerloo_restful/quote');
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

            if (array_key_exists('json_payload', $this->_data) && empty($this->_data['json_payload']) && !empty($this->_data['json_payload_enc']))
                $this->_data['json_payload'] = Mage::helper('core')->decrypt($this->getJsonPayloadEnc());

        }
        else {
            if ('json_payload'===$key) {
                if (empty($this->_data['json_payload']) && !empty($this->_data['json_payload_enc']))
                    $this->_data['json_payload'] = Mage::helper('core')->decrypt($this->getJsonPayloadEnc());

            }
        }

        return parent::getData($key, $index);

    }

}