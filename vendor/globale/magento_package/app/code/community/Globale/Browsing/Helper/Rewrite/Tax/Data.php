<?php

use GlobalE\SDK\Models\Common;

class Globale_Browsing_Helper_Rewrite_Tax_Data extends Mage_Tax_Helper_Data {

	/**
	 * Get product price with all tax settings processing
	 *
	 * @param   Mage_Catalog_Model_Product $Product
	 * @param   float $price inputted product price
	 * @param   bool $includingTax
	 * @param   null|Mage_Customer_Model_Address $shippingAddress
	 * @param   null|Mage_Customer_Model_Address $billingAddress
	 * @param   null|int $ctc customer tax class
	 * @param   null|Mage_Core_Model_Store $store
	 * @param   bool $priceIncludesTax
	 * @return  float
	 */
	public function getPrice($Product, $price, $includingTax = null, $shippingAddress = null, $billingAddress = null,
							 $ctc = null, $store = null, $priceIncludesTax = null, $roundPrice = true)
	{
		//@TODO if it's from calculate shipping price ==>  $this->shippingPriceIncludesTax
		$UserSupportedByGlobale = Mage::registry('globale_user_supported');
		if($UserSupportedByGlobale && !Mage::app()->getStore()->isAdmin() ){

			if (!$price) {
				return $price;
			}

			if(property_exists($this, '_app') && $this->_app instanceof Mage_Core_Model_App){
				$store = $this->_app->getStore($store);
			}else{
				$store = Mage::app()->getStore($store);
			}

			//load taxClassId percent from product if exists
			$Percent = $Product->getTaxPercent();
			$TaxClassId = $Product->getTaxClassId();

			/**@var $Calculator Mage_Tax_Model_Calculation **/
			$Calculator = Mage::getSingleton('tax/calculation');

			// if product doesn't have tax percent value load it from tax class.
			if (is_null($Percent)  && $Product->hasProduct() && $Product->getProduct()->hasGlobaleProductInfo() )  {

				/**@var $GlobaleProductInfo  Common\ProductResponseData */
				$GlobaleProductInfo = $Product->getProduct()->getGlobaleProductInfo();
				$Percent = $GlobaleProductInfo->getVATRateType()->Rate;
				$Product->setTaxPercent($Percent);
			}



			// Load AppliedRates and save it to Product
			if ($Product->getAppliedRates() == null) {
				$request = $Calculator->getRateRequest($shippingAddress, $billingAddress, $ctc, $store);
				$request->setProductClassId($TaxClassId);
				$appliedRates =  $Calculator->getAppliedRates($request);

				$Product->setAppliedRates($appliedRates);
			}



			return $price;
		}


		return parent::getPrice($Product, $price, $includingTax, $shippingAddress, $billingAddress,
			$ctc, $store, $priceIncludesTax, $roundPrice);
	}
}