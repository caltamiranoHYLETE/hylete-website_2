<?php

class Globale_Browsing_Model_Rewrite_Shipping_Info extends Mage_Shipping_Model_Info
{

    /**
     * Retrieve all tracking by order id
     *
     * @return array
     */
    public function getTrackingInfoByOrder()
    {
        $shipTrack = array();
        $order = $this->_initOrder();

        if ($order) {

            $shipments = $order->getShipmentsCollection();
            foreach ($shipments as $shipment){

                $increment_id = $shipment->getIncrementId();
                $tracks = $shipment->getTracksCollection();

                $trackingInfos=array();
                foreach ($tracks as $track){
                    if ($track->getCarrierCode() == \Globale_Base_Model_Carrier::CODE) {
                        $trackingInfos[] = $track;
                    } else {
                        $trackingInfos[] = $track->getNumberDetail();
                    }
                }
                $shipTrack[$increment_id] = $trackingInfos;
            }
        }
        $this->_trackingInfo = $shipTrack;
        return $this->_trackingInfo;
    }

    /**
     * Retrieve all tracking by ship id
     *
     * @return array
     */
    public function getTrackingInfoByShip()
    {
        $shipTrack = array();
        $shipment = $this->_initShipment();
        if ($shipment) {

            $increment_id = $shipment->getIncrementId();
            $tracks = $shipment->getTracksCollection();

            $trackingInfos=array();
            foreach ($tracks as $track){
                if ($track->getCarrierCode() == \Globale_Base_Model_Carrier::CODE) {
                    $trackingInfos[] = $track;
                } else {
                    $trackingInfos[] = $track->getNumberDetail();
                }
            }
            $shipTrack[$increment_id] = $trackingInfos;

        }
        $this->_trackingInfo = $shipTrack;
        return $this->_trackingInfo;
    }

    /**
     * Retrieve tracking by tracking entity id
     *
     * @return array
     */
    public function getTrackingInfoByTrackId()
    {
        $track = Mage::getModel('sales/order_shipment_track')->load($this->getTrackId());

        if ($track->getId() && $this->getProtectCode() == $track->getProtectCode()) {
            if ($track->getCarrierCode() == \Globale_Base_Model_Carrier::CODE) {
                $this->_trackingInfo = array(array($track));
            } else {
                $this->_trackingInfo = array(array($track->getNumberDetail()));
            }
        }
        return $this->_trackingInfo;
    }


}