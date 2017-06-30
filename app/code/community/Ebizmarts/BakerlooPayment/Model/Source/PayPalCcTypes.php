<?php

class Ebizmarts_BakerlooPayment_Model_Source_PayPalCcTypes {

    private function _getCcs() {
        return array(
            'cash'         => Mage::helper('bakerloo_payment')->__('Cash'),
            'credit_debit' => Mage::helper('bakerloo_payment')->__('Credit/Debit Card'),
            'paypal'       => Mage::helper('bakerloo_payment')->__('PayPal'),
        );
    }

    public function toOptionArray() {
        $options =  array();

        $ccTypes = $this->_getCcs();

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

        $ccTypes = $this->_getCcs();

        foreach ($ccTypes as $code => $name) {
            $options[$code] = $name;
        }

        return $options;
    }

}