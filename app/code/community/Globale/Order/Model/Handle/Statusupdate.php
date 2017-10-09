<?php

use GlobalE\SDK\API\Common;
use GlobalE\SDK\Models\Common\Response;
use GlobalE\SDK\Models\Common\iHandleAction;
use GlobalE\SDK\Models\Common\Request;

// TODO: Cancel invoiced orders also

/**
 * Manipulate product price conversion
 * Class Globale_Browsing_Model_Conversion
 */
class Globale_Order_Model_Handle_Statusupdate extends Mage_Core_Model_Abstract implements iHandleAction
{
    
	public function handleAction($Request){

		$GlobaleOrder = Mage::getModel('globale_order/orders')->load($Request->OrderId,'globale_order_id');
		$Data = $GlobaleOrder->getData();
		if(empty($Data)){
			$Response = new Response\Order(false,'Order id not found.',$Request->OrderId);
			return $Response;
		}
		$OrderId = $GlobaleOrder->getOrderId();

		/** @var Mage_Sales_Model_Order $Order */
		$Order = Mage::getModel('sales/order')->load($OrderId, 'increment_id');

		$Data = $Order->getData();
		if(empty($Data)){
			$Response = new Response\Order(false,'Order id not found.',$Request->OrderId);
		}

		if(!isset($Response) && $Request->StatusCode === Mage_Sales_Model_Order::STATE_CANCELED){

			//Delete invoices
			$Invoices = $Order->getInvoiceCollection();
			foreach ($Invoices as $Invoice){
				$Invoice->delete();
			}

			//set Invoiced Qty to 0
			foreach ($Order->getAllItems() as $item) {
				$item->setQtyInvoiced(0);
			}

			if(!$Order->canCancel()){
				$Response = new Response\Order(false,"Order Can't be Canceled",$Request->OrderId);
				return $Response;
			}

			try {
				$Order->cancel();
			} catch (Exception $E) {
				$Response = new Response\Order(false,"Error canceling order: ".$E->getMessage(),$Request->OrderId);
				return $Response;

			}

			$Order->save();
			$Response = new Response\Order(true,'',$Request->OrderId);
		}

		if(!isset($Response)){
			$Response = new Response\Order(false,'Status code not supported.', $Request->OrderId);
		}

		return $Response;
	}

}

