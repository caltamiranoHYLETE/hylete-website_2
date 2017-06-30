<?php

class Ebizmarts_BakerlooPayment_Model_Source_SagePayServerAccountType {

    private function _getAvailableModes() {
        return array(
            'E' => Mage::helper('bakerloo_payment')->__('E-commerce'), //Vendorname taken from ecommerce config from Sage Pay Suite module.
            'M' => Mage::helper('bakerloo_payment')->__('Mail order / telephone order (MOTO)'), //Vendorname taken from MOTO config from Sage Pay Suite module.
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