<?php
use GlobalE\SDK;
use GlobalE\SDK\Models\Common;
use GlobalE\SDK\Models;

class Globale_Browsing_Model_Rewrite_SalesRule_Validator extends Mage_SalesRule_Model_Validator
{

	/**
	 * @var array Array of SimpleActions that incline fix amount
	 * and that's why needs to be converted to current currency
	 */
	private static $FixedActionsArray = array(
		Mage_SalesRule_Model_Rule::TO_FIXED_ACTION,
		Mage_SalesRule_Model_Rule::BY_FIXED_ACTION,
		Mage_SalesRule_Model_Rule::CART_FIXED_ACTION
	);


	/**
	 * @var array Array of attribute values of conditions that incline fix amount
	 * and that's why needs to be converted to current currency
	 */
	private static $ConditionAttributeArray = array(
		'base_subtotal',
		'base_row_total',
		'quote_item_price',
		'quote_item_row_total',
		'price',
	);


	/**
	 * Initialise Validator by running parent initializer and than changing rule prices according to Global-e logic
	 * @param int $websiteId
	 * @param int $customerGroupId
	 * @param string $couponCode
	 * @return $this
	 */
	public function init($websiteId, $customerGroupId, $couponCode)
	{
		parent::init($websiteId, $customerGroupId, $couponCode);

		if ( !Mage::registry('globale_user_supported')  ||  $this->hasGlobaleDiscountAmountChanged()) {
			return $this;
		}

		/**@var $RulesCollection Mage_SalesRule_Model_Resource_Rule_Collection */
		$RulesCollection = $this->_getRules();
		$ItemsArray = $RulesCollection->getItems();
		if (!empty($ItemsArray)) {
			$this->changeItemsDiscountAmount($ItemsArray);
			$this->setGlobaleDiscountAmountChanged(true);

		}
		return $this;
	}

	/**
	 * Change Amount for all prices types of each Rule Item
	 * @param array $ItemsArray
	 */
	protected function changeItemsDiscountAmount(array $ItemsArray)
	{
		foreach ($ItemsArray as $Rule) {
			/**@var $Rule Mage_SalesRule_Model_Rule */

			//convert simple actions values
			if (in_array($Rule->getSimpleAction(), self::$FixedActionsArray)) {
				$DiscountAmount = $Rule->getDiscountAmount();
				$ConvertedDiscountAmount = $this->convertAmount($DiscountAmount);
				$Rule->setDiscountAmount($ConvertedDiscountAmount);
			}

			//convert Conditions setting
			if ($Rule->hasConditionsSerialized()) {
				$ConditionsArray = unserialize($Rule->getConditionsSerialized());
				$ConvertedConditions = $this->convertConditionsSettings($ConditionsArray);
				$Rule->setConditionsSerialized(serialize($ConvertedConditions));
			}

			//convert Actions setting
			if ($Rule->hasActionsSerialized()) {
				$ActionsArray = unserialize($Rule->getActionsSerialized());
				$ConvertedActions = $this->convertConditionsSettings($ActionsArray);
				$Rule->setActionsSerialized(serialize($ConvertedActions));
			}
		}
	}


	/**
	 * Convert Conditions and Actions setting by converting value of price items
	 * @param array $Conditions
	 * @return array
	 */
	protected function convertConditionsSettings(array $Conditions)
	{

		if(!empty($Conditions['conditions'])){
			foreach ($Conditions['conditions'] AS &$SubCondition){
				$SubCondition = $this->convertConditionsSettings($SubCondition);
			}
		}
		if(in_array($Conditions['attribute'], self::$ConditionAttributeArray)){
			$Conditions['value'] = $this->convertAmount($Conditions['value']);
		}
		return $Conditions;
	}



	/**
	 * @TODO change location to one point!!!!!!! / now it's similar to Globale_Browsing_Model_Rewrite_Catalog_Layer_Filter_Price
	 * /**
	 * Convert Amount According to Global-e logic
	 * @param float $Amount
	 * @return float
	 */
	protected function convertAmount($Amount)
	{
		if (empty($Amount)) {
			return $Amount;
		}
		/**@var $GlobaleSDK SDK\SDK */
		$GlobaleSDK = Mage::registry('globale_sdk');


		// $CartAverageTaxRate was calculated in Globale_Browsing_Model_Quote_Address_Total_Preparing
		$CartAverageTaxRate = Mage::registry('globale_cart_average_tax_rate');

		$LocalVATRateType = new Common\VatRateType($CartAverageTaxRate, 'Cart Average Tax Rate', 'GlobaleAverageCartTaxRate');
		$RawPriceRequestData = $this->buildRawPriceRequestData($Amount, $LocalVATRateType);

		$PriceIncludesVAT = Globale_Browsing_Model_Product::isPriceIncludesVAT();

		//Use  $UseRounding = false, $IsDiscount = true
		$SDKResponse = $GlobaleSDK->Browsing()->GetCalculatedRawPrice(array($RawPriceRequestData), $PriceIncludesVAT, false, true);

		if ($SDKResponse->getSuccess()) {
			/**@var $SDKResponse  Common\ProductResponseData[] */
			$Amount = $SDKResponse->getData()[0]->getSalePrice();
		}
		return $Amount;
	}


	/**
	 * Build RawPriceRequestData object for "Amount" item
	 * @param float $Price
	 * @param Common\VatRateType $LocalVATRateType
	 * @return Common\Request\RawPriceRequestData $ProductRequestData
	 */
	protected function buildRawPriceRequestData($Price, $LocalVATRateType)
	{

		/**@var $globaleSDK SDK\SDK */
		$globaleSDK = Mage::registry('globale_sdk');
		$GlobalEVATRateType = $globaleSDK::$MerchantVatRateType;

		$RawPriceRequestData = new Common\Request\RawPriceRequestData();
		$RawPriceRequestData->setOriginalListPrice($Price);
		$RawPriceRequestData->setOriginalSalePrice($Price);
		$RawPriceRequestData->setIsFixedPrice(false);
		$RawPriceRequestData->setVATRateType($GlobalEVATRateType);
		$RawPriceRequestData->setLocalVATRateType($LocalVATRateType);

		return $RawPriceRequestData;
	}

	/**
	 * Change country id  from Global-e settings in the checked coupon address object
	 *
	 * @param Mage_SalesRule_Model_Rule $rule
	 * @param Mage_Sales_Model_Quote_Address $address
	 * @return bool
	 */
	protected function _canProcessRule($rule, $address)
	{
		//Set CountryId according to GE settings
		if(Mage::registry('globale_user_supported')){

			/** @var  GlobalE\SDK\SDK $GlobaleSDK */
			$GlobaleSDK = Mage::registry('globale_sdk');
			$CountryResponse = $GlobaleSDK->Browsing()->GetCountries();

			if($CountryResponse->getSuccess()){
				/**@var $CountryObject Models\Country */
				$CountryObject = $CountryResponse->getData();
				$CountryISO = $CountryObject->getCountry()->getCode();

				$address->setCountryId($CountryISO);
			}
		}

		return parent::_canProcessRule($rule, $address);
	}


}