<?php
class Globale_Order_Block_Rewrite_Tax_Sales_Order_Tax extends  Mage_Tax_Block_Sales_Order_Tax {


	/**
	 * Don't add add Tax total to totals if GlobaleOrder exists
	 * @param string $after
	 * @return Mage_Tax_Block_Sales_Order_Tax
	 */
	protected function _addTax($after = 'discount')
	{
		$Order = $this->getOrder();
		$IncrementId = $Order->getIncrementId();
		$GlobaleOrder = Mage::getModel('globale_order/myAccount')->getGlobaleOrder($IncrementId);

		if(!empty($GlobaleOrder)){
			return $this;
		}
		return parent::_addTax($after);
	}
}