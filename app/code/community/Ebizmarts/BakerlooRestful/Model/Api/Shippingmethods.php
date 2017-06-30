<?php

class Ebizmarts_BakerlooRestful_Model_Api_Shippingmethods extends Ebizmarts_BakerlooRestful_Model_Api_Api {

    /**
     * Process GET requests.
     *
     * @return type
     * @throws Exception
     */
    public function get() {

        $store = $this->getStoreId();

        return Mage::helper('bakerloo_shipping')->getBakerlooShippingMethods($store);

    }

}