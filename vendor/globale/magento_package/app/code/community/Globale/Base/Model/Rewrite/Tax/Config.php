<?php

/**
 * Rewrite Magento Tax configuration for displaying prices in Global-e mode including tax
 * Class Globale_Base_Model_Rewrite_Tax_Config
 */
class Globale_Base_Model_Rewrite_Tax_Config extends Mage_Tax_Model_Config
{

	/**
	 * If it's Globale Mode -->  GE support browsing mode or it's Globale Order in My Account
	 * @return bool
	 */
	protected function IsGlobaleChangesMode()
	{

		//If Globale browsing mode and not My Account  OR it's globale order in My Account
		return
			( Mage::registry('globale_user_supported') && !Mage::app()->getStore()->isAdmin() && Mage::registry('globale_my_account_order')=== null
				&& $this->priceIncludesTax()
			)
			|| Mage::registry('globale_my_account_order') == true ;
	}


	public function displaySalesPricesInclTax($store = null)
	{
		if($this->IsGlobaleChangesMode() ){
			return true;
		}
		return parent::displaySalesPricesInclTax($store);
	}

	public function displaySalesPricesExclTax($store = null)
	{
		if($this->IsGlobaleChangesMode() ){
			return false;
		}
		return parent::displaySalesPricesExclTax($store);
	}

	public function displaySalesPricesBoth($store = null)
	{
		if($this->IsGlobaleChangesMode() ){
			return false;
		}
		return parent::displaySalesPricesBoth($store);
	}

	public function displayCartPricesInclTax($store = null)
	{
		if($this->IsGlobaleChangesMode() ){
			return true;
		}
		return parent::displayCartPricesInclTax($store);
	}


	public function displayCartPricesExclTax($store = null)
	{
		if($this->IsGlobaleChangesMode() ){
			return false;
		}
		return parent::displayCartPricesExclTax($store);
	}

	public function displayCartPricesBoth($store = null)
	{
		if($this->IsGlobaleChangesMode() ){
			return false;
		}
		return parent::displayCartPricesBoth($store);
	}


	public function displayCartSubtotalInclTax($store = null)
	{
		if($this->IsGlobaleChangesMode() ){
			return true;
		}
		return parent::displayCartSubtotalInclTax($store);
	}

	public function displayCartSubtotalExclTax($store = null)
	{
		if($this->IsGlobaleChangesMode() ){
			return false;
		}
		return parent::displayCartSubtotalExclTax($store);
	}

	public function displayCartSubtotalBoth($store = null)
	{
		if($this->IsGlobaleChangesMode() ){
			return false;
		}
		return parent::displayCartSubtotalBoth($store);
	}


	public function displaySalesSubtotalInclTax($store = null)
	{
		if($this->IsGlobaleChangesMode() ){
			return true;
		}
		return parent::displaySalesSubtotalInclTax($store);
	}

	public function displaySalesSubtotalExclTax($store = null)
	{
		if($this->IsGlobaleChangesMode() ){
			return false;
		}
		return parent::displaySalesSubtotalExclTax($store);
	}

	public function displaySalesSubtotalBoth($store = null)
	{
		if($this->IsGlobaleChangesMode() ){
			return false;
		}
		return parent::displaySalesSubtotalBoth($store);
	}

	public function displaySalesShippingInclTax($store = null)
	{
		if($this->IsGlobaleChangesMode() ){
			return true;
		}
		return parent::displaySalesShippingInclTax($store);
	}

	public function displaySalesShippingExclTax($store = null)
	{
		if($this->IsGlobaleChangesMode() ){
			return false;
		}
		return parent::displaySalesShippingExclTax($store);
	}

	public function displaySalesShippingBoth($store = null)
	{
		if($this->IsGlobaleChangesMode() ){
			return false;
		}
		return parent::displaySalesShippingBoth($store);
	}

	public function getPriceDisplayType($store = null)
	{
		if($this->IsGlobaleChangesMode() ){
			return Mage_Tax_Model_Config::DISPLAY_TYPE_INCLUDING_TAX;
		}
		return parent::getPriceDisplayType($store);
	}


}