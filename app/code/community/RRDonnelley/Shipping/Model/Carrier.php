<?php

class RRDonnelley_Shipping_Model_Carrier
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    /**
     * Carrier's code, as defined in parent class
     *
     * @var string
     */
    protected $_code = 'rrdonnelley_shipping';

    /**
     * Returns available shipping rates for Inchoo Shipping carrier
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        /** @var Mage_Shipping_Model_Rate_Result $result */
        $result = Mage::getModel('shipping/rate_result');

        /** @var Inchoo_Shipping_Helper_Data $expressMaxProducts */
        $expressMaxWeight = Mage::helper('rrdonnelley_shipping')->getExpressMaxWeight();

        $expressAvailable = false; //We have express turned off for now
        foreach ($request->getAllItems() as $item) {
            if ($item->getWeight() > $expressMaxWeight) {
                $expressAvailable = false;
            }
        }

        if ($expressAvailable) {
            $result->append($this->_getExpressRate());
        }

        $result->append($this->_getStandardRate());

        return $result;
    }

    /**
     * Returns Allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return array(
            'standard'    =>  'Standard Delivery',
            'express'     =>  'Express Delivery',
        );
    }

    /**
     * Get Standard rate object
     *
     * @return Mage_Shipping_Model_Rate_Result_Method
     */
    protected function _getStandardRate()
    {
        /** @var Mage_Shipping_Model_Rate_Result_Method $rate */
        $rate = Mage::getModel('shipping/rate_result_method');

        $baseRate = $this->getConfigData('base_price');
        if($baseRate == "") {
            $baseRate = 14.99;
        }

        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setMethod('rrd_intl');
        $rate->setMethodTitle($this->getConfigData('methodtitle'));
        $rate->setPrice($baseRate);
        $rate->setCost(0);

        return $rate;
    }

    /**
     * Get Express rate object
     *
     * @return Mage_Shipping_Model_Rate_Result_Method
     */
    protected function _getExpressRate()
    {
        /** @var Mage_Shipping_Model_Rate_Result_Method $rate */
        $rate = Mage::getModel('shipping/rate_result_method');

        $baseRate = $this->getConfigData('base_price');
        if($baseRate == "") {
            $baseRate = 40.99;
        }

        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setMethod('rrd_intl');
        $rate->setMethodTitle($this->getConfigData('expressmethodtitle'));
        $rate->setPrice($baseRate);
        $rate->setCost(0);

        return $rate;
    }

    public function getTrackingInfo($tracking)
    {
        $track = mage::getmodel('shipping/tracking_result_status');
        $track->setUrl('http://www.ppxtrack.com/t/NewgisticsInc?id='.$tracking);
        $track->setTracking($tracking);
        $track->setCarrier("rrdonnelley_shipping");
        $track->setCarrierTitle($this->getConfigData('trackingtitle'));

        return $track;
    }

    public function isTrackingAvailable()
    {
        return true;
    }
}