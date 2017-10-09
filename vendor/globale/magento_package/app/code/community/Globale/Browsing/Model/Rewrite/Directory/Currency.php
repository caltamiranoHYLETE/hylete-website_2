<?php
class Globale_Browsing_Model_Rewrite_Directory_Currency extends Mage_Directory_Model_Currency {

	/**
	 * Use globale_max_decimal_places value in Magento formatting
	 * @param float $price
	 * @param array $options
	 * @param bool $includeContainer
	 * @param bool $addBrackets
	 * @return string
	 */
	public function format($price, $options = array(), $includeContainer = true, $addBrackets = false)
	{
		if(Mage::registry('globale_user_supported') && Mage::registry('globale_max_decimal_places') !== null){
			return $this->formatPrecision($price, Mage::registry('globale_max_decimal_places'), $options, $includeContainer, $addBrackets);
		}
		return parent::format($price, $options, $includeContainer, $addBrackets);
	}

}