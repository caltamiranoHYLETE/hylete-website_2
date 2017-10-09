<?php

/**
 * Rewrite Option type default class for beatify price per percentage options in calculation
 * Class Globale_Browsing_Model_Rewrite_Catalog_Product_Option_Type_Default
 */
class Globale_Browsing_Model_Rewrite_Catalog_Product_Option_Type_Default extends Mage_Catalog_Model_Product_Option_Type_Default   {

	/**
	 * Get Chargeable Option Price. Beatify in case of Global-e percentage
	 * @param float $price
	 * @param bool $isPercent
	 * @param float $basePrice
	 * @return float|mixed
	 */
	protected function _getChargableOptionPrice($price, $isPercent, $basePrice)
	{
		$ChargeableOptionPrice = parent::_getChargableOptionPrice($price, $isPercent, $basePrice);

		if(Mage::registry('globale_user_supported') && !$this->optionHasProductWithoutGelobaleInfo() && $isPercent){
			$ChargeableOptionPrice = Mage::helper('globale_browsing')->getBeautyAmount($ChargeableOptionPrice,true);
		}
		return $ChargeableOptionPrice;
	}


	protected function optionHasProductWithoutGelobaleInfo(){
		return (isset($this->_option) && $this->_option->getProduct() && !$this->_option->getProduct()->hasGlobaleProductInfo() );
	}
}