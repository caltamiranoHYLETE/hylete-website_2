<?php

/**
 * Rewrite Configurable Product View Class for adding Global-e logic to percentage price
 * Class Globale_Browsing_Block_Rewrite_Product_View_Type_Configurable
 */
class Globale_Browsing_Block_Rewrite_Catalog_Product_View_Type_Configurable extends Mage_Catalog_Block_Product_View_Type_Configurable {


	/**
	 * Beauty oldPrice of Configurable percentage option
	 * @param float $Price
	 * @param bool $IsPercent
	 * @return mixed
	 */
	protected function _prepareOldPrice($Price, $IsPercent = false)
	{
		$PrepareOldPrice = parent::_prepareOldPrice($Price, $IsPercent);
		if(Mage::registry('globale_user_supported') && $IsPercent ){
			$PrepareOldPrice = Mage::helper('globale_browsing')->getBeautyAmount($PrepareOldPrice,true);
		}
		return $PrepareOldPrice;
	}


}