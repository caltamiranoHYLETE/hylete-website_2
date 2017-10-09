<?php

class Globale_Order_Model_Observers_Shipment {

    /**
     * Send Parcel to Global-e once an International order is shipped
     * EVENT ==> sales_order_shipment_save_after
     * @param Varien_Event_Observer $Observer
     * @return $this
     */
    public function salesOrderShipmentSaveAfter(Varien_Event_Observer $Observer) {

        $Order = $Observer->getEvent()->getShipment()->getOrder();
        $GlobaleOrder = Mage::getModel('globale_order/orders')->load($Order->getIncrementId(),'order_id');
        
        /** @var Globale_Order_Model_Orders $OrderModel */
        $ShippingModel = Mage::getModel('globale_order/shipping');
        if($GlobaleOrder->hasData()){
            $ShippingModel->processGlobaleParcel($Observer, $GlobaleOrder);
        }
        return $this;
    }


}