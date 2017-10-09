<?php

use GlobalE\SDK\Models\Common\Response;
use GlobalE\SDK\Models\Common\iHandleAction;

/**
 * Class Globale_Order_Model_Handle_Shipping
 */
class Globale_Order_Model_Handle_Shipping extends Mage_Core_Model_Abstract implements iHandleAction
{

    /**
     * @param \stdClass $Request
     * @return Response\Order
     */
    public function handleAction($Request)
    {

        try {
            $ISValid = $this->validateAction($Request);
            if(!$ISValid->getSuccess()){
                return $ISValid;
            }

            $this->updateMagentoShipment($Request);
            $this->updateGlobaleShipment($Request);

            $Response = new Response(true);

        } catch (\Exception $E) {
            $Response = new Response(false, $E->getMessage());
        }

        return $Response;
    }

    /**
     * @param $Request
     * @return Response
     */
    protected function validateAction($Request){

        $Valid = new Response(true);

        if(!empty($Request->OrderId)){
            $OrderId = Mage::getModel('globale_order/orders')->load($Request->OrderId,'globale_order_id')->getId();
        }

        if(empty($OrderId)){
            $Valid = new Response(false, "Order {$Request->OrderId} - not found.\r\n");
        }

        return $Valid;
    }


    /**
     * @param $Request
     * @throws Exception
     */
    protected function updateMagentoShipment($Request)
    {

        $GlobaleOrder = Mage::getModel('globale_order/orders')->load($Request->OrderId,'globale_order_id');

        /** @var Mage_Sales_Model_Order $MagentoOrder */
        $MagentoOrder = Mage::getModel('sales/order')->load($GlobaleOrder->orderId, 'increment_id');

        /** @var Mage_Sales_Model_Resource_Order_Shipment_Collection $ExistingShipment */
        $ExistingShipment = $MagentoOrder->getShipmentsCollection();

        /** @var Mage_Sales_Model_Order_Shipment_Api $OrderShipmentAPI */
        $OrderShipmentAPI = Mage::getModel('sales/order_shipment_api');

        /** @var Globale_Base_Model_Carrier $Carrier */
        $Carrier = $MagentoOrder->getShippingCarrier();

        if ($ExistingShipment->count() > 0) {

            /** @var Mage_Sales_Model_Order_Shipment $Shipment */
            foreach($ExistingShipment as $Shipment) {
                /** @var Mage_Sales_Model_Resource_Order_Shipment_Track_Collection $Tracks */
                $Tracks = $Shipment->getTracksCollection();
                if($Tracks->count() == 0) {
                    $OrderShipmentAPI->addTrack($Shipment->incrementId, $Carrier->getCarrierCode(), $Carrier->getTitle(), $Request->InternationalDetails->OrderTrackingNumber);
                } else {
                    $Exists = false;
                    /** @var Mage_Sales_Model_Order_Shipment_Track $Track */
                    foreach($Tracks as $Track) {
                        if ($Track->getNumber() == $Request->InternationalDetails->OrderTrackingNumber) {
                            $Exists = true;
                            break;
                        }
                    }
                    if (!$Exists) {
                        $OrderShipmentAPI->addTrack($Shipment->incrementId, $Carrier->getCarrierCode(), $Carrier->getTitle(), $Request->InternationalDetails->OrderTrackingNumber);
                    }
                }
            }
        }

    }

    /**
     * @param $Request
     */
    protected function updateGlobaleShipment($Request)
    {

        $GlobaleOrder = Mage::getModel('globale_order/orders')->load($Request->OrderId,'globale_order_id');

        /** @var Mage_Sales_Model_Order $MagentoOrder */
        $MagentoOrder = Mage::getModel('sales/order')->load($GlobaleOrder->orderId, 'increment_id');

        /** @var Globale_Order_Model_Resource_Shipping_Collection $Shippings */
        $Shippings = Mage::getModel('globale_order/shipping')->getCollection()->addFilter('order_id', $MagentoOrder->incrementId);

        $Exists = false;
        foreach($Shippings as $Shipping) {
            /** @var Globale_Order_Model_Shipping $Shipping */
            if($Shipping->getOrderTrackingNumber() == $Request->InternationalDetails->OrderTrackingNumber){
                // already have it - update URL
                $Exists = true;
                $Shipping->setOrderTrackingUrl($Request->InternationalDetails->OrderTrackingUrl);
                $Shipping->save();
                break;
            }
        }
        if(!$Exists) {
            $NewShipping = clone $Shippings->getFirstItem();
            $NewShipping->setId(null);
            $NewShipping->setOrderTrackingNumber($Request->InternationalDetails->OrderTrackingNumber);
            $NewShipping->setOrderTrackingUrl($Request->InternationalDetails->OrderTrackingUrl);
            $NewShipping->save();
        }
    }

}

