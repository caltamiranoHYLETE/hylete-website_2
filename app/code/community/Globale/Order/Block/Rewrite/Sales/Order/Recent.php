<?php
class Globale_Order_Block_Rewrite_Sales_Order_Recent extends Mage_Sales_Block_Order_Recent {

	/**
	 * Globale_Order_Block_Rewrite_Sales_Order_Recent constructor.
	 * If GlobaleOrder exists - change Order GrandTotal and RealOrderId
	 */
	public function __construct(){
		parent::__construct();

		/**@var $Orders Mage_Sales_Model_Resource_Order_Collection */
		$Orders = $this->getOrders();

		foreach ($Orders AS &$Order){
			/**@var $Order Mage_Sales_Model_Order */
			$IncrementId = $Order->getIncrementId();
			$GlobaleOrder = Mage::getModel('globale_order/myAccount')->getGlobaleOrder($IncrementId);
			if(!empty($GlobaleOrder)){
				$InternationalDetails = Mage::getModel('globale_order/myAccount')->getInternationalDetails($IncrementId);
				$RealOrderId = $GlobaleOrder['order_id'].' <br /> '.$GlobaleOrder['globale_order_id'];
				$GrandTotal = $InternationalDetails['details']['total_price'];

				$Order->setGrandTotal($GrandTotal);
				$Order->setRealOrderId($RealOrderId);
			}
		}
	}



}