<?php

class Ebizmarts_BakerlooPayment_Model_Source_Paymentmethods
    extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {

    /**
     * Retrieve All options
     *
     * @return array
     */
    public function getAllOptions() {
        if (is_null($this->_options)) {

            $this->_options = array();

            foreach (Mage::helper('bakerloo_payment')->getBakerlooPaymentMethods(null) as $method) {
                $this->_options[] = array(
                    'label' => $method['label'],
                    'value' => $method['code'],
                );
            }
        }
        return $this->_options;
    }
}
