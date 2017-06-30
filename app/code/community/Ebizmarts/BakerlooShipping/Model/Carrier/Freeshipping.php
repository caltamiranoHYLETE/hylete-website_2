<?php

class Ebizmarts_BakerlooShipping_Model_Carrier_Freeshipping extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface {

    protected $_code = 'bakerloo_freeshipping';
    protected $_isFixed = true;
    protected $_active  = false;

    public function __construct() {
        parent::__construct();

        $apiKey = Mage::app()->getRequest()->getHeader(Mage::helper('bakerloo_restful')->getApiKeyHeader());

        if($apiKey !== false) {
            $this->_active = true;
        }

    }

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
        $method->setMethodTitle($this->getConfigData('name'));

        $method->setPrice('0.00');
        $method->setCost('0.00');

        $result->append($method);

        return $result;
    }

    public function getAllowedMethods() {
        return array($this->_code => $this->getConfigData('name'));
    }

}