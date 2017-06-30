<?php

class Ebizmarts_BakerlooRestful_Model_Api_Promotions extends Ebizmarts_BakerlooRestful_Model_Api_Api {

    protected $_model   = "salesrule/rule";
    public $defaultSort = "code";

    public function get() {
        Mage::throwException('Not implemented.');
    }

    public function post() {
        Mage::throwException('Not implemented.');
    }

    /**
     * Validate provided coupon code.
     * Receives an order and validates coupon code.
     *
     * PUT
     */
    public function put() {

        if(!$this->getStoreId()) {
            Mage::throwException('Please provide a Store ID.');
        }

        Mage::app()->setCurrentStore($this->getStoreId());

        //disable non-POS shipping methods
        Mage::helper('bakerloo_shipping')->disableShippingMethods($this->getStoreId());

        $data     = $this->getJsonPayload();
        $quote    = Mage::helper('bakerloo_restful/sales')->buildQuote($this->getStoreId(), $data, false);

        $cartData = Mage::helper('bakerloo_restful/sales')->getCartData($quote);

        $quote->delete();

        return $cartData;

    }


}