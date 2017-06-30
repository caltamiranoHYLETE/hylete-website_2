<?php

class Ebizmarts_BakerlooPayment_Model_Source_Testorlive {

    private function _getAvailableModes() {
        return array(
            'test' => Mage::helper('bakerloo_payment')->__('Test'),
            'live' => Mage::helper('bakerloo_payment')->__('Live'),
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