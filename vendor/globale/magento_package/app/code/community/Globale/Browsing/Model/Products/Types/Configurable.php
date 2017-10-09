<?php
use GlobalE\SDK;
use GlobalE\SDK\Models\Common;

class Globale_Browsing_Model_Products_Types_Configurable extends Globale_Browsing_Model_Products_Abstract {

	const DELIMITER = '|';


	/**
	 * Change Additional Product Prices for Configurable product type
	 * @param Mage_Catalog_Model_Product $Product
	 */
	public function changeAdditionalProductPrices(Mage_Catalog_Model_Product $Product)
	{
		$this->updateSpecialAttributePrice($Product);
	}


	/**
	 * Update Price of Special Attribute according to Global-e logic
	 * @param Mage_Catalog_Model_Product $Product
	 */
	protected function updateSpecialAttributePrice(Mage_Catalog_Model_Product $Product){

		//if Attribute is already changed
		if($Product->getGlobaleConfigurableAttributesChanged()){
			return;
		}

		/**@var $GlobaleSDK SDK\SDK */
		$GlobaleSDK = Mage::registry('globale_sdk');
		$SpecialAttributeRequestData = $this->buildSpecialAttributesRequest($Product);

		if(empty($SpecialAttributeRequestData)){
			return;
		}
		$PriceIncludesVAT = Globale_Browsing_Model_Product::isPriceIncludesVAT();

		$SpecialAttributeResponse = $GlobaleSDK->Browsing()->GetCalculatedRawPrice($SpecialAttributeRequestData,$PriceIncludesVAT,true);

		if ($SpecialAttributeResponse->getSuccess()) {
			/**@var $SpecialAttributeSDKResult  Common\RawPriceResponseData[] */
			$SpecialAttributeSDKResult = $SpecialAttributeResponse->getData();
			$this->changeSpecialAttributePrices($SpecialAttributeSDKResult,$Product);
		}
	}


	/**
	 * Build array of RawPriceRequestData objects per each attribute that need to update the price
	 * @param Mage_Catalog_Model_Product $Product
	 * @return array|null
	 */
	protected function buildSpecialAttributesRequest(Mage_Catalog_Model_Product $Product){

		$SpecialAttributeRequest = array();

		/**@var $Attributes Mage_Catalog_Model_Resource_Product_Type_Configurable_Attribute_Collection */
		$Attributes = $Product->getTypeInstance(true)->getConfigurableAttributes($Product);

		$Items = $Attributes->getItems();

		if (empty($Items)) {
			return null;
		}

		$LocalVATRateType = Mage::getModel('globale_base/product')->buildLocalVATRateType($Product);

		foreach ($Items as $AttributeId => $Attribute) {
			/**@var $Attribute Mage_Catalog_Model_Product_Type_Configurable_Attribute */
			$AttributePrices = $Attribute->getPrices();

			if (empty($AttributePrices)) {
				return null;
			}

			foreach ($AttributePrices as $PriceKey => $AttributePrice) {
				if ($AttributePrice['pricing_value'] && $AttributePrice['is_percent'] == 0) {

					$AttPriceSku = implode(self::DELIMITER, array($Product->getSku(), $AttributeId ,$PriceKey));
					$SpecialAttributeRequest[$AttPriceSku] = $this->buildAttributeRequestData($AttributePrice['pricing_value'],$LocalVATRateType);
				}
			}
		}

		return $SpecialAttributeRequest;
	}

	/**
	 * Build RawPriceRequestData object for Special Attribute
	 * @param float $Price
	 * @param Common\VatRateType $LocalVATRateType
	 * @return Common\Request\RawPriceRequestData $RawPriceRequestData
	 */
	protected function buildAttributeRequestData($Price,$LocalVATRateType){

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
	 * Change Special Attribute Prices according to $SDKResult
	 * @param Common\RawPriceResponseData[] $SDKResult
	 * @param Mage_Catalog_Model_Product $Product
	 */
	protected function changeSpecialAttributePrices(array $SDKResult,Mage_Catalog_Model_Product $Product){

		/**@var $Attributes Mage_Catalog_Model_Resource_Product_Type_Configurable_Attribute_Collection */
		$Attributes = $Product->getTypeInstance(true)->getConfigurableAttributes($Product);


		foreach ($SDKResult AS $AttPriceSku => $AttributeRawPriceItem ){

			$AttributeMapArray = explode(self::DELIMITER,$AttPriceSku) ;
			$AttributeId = $AttributeMapArray[1];
			$PriceKey = $AttributeMapArray[2];

			$Item = $Attributes->getItemById($AttributeId);
			$ItemPrices = $Item->getPrices();
			$ItemPrices[$PriceKey]['globale_special_price_info'] = $AttributeRawPriceItem;

			$ItemPrices[$PriceKey]['pricing_value'] = $AttributeRawPriceItem->getSalePrice();

			$Item->setPrices($ItemPrices);

		}

		//save changed Attributes to Product
		$Product->set_cacheInstanceConfigurableAttributes($Attributes);
		$Product->setGlobaleConfigurableAttributesChanged(true);
	}


}