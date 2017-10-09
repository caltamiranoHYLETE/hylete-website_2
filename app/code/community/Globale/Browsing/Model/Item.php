<?php

use GlobalE\SDK\Models\Common;

class Globale_Browsing_Model_Item extends Mage_Core_Model_Abstract
{


	/**
	 * Calculate Additional Item Amount from selected Options/Associated Products
	 * @param Mage_Sales_Model_Quote_Item $Item
	 * @param $IsBasePrice - Base on BasePrice or Price
	 * @param boolean $IsSalePriceCase - If true calculation base on OriginalSalePrice, otherwise OriginalListPrice
	 * @param boolean $IsSaleBeforeRounding - If it's SellPriceBeforeRounding (no matter what $IsSalePriceCase $IsBasePrice flags)
	 * @return float
	 */
	public function calculateAdditionalItemAmount(Mage_Sales_Model_Quote_Item $Item, $IsBasePrice, $IsSalePriceCase = false, $IsSaleBeforeRounding = false)
	{
		$ProductTypeId = $Item->getProduct()->getTypeId();

		$Amount = 0;

		//If $IsSaleBeforeRounding = true OVERRIDE $IsSalePriceCase to true
//		if($IsSaleBeforeRounding){
//			$IsSalePriceCase = true;
//		}

		$Amount += $this->calculateSimpleOptionsAdditionalAmount($Item, $IsBasePrice, $IsSalePriceCase, $IsSaleBeforeRounding);

		if($ProductTypeId == Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE ){
			$Amount += $this->calculateConfigurableOptionAdditionalAmount($Item,$IsBasePrice, $IsSalePriceCase, $IsSaleBeforeRounding);
		}

		return $Amount;
	}


	/**
	 * Return Price from GlobaleProductInfo according to $IsBasePrice and $IsSalePriceCase flags
	 * @param Common\ItemDataResponseInterface $GlobaleProductInfo
	 * @param Mage_Sales_Model_Quote_Item $Item
	 * @param $IsBasePrice - Base on BasePrice or Price
	 * @param boolean $IsSalePriceCase - If true calculation base on OriginalSalePrice, otherwise OriginalListPrice
	 * @param boolean $IsSaleBeforeRounding - If it's SellPriceBeforeRounding (no matter what $IsSalePriceCase $IsBasePrice flags)
	 * @return float
	 */
	protected function chooseItemInfoPrice(Common\ItemDataResponseInterface $GlobaleProductInfo, $IsBasePrice, $IsSalePriceCase, $IsSaleBeforeRounding = false) {
		$Price = 0;
		switch (true){
			case ($IsSaleBeforeRounding):
				$Price = $GlobaleProductInfo->getSalePriceBeforeRounding();
				break;
			case ($IsSalePriceCase && $IsBasePrice ):
				$Price = $GlobaleProductInfo->getOriginalSalePrice();
				break;
			case(!$IsSalePriceCase && $IsBasePrice):
				$Price = $GlobaleProductInfo->getOriginalListPrice();
				break;
			case($IsSalePriceCase && !$IsBasePrice):
				$Price = $GlobaleProductInfo->getSalePrice();
				break;
			case(!$IsSalePriceCase && !$IsBasePrice):
				$Price = $GlobaleProductInfo->getListPrice();
				break;
		}
		return $Price;
	}


