<?php

/**
 * Class Globale_Browsing_Model_Rewrite_Catalog_Product_Option_Value
 */
class Globale_Browsing_Model_Rewrite_Catalog_Product_Option_Value extends Mage_Catalog_Model_Product_Option_Value{

	/**
	 * Rewrite option getPrice for adding global-e Beauty per percentage calculation
	 * @param bool $flag
	 * @return float|int|mixed
	 */
	public function getPrice($flag = false)
	{
		$Price = parent::getPrice($flag);
		//if globale_user_supported ,percentage price calculation (type = percent and flag = true)
		if(Mage::registry('globale_user_supported') && $this->getPriceType() == 'percent' && $flag){
			$Price = Mage::helper('globale_browsing')->getBeautyAmount($Price,true);
		}
		return $Price;
	}
}