<?php

class Newgistics_Shipping_Model_Carrier
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    /**
     * Carrier's code, as defined in parent class
     *
     * @var string
     */
    protected $_code = 'newgistics_shipping';

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
        $expressMaxWeight = Mage::helper('newgistics_shipping')->getExpressMaxWeight();

        $expressAvailable = false; //We have express turned off for now
        foreach ($request->getAllItems() as $item) {
            if ($item->getWeight() > $expressMaxWeight) {
                $expressAvailable = false;
            }
        }

        if ($expressAvailable) {
            $result->append($this->_getExpressRate());
        }
        
        if($this->getConfigData('use_free_shipping') == 1) {

            if ($request->getFreeShipping()) {
                /**
                 *  If the request has the free shipping flag,
                 *  append a free shipping rate to the result.
                 */
                $freeShippingRate = $this->_getFreeShippingRate();
                $result->append($freeShippingRate);
            } else {
                $result->append($this->_getStandardRate());
            }

        } else {
            $result->append($this->_getStandardRate());
        }

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
            $baseRate= 5.99;
        }

        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setMethod('parcel_select');
        $rate->setMethodTitle($this->getConfigData('shipping_method_title'));
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

        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setMethod('express');
        $rate->setMethodTitle('Express Delivery');
        $rate->setPrice(12.3);
        $rate->setCost(0);

        return $rate;
    }
    
    public function getTrackingInfo($tracking)
    {
        $track = mage::getmodel('shipping/tracking_result_status'); 
        $track->setUrl('https://tools.usps.com/go/TrackConfirmAction.action?tLabels='.$tracking);
        $track->setTracking($tracking);
        $track->setCarrier('newgistics_shipping');
        $track->setCarrierTitle($this->getConfigData('shipping_method_title'));
        
        return $track;
    }

    protected function _getFreeShippingRate()
    {
        $rate = Mage::getModel('shipping/rate_result_method');
        /* @var $rate Mage_Shipping_Model_Rate_Result_Method */
        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setMethod('parcel_select');
        $rate->setMethodTitle($this->getConfigData('free_shipping_title'));
        $rate->setPrice(0);
        $rate->setCost(0);
        return $rate;
    }
    
    public function isTrackingAvailable()
    {
        return true;
    }
}