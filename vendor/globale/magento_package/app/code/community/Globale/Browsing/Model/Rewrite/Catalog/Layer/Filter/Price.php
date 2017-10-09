<?php

class Globale_Browsing_Model_Rewrite_Catalog_Layer_Filter_Price extends Mage_Catalog_Model_Layer_Filter_Price
{
	/**
	 * Convert Prices according to SDK logic (currencies and etc.)
	 *
	 * @param float|string $fromPrice
	 * @param float|string $toPrice
	 * @return string
	 */
	protected function _renderRangeLabel($fromPrice, $toPrice)
	{
		if(Mage::registry('globale_user_supported')){
			$fromPrice = Mage::helper('globale_browsing/price')->convertAmount($fromPrice);
			$toPrice = Mage::helper('globale_browsing/price')->convertAmount($toPrice);
		}

		return parent::_renderRangeLabel($fromPrice, $toPrice);
	}




}