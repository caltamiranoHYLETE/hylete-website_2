<?php
class Globale_Order_Block_Rewrite_Sales_Order_Info extends Mage_Sales_Block_Order_Info {

	/**
	 * If GlobaleOrder exists - change RealOrderId
	 */
	protected function _prepareLayout()	{

		$Order = $this->getOrder();

		/**@var $Order Mage_Sales_Model_Order */
		$IncrementId = $Order->getIncrementId();
		$GlobaleOrder = Mage::getModel('globale_order/myAccount')->getGlobaleOrder($IncrementId);
		if(!empty($GlobaleOrder)){
			$RealOrderId = $GlobaleOrder['order_id'].' / '.$GlobaleOrder['globale_order_id'];
			$Order->setRealOrderId($RealOrderId);
		}

		parent::_prepareLayout();
	}
}