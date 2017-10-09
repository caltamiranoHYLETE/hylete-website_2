<?php
use GlobalE\SDK\SDK;
use GlobalE\SDK\Models\Common;

class Globale_Order_Model_Api2_Rest_Admin_V1 extends  Globale_Base_Model_Api2_Restapi  {


    const ACTION_CREATE = 'create';
    const ACTION_PAYMENT = 'payment';
    const ACTION_UPDATE_STATUS = 'statusupdate';
    /**@deprecated  updateStatus is the old style */
    const ACTION_UPDATE_STATUS_OLD = 'updateStatus';
    const ACTION_UPDATE_ORDER_SHIPPING  = 'updateShipping';

	/**
	 * Create Output data per international/order/:action request
	 * @return array
	 */
	protected function createOutputData(){

		$Action = $this->getRequest()->getParam('action');
		$requestData = $this->getRequest()->getBodyParams();

		/** @var GlobalE\SDK\SDK $GlobaleSDK */
		$GlobaleSDK = Mage::registry('globale_sdk');

		switch ($Action){
			case self::ACTION_UPDATE_STATUS:
			case self::ACTION_UPDATE_STATUS_OLD:
				/** @var Globale_Order_Model_Handle_Statusupdate $OrderModel */
				$OrderModel = Mage::getModel('globale_order/handle_statusupdate');
				$Response = $GlobaleSDK->Merchant()->HandleOrderStatusUpdate(json_encode($requestData),$OrderModel,false);
			break;
            case self::ACTION_CREATE:
                /** @var Globale_Order_Model_Handle_Create $OrderModel */
                $OrderModel = Mage::getModel('globale_order/handle_create');
                $Response = $GlobaleSDK->Merchant()->HandleOrderCreation(json_encode($requestData),$OrderModel,false);
                break;
            case self::ACTION_PAYMENT:
                /** @var Globale_Order_Model_Handle_Payment $OrderModel */
                $OrderModel = Mage::getModel('globale_order/handle_payment');
                $Response = $GlobaleSDK->Merchant()->HandleOrderPayment(json_encode($requestData),$OrderModel,false);
                break;
            case  self::ACTION_UPDATE_ORDER_SHIPPING:
                /** @var Globale_Order_Model_Handle_Shipping $OrderModel */
                $OrderModel = Mage::getModel('globale_order/handle_shipping');
                $Response = $GlobaleSDK->Merchant()->HandleOrderShippingInfo(json_encode($requestData),$OrderModel,false);
                break;
			default:
				$Success = false;
				$Message = "Action '$Action' not supported.";
				$Response = new Common\Response($Success, $Message);
		}

		$OutputData = $Response->getObjectVars();
		return $OutputData;
	}

}