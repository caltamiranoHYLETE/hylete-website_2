<?php
class Globale_Order_Block_Rewrite_Sales_Order_Totals extends Mage_Sales_Block_Order_Totals {

	/**
	 * Add International Duties, Taxes & Fees total if it exists in Source
	 * @return $this
	 */
	protected function _initTotals()
	{
		parent::_initTotals();
		$TotalDutiesPrice = (float)$this->getSource()->getTotalDutiesPrice();

		if($TotalDutiesPrice){
			$TotalDutiesPriceTotal = new Varien_Object(array(
				'code'      => 'ge_duties',
				'value'     => $this->getSource()->getTotalDutiesPrice(),
				'label'     => $this->helper('sales')->__('International Duties, Taxes & Fees')
			));

			$this->addTotalBefore($TotalDutiesPriceTotal,'grand_total');
		}
		return $this;
	}
}