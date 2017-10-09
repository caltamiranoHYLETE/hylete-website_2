<?php

class Globale_Browsing_Model_Rewrite_Sales_Order_Shipment_Track extends Mage_Sales_Model_Order_Shipment_Track
{

    protected $GlobaleShipping = false;

    /**
     * Checks if track has globale shipping
     * @return bool
     */
    public function hasGlobaleShipping()
    {
        if ($this->GlobaleShipping === false) {
            /** @var Globale_Order_Model_Shipping $Shipping */
            $this->GlobaleShipping = Mage::getModel('globale_order/shipping')
                ->getCollection()
                ->addFilter('order_id', $this->getShipment()->getOrder()->getIncrementId())
                ->addFilter('order_tracking_number', $this->getNumber())
                ->getFirstItem();
        }

        if ($this->GlobaleShipping !== null && $this->GlobaleShipping->getId()) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Get tracking url
     * @return string
     */
    public function getUrl()
    {
        $url = null;

        if ($this->hasGlobaleShipping()){
            $url = $this->GlobaleShipping->getOrderTrackingUrl();
        } else {
            $url = parent::getUrl();
        }

        return $url;

    }

    /**
     * @return null|string
     */
    public function getTracking()
    {
        $tracking = null;

        if ($this->hasGlobaleShipping()) {
            $tracking = $this->getNumber();
        } else {
            $tracking = parent::getTracking();
        }

        return $tracking;
    }

    public function getCarrierTitle()
    {
        return Mage::getModel('globale_base/carrier')->getConfigData('title');
    }
}