	/**
	 * Calculate Additional Amount that came from Configurable Selected Attributes IN BASE CURRENCY
	 * @param Mage_Sales_Model_Quote_Item $Item
	 * @param $IsBasePrice - Base on BasePrice or Price
	 * @param boolean $IsSalePriceCase - If true calculation base on OriginalSalePrice, otherwise OriginalListPrice
	 * @param boolean $IsSaleBeforeRounding - If it's SellPriceBeforeRounding (no matter what $IsSalePriceCase $IsBasePrice flags)
	 * @return float
	 */
	protected function calculateConfigurableOptionAdditionalAmount(Mage_Sales_Model_Quote_Item $Item, $IsBasePrice, $IsSalePriceCase, $IsSaleBeforeRounding){
		$AdditionalAmount = 0;

		//check if we have GlobaleProductInfo => OriginalListPrice, otherwise we don't have basePrice
		if(!$Item->getProduct()->hasGlobaleProductInfo()){
			return 0;
		}

		/**@var $GlobaleProductInfo Common\ProductResponseData */
		$GlobaleProductInfo = $Item->getProduct()->getGlobaleProductInfo();

		$BasePrice = $this->chooseItemInfoPrice($GlobaleProductInfo, $IsBasePrice, $IsSalePriceCase, $IsSaleBeforeRounding);


		$SelectedAttributes = Mage::getModel('globale_base/product')->loadSelectedAttributes($Item->getProduct());

		if(empty($SelectedAttributes) || $BasePrice == 0 ){
			return 0;
		}

			/**@var $Attributes Mage_Catalog_Model_Resource_Product_Type_Configurable_Attribute_Collection */
		$Attributes = $Item->getProduct()->getTypeInstance(true)->getConfigurableAttributes($Item->getProduct());

		foreach ($Attributes as $Attribute){
			//if attribute label do not selected
			if(!in_array($Attribute->getLabel(), array_keys($SelectedAttributes)) ){
				continue;
			}
			$SelectedLabel = $SelectedAttributes[$Attribute->getLabel()];

			$Prices = $Attribute->getPrices();
			foreach ($Prices AS $PriceLabel){
				if($PriceLabel['label'] == $SelectedLabel && $PriceLabel['pricing_value'] ){

					if($PriceLabel['is_percent']){
						$AdditionalAmount += $BasePrice * $PriceLabel['pricing_value'] /100;
					}else{
						/**@var $GlobaleSpecialPriceInfo  Common\RawPriceResponseData */
						$GlobaleSpecialPriceInfo = $PriceLabel['globale_special_price_info'];

						$AdditionalPrice = $this->chooseItemInfoPrice($GlobaleSpecialPriceInfo, $IsBasePrice, $IsSalePriceCase, $IsSaleBeforeRounding);
						$AdditionalAmount += $AdditionalPrice;
					}
				}
			}
		}

		return $AdditionalAmount;
	}

	/**
	 * Calculate Additional Amount from Selected options for Item IN BASE CURRENCY
	 * @param Mage_Sales_Model_Quote_Item $Item
	 * @param $IsBasePrice - Base on BasePrice or Price
	 * @param boolean $IsSalePriceCase - If true calculation base on OriginalSalePrice, otherwise OriginalListPrice
	 * @param boolean $IsSaleBeforeRounding - If it's SellPriceBeforeRounding (no matter what $IsSalePriceCase $IsBasePrice flags)
	 * @return float
	 */
	protected function calculateSimpleOptionsAdditionalAmount(Mage_Sales_Model_Quote_Item $Item, $IsBasePrice, $IsSalePriceCase, $IsSaleBeforeRounding){

		$AdditionalAmount = 0;

		//check if we have GlobaleProductInfo => OriginalListPrice, otherwise we don't have basePrice
		if(!$Item->getProduct()->hasGlobaleProductInfo()){
			return 0;
		}

		/**@var $GlobaleProductInfo Common\ProductResponseData */
		$GlobaleProductInfo = $Item->getProduct()->getGlobaleProductInfo();

		$BasePrice = $this->chooseItemInfoPrice($GlobaleProductInfo,$IsBasePrice, $IsSalePriceCase,$IsSaleBeforeRounding );

		$SelectedOptions = $this->loadSelectedOptions($Item);
		if(empty($SelectedOptions) || $BasePrice == 0){
			return $AdditionalAmount;
		}
		$Options = $Item->getProduct()->getOptions();

		foreach ($SelectedOptions AS $Option_id => $Value){
			/**@var $Option Mage_Catalog_Model_Product_Option */
			$Option = $Options[$Option_id];

			$OptionValues = $Option->getValues();
			//Option without values -> amount in data
			if(empty($OptionValues)){
				if($Option->getPriceType() == 'by_char'){
				    $Option->setByCharValue($Value);
                }
			    $AdditionalAmount += $this->calculateOptionAdditionalAmount($Option, $BasePrice,$IsBasePrice, $IsSalePriceCase, $IsSaleBeforeRounding);

			}else{
				// The Option's amounts are inside of Option Values
				$AppliedValues = explode(',',$Value);
				foreach ($AppliedValues as $AppliedValue){

					$OptionValue = $OptionValues[$AppliedValue];
					$AdditionalAmount += $this->calculateOptionAdditionalAmount($OptionValue, $BasePrice, $IsBasePrice, $IsSalePriceCase, $IsSaleBeforeRounding);
				}
			}
		}
		return $AdditionalAmount;
	}


