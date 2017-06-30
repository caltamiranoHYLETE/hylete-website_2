<?php

class Ebizmarts_BakerlooShipping_Model_Carrier_StorePickup extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface {

    protected $_code     = 'bakerloo_store_pickup';
    protected $_isFixed  = true;
    protected $_active   = true;

    /**
     * Allows free shipping when all product items have free shipping (promotions etc.)
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return void
     */
    protected function _updateFreeMethodQuote($request) {
        return true;
    }

    /**
     * FreeShipping Rates Collector
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request) {

        if (!$this->getConfigFlag('active') || !$this->_active) {
            return false;
        }

        $result = Mage::getModel('shipping/rate_result');

        $this->_updateFreeMethodQuote($request);

        $method = Mage::getModel('shipping/rate_result_method');

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod($this->_code);

        $chosenDesc = Mage::getSingleton('checkout/session')->getPosInStorePickupDesc();
        if($chosenDesc) {
            $method->setMethodTitle($chosenDesc);
        }
        else {
            $method->setMethodTitle($this->getConfigData('name'));
        }

        $price = $this->getConfigData('price');

        $method->setPrice($price); //(cost+handling)
        $method->setCost($price);

        $result->append($method);

        return $result;
    }

    public function getAllowedMethods() {
        return array($this->_code => $this->getConfigData('name'));
    }

}