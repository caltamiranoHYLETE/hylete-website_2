<?php

use GlobalE\SDK\Models\Common;

class Globale_Browsing_Model_Quote_Address_Total_Base extends Mage_Sales_Model_Quote_Address_Total_Abstract {

    /**
     * @param Mage_Sales_Model_Quote_Address $Address
     * @return $this|Mage_Sales_Model_Quote_Address_Total_Abstract
     */
	public function collect(Mage_Sales_Model_Quote_Address $Address)
	{
		if (!Mage::registry('globale_user_supported')) {
			return $this;
		}

		parent::collect($Address);

		$this->recalculateItemsBasePrices($Address);
		$this->recalculateQuoteBasePrises($Address);

		return $this;

	}


	/**
	 * Recalculate Base prices for each Quote Item
	 * @param Mage_Sales_Model_Quote_Address $Address
	 */
	protected function recalculateItemsBasePrices(Mage_Sales_Model_Quote_Address $Address){

		$Items = $this->_getAddressItems($Address);

		$CalculationIncludeTax =  Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX);
		$ApplyTaxAfterDiscount = Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_APPLY_AFTER_DISCOUNT);
		$ApplyDiscountOnPriceIncludeTax = Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_DISCOUNT_TAX);

//

		foreach ($Items AS $Item){
			/**@var $Item Mage_Sales_Model_Quote_Item */

			$Product = $Item->getProduct();

			if ($Item->isDeleted() ||  $Product->hasGlobaleProductInfo() == false || $Item->getPrice() == 0) {
				continue;
			}
			/**@var $Info Common\ProductResponseData * */
			$Info = $Product->getGlobaleProductInfo();

			###### Calculate additions to price in base currency that came from selected options/attributes
			/**@var $ItemModel Globale_Browsing_Model_Item */
			$ItemModel = Mage::getModel('globale_browsing/item');

			/**@var $Item Mage_Sales_Model_Quote_Item */
			$BaseAdditionalSaleItemAmount = $ItemModel->calculateAdditionalItemAmount($Item,true,true);


			// base_price calculation

			if($CalculationIncludeTax){
				$BasePrice = ($Info->getOriginalSalePrice() + $BaseAdditionalSaleItemAmount) / (1 + $Item->getTaxPercent()/100 );

			}else{
				$BasePrice = $Info->getOriginalSalePrice() + $BaseAdditionalSaleItemAmount;
			}
			$Item->setBasePrice($BasePrice);


			// base_discount_amount calculation
			$DiscountRate = ( $Item->getPrice() - $Item->getDiscountAmount()) / $Item->getPrice();
			$BaseDiscountAmount = $BasePrice - ($BasePrice * $DiscountRate) ;
			$Item->setBaseDiscountAmount($BaseDiscountAmount);

			//base_row_total calculation
			$BaseRowTotal = $BasePrice * $Item->getQty();
			$Item->setBaseRowTotal($BaseRowTotal);


			//base_price_incl_tax calculation
			$BasePriceInclTax = $BasePrice * (1 + $Item->getTaxPercent()/100 );

			$Item->setBasePriceInclTax($BasePriceInclTax);


			//base_row_total_incl_tax calculation
			$BaseRowTotalInclTax = $BaseRowTotal * (1 + $Item->getTaxPercent()/100 );
			$Item->setBaseRowTotalInclTax($BaseRowTotalInclTax);


			//base_tax_amount calculation
			if($ApplyTaxAfterDiscount){
				$BaseTaxAmount = ( $BaseRowTotalInclTax -( $BaseRowTotalInclTax/((1 + $Item->getTaxPercent()/100))) ) - $Item->getBaseDiscountAmount();
			}else{
				$BaseTaxAmount = $BaseRowTotalInclTax - ( $BaseRowTotalInclTax/( (1 + $Item->getTaxPercent()/100)) );
			}
			$Item->setBaseTaxAmount($BaseTaxAmount);


			//base_hidden_tax_amount = 0
			$Item->setBaseHiddenTaxAmount(0);


			//base_row_tax calculation
			if($CalculationIncludeTax){
				$BaseRowTax = $BaseRowTotalInclTax - ($BaseRowTotalInclTax / (1 + $Item->getTaxPercent()/100) );
				$Item->setBaseRowTax($BaseRowTax);
			}
			//no base_row_tax if $CalculationIncludeTax == false


			//base_taxable_amount
			if($CalculationIncludeTax){
				$BaseTaxableAmount = $BaseRowTotalInclTax;
			}else{
				$BaseTaxableAmount = $BaseRowTotal;
			}
			$Item->setBaseTaxableAmount($BaseTaxableAmount);


			//base_discount_calculation_price
			if($ApplyDiscountOnPriceIncludeTax){
				$BaseDiscountCalculationPrice = $BasePriceInclTax;
			}else{
				$BaseDiscountCalculationPrice = $BasePrice;
			}
			$Item->setBaseDiscountCalculationPrice($BaseDiscountCalculationPrice);

		}
	}


	/**
	 * Recalculate Quote Base Prices
	 * @param Mage_Sales_Model_Quote_Address $Address
	 */
	protected function recalculateQuoteBasePrises(Mage_Sales_Model_Quote_Address $Address){

		$Store = $Address->getQuote()->getStore();

		$Items = $this->_getAddressItems($Address);

		$BaseRowTotalInclTax = 0;
		$BaseSubtotal = 0;
		$BaseDiscountAmount = 0;

		$BaseTaxAmount = 0;
		$BaseSubtotalInclTax = 0;

		foreach ($Items AS $Item) {
			/**@var $Item Mage_Sales_Model_Quote_Item */

			$Product = $Item->getProduct();

			if ($Item->isDeleted() ||  $Product->hasGlobaleProductInfo() == false || $Item->getPrice() == 0) {
				continue;
			}


			$BaseSubtotal +=  $Item->getBasePrice() * $Item->getQty();
			$BaseDiscountAmount += $Item->getBaseDiscountAmount();



			$BaseTaxAmount += $Item->getBaseTaxAmount();
			$BaseSubtotalInclTax += $Item->getBasePriceInclTax() ;
			$BaseRowTotalInclTax += $Item->getBaseRowTotalInclTax();
		}

		$Address->setBaseGrandTotal($Store->roundPrice($BaseRowTotalInclTax - $BaseDiscountAmount));

		$Address->setBaseSubtotal($Store->roundPrice($BaseSubtotal));
		$Address->setBaseSubtotalWithDiscount($Store->roundPrice($BaseSubtotal - $BaseDiscountAmount ));

		$Address->setBaseTaxAmount($Store->roundPrice($BaseTaxAmount));
		$Address->setBaseSubtotalInclTax($Store->roundPrice($BaseSubtotalInclTax));
		$Address->setBaseSubtotalTotalInclTax($Store->roundPrice($BaseSubtotalInclTax));
		$Address->setBaseDiscountAmount($Store->roundPrice((-1 * $BaseDiscountAmount)));

	}


}