<?php

class Ebizmarts_BakerlooPayment_Model_Source_AnetPaymentMode {

    private function _getAvailableModes() {
        return array(
            'AUTH_CAPTURE' => Mage::helper('bakerloo_payment')->__('Authorization and Capture'),
            'AUTH_ONLY'    => Mage::helper('bakerloo_payment')->__('Authorization Only'),
        );
    }

    public function toOptionArray() {
        $options =  array();

        $ccTypes = $this->_getAvailableModes();

        foreach ($ccTypes as $code => $name) {
            $options[] = array(
                'value' => $code,
                'label' => $name
            );
        }

        return $options;
    }

    public function toOption() {
        $options =  array();

        $ccTypes = $this->_getAvailableModes();

        foreach ($ccTypes as $code => $name) {
            $options[$code] = $name;
        }

        return $options;
    }

}