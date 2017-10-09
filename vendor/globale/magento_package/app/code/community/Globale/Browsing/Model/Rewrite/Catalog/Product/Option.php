<?php

/**
 * Rewrite Product Option class for beatify price per percentage options
 * Class Globale_Browsing_Model_Rewrite_Catalog_Product_Option
 */
class Globale_Browsing_Model_Rewrite_Catalog_Product_Option extends Mage_Catalog_Model_Product_Option {

	/**
	 * Rewrite option getPrice for adding global-e Beauty per percentage calculation
	 * @param bool $flag
	 * @return decimal|mixed
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