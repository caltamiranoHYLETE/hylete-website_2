<?php

class Ebizmarts_BakerlooRestful_Model_Api_Shippingrates extends Ebizmarts_BakerlooRestful_Model_Api_Api {

    public function get() {
        throw new Exception('Incorrect request. GET not implemented.');
    }

    public function post() {

        parent::post();

        if(!$this->getStoreId()) {
            Mage::throwException('Please provide a Store ID.');
        }

        Mage::app()->setCurrentStore($this->getStoreId());

        $data = $this->getJsonPayload();

        $quote = Mage::helper('bakerloo_restful/sales')->buildQuote($this->getStoreId(), $data, true);

        $groups = $quote->getShippingAddress()->getGroupedAllShippingRates();

        $rates = array();

        foreach ($groups as $_rates) {
            foreach ($_rates as $_rate) {

                array_push(
                    $rates,
                    array(
                        'code'  => $_rate->getCode(),
                        'title' => $_rate->getMethodTitle(),
                        'price' => (float)$_rate->getPrice(),
                        'sort_order' => (int)$_rate->getCarrierSortOrder()
                    )
                );
            }
        }

        //DELETE quote so we don't leave garbage in db
        $quote->delete();

        return $rates;
    }

}