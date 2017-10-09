<?php

use GlobalE\SDK\Models\Common\Request;
use GlobalE\SDK\Core;

/**
 * Class Globale_Order_Model_Shipping
 */
class Globale_Order_Model_Shipping extends Mage_Core_Model_Abstract
{
	const PARTIAL_SHIPPED_STATUS = 'PartialDispatch';

	public function _construct()
    {
        parent::_construct();
        $this->_init('globale_order/shipping'); // this is location of the resource file.
    }

    /**
     * Save shipping for Order in DB, in globale_order_shipping
     * @param $Request
     * @param $IncrementId
     */
    public function saveShipping($Request, $IncrementId) {

        $this->setOrderId($IncrementId);
        $this->setUserId($Request->UserId);
        $this->setOrderTrackingNumber($Request->InternationalDetails->OrderTrackingNumber);
        $this->setOrderTrackingUrl($Request->InternationalDetails->OrderTrackingUrl);
        $this->setOrderWaybillNumber($Request->InternationalDetails->OrderWaybillNumber);
        $this->setShippingMethodTypeCode($Request->InternationalDetails->ShippingMethodTypeCode);
        $this->setShippingMethodTypeName($Request->InternationalDetails->ShippingMethodTypeName);
        $this->setShippingMethodStatusCode($Request->InternationalDetails->ShippingMethodStatusCode);
        $this->setShippingMethodStatusName($Request->InternationalDetails->ShippingMethodStatusName);
        $this->setGlobaleShippingMethodCode($Request->ShippingMethodCode); // changed 1.1.0 ShippingMethodCode => GlobaleShippingMethodCode
        //$this->setTotalDutiesPrice($Request->InternationalDetails->TotalDutiesPrice); // moved to details 1.1.0
        $this->setTotalPrice($Request->InternationalDetails->TotalShippingPrice);
        $this->setTransactionCurrencyCode($Request->InternationalDetails->TransactionCurrencyCode);
        // added on 1.1.0
        $this->setCustomerShippingMethodCode($Request->InternationalDetails->ShippingMethodCode);
        $this->setCustomerShippingMethodName($Request->InternationalDetails->ShippingMethodName);
        $this->setDiscountedShippingPrice($Request->InternationalDetails->DiscountedShippingPrice);
        $this->setShipmentStatusUpdateTime($Request->InternationalDetails->ShipmentStatusUpdateTime);
        $this->setShipmentLocation($Request->InternationalDetails->ShipmentLocation);
        $this->save();
    }

    /**
     * Process the shipment Parcel creation and status change in Global-e if partially shipped.
     * @param Varien_Event_Observer $Observer
     * @param $GlobaleOrder
     */
    public function processGlobaleParcel(Varien_Event_Observer $Observer, $GlobaleOrder) {

        /** @var GlobalE\SDK\SDK $GlobaleSDK */
        $GlobaleSDK = Mage::registry('globale_sdk');
        /**@var $CurrencyModel Globale_Browsing_Model_Initializer */
        $InitializerModel = Mage::getModel('globale_browsing/initializer');
        $InitializerModel->OnPageLoad($GlobaleSDK);

        $EnableAutomaticManifestProcess = Core\Settings::get('AppSettings.ServerSettings.EnableAutomaticManifestProcess.Value');
        /** @var Mage_Sales_Model_Order_Shipment $Shipment */
        $Shipment = $Observer->getEvent()->getShipment();

        $ShippedItems = $Shipment->getAllItems();
        $Order = $Shipment->getOrder();
        $Status = $Order->getStatus();

		$ShipmentOrigData = $Shipment->getOrigData();

        if(!empty($ShipmentOrigData) || $EnableAutomaticManifestProcess != 'false' || $Status == 'completed' || count($ShippedItems) === 0){
            return;
        }

        $Parcel = $this->createParcel($Shipment);

        $Response = $GlobaleSDK->Admin()->UpdateParcelDispatch($GlobaleOrder->getGlobaleOrderId(), array($Parcel));

        if($Response->getSuccess() && $this->isPartiallyShipped($Order)){
            $this->changeStatusPartially($Order);
        }
    }

    /**
     * Create the Global-e Parcel that will be sent via UpdateParcelDispatch API.
     * @param Mage_Sales_Model_Order_Shipment $Shipment
     * @return Request\Parcel
     */
    public function createParcel(Mage_Sales_Model_Order_Shipment $Shipment){

        $ShippedItems = $Shipment->getAllItems();
        $ParcelProductArray = array();

        /** @var Mage_Sales_Model_Order_Shipment_Item $Item */
        foreach ($ShippedItems as $Item){
            $ParcelProduct = new Request\ParcelProduct();
            $ParcelProduct->setProductCode( $Item->getSku() );
            //$ParcelProduct->setCartItemId( $Item->getOrderItemId() ); //removed in 1.3.1 as $Qoute->itemId and $Order->itemId are different.
            $ParcelProduct->setDeliveryQuantity( $Item->getQty() );
            $ParcelProductArray[] = $ParcelProduct;
        }

        $Parcel = new Request\Parcel();
        $Parcel->setParcelCode( $Shipment->getIncrementId() );
        $Parcel->setProducts( $ParcelProductArray );

        return $Parcel;

    }

    /**
     * Check if NOT all items in orders were shipped and the order is partially shipped.
     * @param Mage_Sales_Model_Order $Order
     * @return bool
     */
    public function isPartiallyShipped(Mage_Sales_Model_Order $Order){

        $OrderItems = $Order->getAllVisibleItems();
        $TotalQtyLeft = 0;

        /** @var Mage_Sales_Model_Order_Item $Item */
        foreach ($OrderItems as $Item) {
            $TotalQtyLeft = $Item->getQtyOrdered() - $Item->getQtyShipped();
        }

        return ($TotalQtyLeft > 0);

    }

    /**
     * Send an UpdateStatus call to Global-e API.
     * @param Mage_Sales_Model_Order $Order
     */
    public function changeStatusPartially(Mage_Sales_Model_Order $Order){
        /** @var Globale_Order_Model_Orders $OrderModel */
        $OrderModel = Mage::getModel('globale_order/orders');
        $OrderModel->sendUpdateStatusRequest($Order, self::PARTIAL_SHIPPED_STATUS );
    }


}