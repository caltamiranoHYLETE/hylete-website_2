<?php
use GlobalE\SDK\Models\Common;

class Globale_Browsing_Model_Quote_Address_Total_Preparing extends Mage_Sales_Model_Quote_Address_Total_Abstract
{

	/**
	 * @param Mage_Sales_Model_Quote_Address $Address
	 */
	public function collect(Mage_Sales_Model_Quote_Address $Address)
	{
		if (!Mage::registry('globale_user_supported')) {
			return;
		}

		parent::collect($Address);

		$this->calculateAverageTaxRate($Address);
	}


	/**
	 * Calculate Cart Average Tax Rate for future usage in SalesRule_Validator for convertAmount
	 * @param $Address
	 */
	protected function calculateAverageTaxRate(Mage_Sales_Model_Quote_Address $Address)
	{
		$Items = $this->_getAddressItems($Address);

		$AverageTaxRate = 0;
		$Total = 0;
		$ItemTaxValueArray = array();

		foreach ($Items as $Item) {
			/**@var $Item Mage_Sales_Model_Quote_Item */

			if ($Item->isDeleted() || !$Item->hasRowTotalInclTax()) {
				continue;
			}
			$Product = $Item->getProduct();

			if($Product->hasGlobaleProductInfo()){

				/**@var $Info Common\ProductResponseData **/
				$Info = $Product->getGlobaleProductInfo();

				$ItemTaxPercent = $Info->getVATRateType()->getRate();
				$ItemPrice = $Item->getRowTotalInclTax();

				$Total += $ItemPrice;
				$ItemTaxValueArray[] = $ItemPrice * $ItemTaxPercent;
			}
		}
		if ($Total) {
			foreach ($ItemTaxValueArray AS $ItemTaxValue) {
				$AverageTaxRate += $ItemTaxValue / $Total;
			}

			Mage::unregister('globale_cart_average_tax_rate');
			Mage::register('globale_cart_average_tax_rate',$AverageTaxRate);
		}
	}

}