	/**
	 * Calculate Additional Amount from single option / option value
	 * @param Mage_Catalog_Model_Product_Option | Mage_Catalog_Model_Product_Option_Value $OptionItem
	 * @param $BasePrice
	 * @param $IsBasePrice - Base on BasePrice or Price
	 * @param boolean $IsSalePriceCase - If true calculation base on OriginalSalePrice, otherwise OriginalListPrice
	 * @param boolean $IsSaleBeforeRounding - If it's SellPriceBeforeRounding (no matter what $IsSalePriceCase $IsBasePrice flags)
	 * @return float
	 */
	protected function calculateOptionAdditionalAmount($OptionItem, $BasePrice, $IsBasePrice, $IsSalePriceCase, $IsSaleBeforeRounding){
		if ($OptionItem->getPriceType() == 'percent') {
			$AdditionalAmount = $BasePrice * $OptionItem->_getData('price') / 100;
		} else {
			/**@var $GlobaleOptionInfo Common\RawPriceResponseData */
			$GlobaleOptionInfo = $OptionItem->getGlobaleOptionInfo();

            $AdditionalAmount = $this->chooseItemInfoPrice($GlobaleOptionInfo, $IsBasePrice, $IsSalePriceCase, $IsSaleBeforeRounding);
            //addition to support personalisation items
            if(!empty($AdditionalAmount) && $OptionItem->hasByCharValue()){
                $Qty = strlen($OptionItem->getByCharValue());
                $AdditionalAmount = $AdditionalAmount * $Qty;
            }
		}
		return $AdditionalAmount;
	}


	/**
	 * Load  Selected Options in required structure
	 * @param Mage_Sales_Model_Quote_Item $Item
	 * @return array
	 */
	protected function loadSelectedOptions(Mage_Sales_Model_Quote_Item $Item){

		$Product = $Item->getProduct();
		$SelectedOptions = array();

		$OptionIds = $Item->getOptionByCode('option_ids');
		if ($OptionIds) {
			foreach (explode(',', $OptionIds->getValue()) as $OptionId) {
				$Option = $Product->getOptionById($OptionId);

				if ($Option) {
					$ItemOption = $Item->getOptionByCode('option_' . $Option->getId());

					$SelectedOptions[$Option->getId()] = $ItemOption->getValue();
				}
			}
		}

		return $SelectedOptions;
	}







	/**
	 * Change Prices when add to quote
	 * Event ==> sales_quote_collect_totals_before
	 * @param Varien_Event_Observer $observer
	 */
	public function updateQuoteTotals(Varien_Event_Observer $observer){
		if(Mage::registry('globale_user_supported')){
			/**@var $Product Mage_Catalog_Model_Product */
			$Quote = $observer->getQuote();
			$Quote->setIsSuperMode(true);

			//@TODO change to other event
		}
	}


}