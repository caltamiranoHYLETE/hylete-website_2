<?php

use GlobalE\SDK;
use GlobalE\SDK\Models\Common;

class Globale_Browsing_Model_Products_Types_Bundle extends Globale_Browsing_Model_Products_Abstract
{


	/**
	 * Change Additional Product Prices for Bundle product type
	 * @param Mage_Catalog_Model_Product $Product
	 */
	public function changeAdditionalProductPrices(Mage_Catalog_Model_Product $Product)
	{
		$this->changeSelectionsPrice($Product);
	}

	######################## SelectionsPrice ##############################

	/**
	 * Convert Selection Price for Fix Bundle (convert fix value from base currency according to Global-E logic)
	 * @param Mage_Catalog_Model_Product $Product
	 */
	protected function changeSelectionsPrice(Mage_Catalog_Model_Product $Product)
	{
		//if SelectionPrice wasn't changed
		if ($Product->hasGlobaleSelectionPriceChanged()) {
			return;
		}

		//If Fixed Price Bundle & SelectionPrice wasn't changed  &&  has Options :
		if ($Product->getPriceType() == Mage_Bundle_Model_Product_Price::PRICE_TYPE_FIXED
			&& $Product->hasGlobaleSelectionPriceChanged() == false
			&& $Product->getTypeInstance(true)->hasOptions($Product)
		) {
			$SelectionCollection = $this->loadSelectionCollection($Product);
			$this->updateSelectionPrices($SelectionCollection, $Product);
		}
	}


	/**
	 * Load SelectionCollection
	 * @param Mage_Catalog_Model_Product $Product
	 * @return Mage_Bundle_Model_Resource_Selection_Collection
	 */
	protected function loadSelectionCollection(Mage_Catalog_Model_Product $Product)
	{
		/**@var $SelectionCollection  Mage_Bundle_Model_Resource_Selection_Collection */
		$SelectionCollection = $Product->getTypeInstance(true)->getSelectionsCollection(
			$Product->getTypeInstance(true)->getOptionsIds($Product),
			$Product
		);
		return $SelectionCollection;
	}


	/**
	 * Update prices of input SelectionCollection and update product
	 * @param Mage_Bundle_Model_Resource_Selection_Collection $SelectionCollection
	 * @param Mage_Catalog_Model_Product $Product
	 */
	protected function updateSelectionPrices(Mage_Bundle_Model_Resource_Selection_Collection $SelectionCollection, Mage_Catalog_Model_Product $Product)
	{
		//if no items
		if ($SelectionCollection->getPricesCount() == 0) {
			return;
		}
		/**@var $GlobaleSDK SDK\SDK */
		$GlobaleSDK = Mage::registry('globale_sdk');

		$LocalVATRateType =  Mage::getModel('globale_base/product')->buildLocalVATRateType($Product);
		$PriceIncludesVAT = Globale_Browsing_Model_Product::isPriceIncludesVAT();

		$SelectionPricesRequestData = $this->buildSelectionPricesRequest($SelectionCollection, $LocalVATRateType);

		if (empty($SelectionPricesRequestData)) {
			return;
		}
		$SelectionPricesResponse = $GlobaleSDK->Browsing()->GetCalculatedRawPrice($SelectionPricesRequestData, $PriceIncludesVAT, true);

		if ($SelectionPricesResponse->getSuccess()) {
			/**@var $SelectionPricesSDKResult  Common\RawPriceResponseData[] */
			$SelectionPricesSDKResult = $SelectionPricesResponse->getData();
			$this->changeSelectionPrices($SelectionPricesSDKResult, $SelectionCollection, $Product);
		}
	}


	/**
	 * Build Array of RawPriceRequestData for SelectionPrices that need to be changed
	 * @param Mage_Bundle_Model_Resource_Selection_Collection $SelectionCollection
	 * @param Common\VatRateType $LocalVATRateType
	 * @return array
	 */
	protected function buildSelectionPricesRequest(Mage_Bundle_Model_Resource_Selection_Collection $SelectionCollection, Common\VatRateType $LocalVATRateType)
	{
		$SelectionPricesRequest = array();

		foreach ($SelectionCollection as $SelectionId => $ProductItem) {
			/**@var $ProductItem Mage_Catalog_Model_Product */
			//if Price Type is Fixed and Price > 0
			if ($ProductItem->getSelectionPriceType() == 0 && $ProductItem->getSelectionPriceValue() > 0) {
				$RequestItem = $this->buildRequestItem($ProductItem->getSelectionPriceValue(), $LocalVATRateType);
				$SelectionPricesRequest[$SelectionId] = $RequestItem;
			}
		}
		return $SelectionPricesRequest;
	}

	/**
	 * Build RawPriceRequestData per item for SDK
	 * @param float $Price
	 * @param Common\VatRateType $LocalVATRateType
	 * @return Common\Request\RawPriceRequestData
	 */
	protected function buildRequestItem($Price, $LocalVATRateType)
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
	 * Change SelectionPrices according to SDK response array
	 * @param Common\RawPriceResponseData[] $SelectionPricesSDKResult
	 * @param Mage_Bundle_Model_Resource_Selection_Collection $SelectionCollection
	 * @param $Product
	 */
	protected function changeSelectionPrices(array $SelectionPricesSDKResult, $SelectionCollection, $Product)
	{
		if (empty($SelectionPricesSDKResult)) {
			return;
		}

		foreach ($SelectionPricesSDKResult AS $ItemId => $ItemPriceResponse) {
			/**@var $ItemPriceResponse Common\RawPriceResponseData */
			$Price = $ItemPriceResponse->getSalePrice();

			$Item = $SelectionCollection->getItemById($ItemId);
			$Item->setSelectionPriceValue($Price);
		}

		//@TODO check id we need it + how to save data for card
		//$Product->setSelectionsCollection($SelectionCollection);
		$Product->setGlobaleSelectionPriceChanged(true);
	}
	######################################################

}