<?php
class Globale_Order_Model_Observers_MyAccount {

	/**
	 * My account - Update Order totals prices
	 * Event ==> sales_order_load_after
	 * @param Varien_Event_Observer $Observer
	 * @access public
	 */
	public function changeOrderTotals(Varien_Event_Observer $Observer) {

		$Order = $Observer->getOrder();
		if($Order instanceof Mage_Sales_Model_Order){
			Mage::getModel('globale_order/myAccount')->changeOrderTotals($Order);
		}

	}


}