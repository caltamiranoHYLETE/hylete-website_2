<?php

class Ebizmarts_BakerlooPayment_Model_Source_Shipyesno {

    private function _getAvailableModes() {
        return array(
            '0' => Mage::helper('bakerloo_payment')->__('No'),
            '1' => Mage::helper('bakerloo_payment')->__('Yes'),
            '2' => Mage::helper('bakerloo_payment')->__('Use shipping method setting'),
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