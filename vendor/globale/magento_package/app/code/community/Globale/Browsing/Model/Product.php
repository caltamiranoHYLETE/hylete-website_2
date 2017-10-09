<?php

use GlobalE\SDK;
use GlobalE\SDK\Models\Common;
use GlobalE\SDK\Models\Common\Request;
use GlobalE\SDK\Core;

/**
 * Manipulate product price conversion
 * Class Globale_Browsing_Model_Conversion
 */
class Globale_Browsing_Model_Product extends Mage_Core_Model_Abstract
{
	const DELIMITER = '|';

	/**
	 * Convert product prices by using the Global-e SDK for single product
	 * Observer => Product::updateProductPrices
	 * Events => catalog_product_load_after, catalog_controller_product_view, sales_quote_item_set_product
	 *   sales_quote_item_set_product - called from <global> that's why we need to check if scope is NOT admin
	 * @param $observer
	 * @access public
	 */

	public function updateProductPrices(Varien_Event_Observer $observer)
	{
		if(Mage::registry('globale_user_supported') && !Mage::app()->getStore()->isAdmin() ){
			/**@var $Product Mage_Catalog_Model_Product */
			$Product = $observer->getProduct();
			$this->updateProductsPrices(array($Product));
		}
	}


	/**
	 * Convert product prices by using the Global-e SDK for Product Collection
	 * Observer => Product::updateCollectionProductsPrices
	 * Events => catalog_product_collection_load_after
	 * @param Varien_Event_Observer $observer
	 */
	public function updateCollectionProductsPrices(Varien_Event_Observer $observer)
	{
		if(Mage::registry('globale_user_supported')){
			$ProductsCollection = $observer->getEvent()->getCollection();
			$this->updateProductsPrices($ProductsCollection);
		}
	}


	/**
	 * Update products array or collection prices
	 * @param Iterator | array $Products
	 */
	protected function updateProductsPrices($Products){
		/**@var $GlobaleSDK SDK\SDK */
		$GlobaleSDK = Mage::registry('globale_sdk');
		$ProductRequestData = $this->buildProductsRequest($Products);
		//if no product with prices
		if(!empty($ProductRequestData)){
			$PriceIncludesVAT = self::isPriceIncludesVAT();

			$ProductResponse = $GlobaleSDK->Browsing()->GetProductsInformation($ProductRequestData,$PriceIncludesVAT);
			if ($ProductResponse->getSuccess()) {
				/**@var $ProductsSDKResult  Common\ProductResponseData[] */
				$ProductsSDKResult = $ProductResponse->getData();
				$this->changeProductsPrices($ProductsSDKResult,$Products);
			}
		}
		$this->updateOptionsPrice($Products);
		$this->changeAdditionalProductPrices($Products);
	}


	protected function changeAdditionalProductPrices($Products){
		foreach ($Products as $Product){
			/**@var $Product Mage_Catalog_Model_Product */
			$ProductTypeModel = $this->productTypeFactory($Product->getTypeId());
			$ProductTypeModel->changeAdditionalProductPrices($Product);
		}

}

	/**
	 * Load Magento setting of price include vat
	 * @return bool
	 */
	public static function isPriceIncludesVAT(){
		$IsPriceIncludesVAT = (bool)Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX, null);
		return $IsPriceIncludesVAT;
	}

	/**
	 * Prepare request object of Products data for send to SDK
	 * @param Mage_Catalog_Model_Product[] $Products
	 * @return Request\ProductRequestData[]
	 */
	protected function buildProductsRequest($Products)
	{
		$ProductsRequestData = array();

		Mage::dispatchEvent('globale_build_products_request_before', array('products' => $Products));

		foreach ($Products AS $Product){

            // remove tax calculation for merchants out of euro
            $IgnoreProductTaxClass = Core\Settings::get('AppSettings.ServerSettings.IgnoreProductTaxClass.Value');
            if($IgnoreProductTaxClass){
                $Product->setTaxClassId(0);
            }

			/**@var $Product Mage_Catalog_Model_Product  **/
			//if Product doesn't have price and GlobalePrice - add product to $GlobaleProductInfo array
			if($Product->hasPrice() && !$Product->hasGlobaleProductInfo()){
				$ProductRequestData = $this->buildProductRequestData($Product);
				$ProductsRequestData[$Product->getSku()] = $ProductRequestData;

			}
		}
		return $ProductsRequestData;
	}


	/**
	 * Return class for additional price changes per product TypeId
	 * @param $type_id
	 * @return false|Globale_Browsing_Model_Products_Abstract
	 */
	protected function productTypeFactory($type_id){
		switch ($type_id){

			case Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL:
				/**@var $ProductTypeModel Globale_Browsing_Model_Products_Types_Virtual */
				$ProductTypeModel = Mage::getModel('globale_browsing/products_types_virtual');
				break;

			case Mage_Catalog_Model_Product_Type::TYPE_BUNDLE:
				/**@var $ProductTypeModel Globale_Browsing_Model_Products_Types_Bundle */
				$ProductTypeModel = Mage::getModel('globale_browsing/products_types_bundle');
				break;

			case Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE:
				/**@var $ProductTypeModel Globale_Browsing_Model_Products_Types_Configurable */
				$ProductTypeModel = Mage::getModel('globale_browsing/products_types_configurable');
				break;

			case Mage_Catalog_Model_Product_Type::TYPE_SIMPLE:
			default:
			/**@var $ProductTypeModel Globale_Browsing_Model_Products_Types_Simple */
			$ProductTypeModel = Mage::getModel('globale_browsing/products_types_simple');
			break;
			//@TODO add for all possible product types
		}
		return $ProductTypeModel;
	}


	/**
	 * Build ProductRequestData item of single product prices
	 * @param Mage_Catalog_Model_Product $Product
	 * @return Request\ProductRequestData
	 */
	protected function buildProductRequestData(Mage_Catalog_Model_Product $Product){
		/**@var $Product Mage_Catalog_Model_Product */
		$ProductRequestData = new Request\ProductRequestData();

        Mage::dispatchEvent(
            'globale_build_product_request_data',
            array('product' => $Product, 'product_request_data' => $ProductRequestData)
        );

		$OriginalListPrice = $Product->getPrice();

		if($OriginalListPrice == 0 && $Product->getMaxPrice()  ){
			$OriginalListPrice = $Product->getMaxPrice() ;
		}

		$OriginalSalePrice = $Product->getFinalPrice();

		if($OriginalSalePrice == 0 && $Product->getMinimalPrice() ){
			$OriginalSalePrice = $Product->getMinimalPrice();
		}


		/**@var $globaleSDK SDK\SDK */
		$globaleSDK = Mage::registry('globale_sdk');

		$GlobalEVATRateType = $globaleSDK::$MerchantVatRateType;

		$LocalVATRateType = Mage::getModel('globale_base/product')->buildLocalVATRateType($Product);

		$ProductRequestData->setProductCode($Product->getSku());
		$ProductRequestData->setOriginalListPrice($OriginalListPrice);
		$ProductRequestData->setOriginalSalePrice($OriginalSalePrice);
		$ProductRequestData->setIsFixedPrice($this->checkIfIsFixedPrice($Product));
		$ProductRequestData->setVATRateType($GlobalEVATRateType);
		$ProductRequestData->setLocalVATRateType($LocalVATRateType);

		return $ProductRequestData;
	}


	/**
	 * Check if product mode is fix price
	 * @param Mage_Catalog_Model_Product $Product
	 * @return boolean
	 */
	protected function checkIfIsFixedPrice(Mage_Catalog_Model_Product $Product){

        if($Product->hasGlobaleFixedPrices()){
            return true;
        }

		return Mage::helper('globale_base')->useCatalogPriceAsFixedPrice();
	}

	/**
	 * Change Prices of Product objects according to Global-e logic
	 * @param Common\ProductResponseData[] $ProductsSDKResult
	 * @param Mage_Catalog_Model_Product[] $Products
	 */
	protected function changeProductsPrices(array $ProductsSDKResult, $Products){
		foreach ($Products AS $Product){
			/**@var $Product Mage_Catalog_Model_Product */

			//Change Product prices according to $ProductsSDKResult array
			if($Product->hasPrice() && !$Product->hasGlobaleProductInfo() && isset($ProductsSDKResult[$Product->getSku()])){
				$this->changeProductPrices($Product,$ProductsSDKResult[$Product->getSku()]);
			}
		}
	}

	/**
	 * Change prices of product item
	 * @param Mage_Catalog_Model_Product $Product
	 * @param Common\ProductResponseData $ProductResponseData
	 */
	protected function changeProductPrices(Mage_Catalog_Model_Product $Product, Common\ProductResponseData $ProductResponseData ){
		/**@var $Product Mage_Catalog_Model_Product **/
		$Product->setGlobaleProductInfo($ProductResponseData);

		//save Original prices to Product obj
		$Product->setOriginalListPrice($Product->getPrice());
		if($Product->getFinalPrice() != $Product->getPrice()){
			$Product->setOriginalSalePrice($Product->getFinalPrice());
		}

		//if Special Price
		if($Product->getSpecialPrice() == $Product->getFinalPrice()){
			$Product->setSpecialPrice($ProductResponseData->getSalePrice());
		}


		if ($Product->getPrice() != 0 ){
			$Product->setPrice($ProductResponseData->getListPrice());
		}

		if ($Product->getFinalPrice() != 0 ) {
			$Product->setFinalPrice($ProductResponseData->getSalePrice());
		}

		$Product->setMaxPrice($ProductResponseData->getListPrice());
		$Product->setMinPrice($ProductResponseData->getSalePrice());
		$Product->setMinimalPrice($ProductResponseData->getSalePrice());
	}


	######################## Options care #################################

	/**
	 * Update Prices for Options
	 * @param Mage_Catalog_Model_Product[] | Iterator
	 */
	protected function updateOptionsPrice($Products){
		foreach ($Products as $Product){
			$this->updateOptionPrice($Product);
		}

	}

	/**
	 * Update Option prices of the product according to Global-e logic
	 * @param Mage_Catalog_Model_Product $Product
	 */
	protected function updateOptionPrice(Mage_Catalog_Model_Product $Product)
	{
		//If options was updates - return
		if ($Product->hasGlobaleOptionChanged() && $Product->getGlobaleOptionChanged() == true) {
			return;
		}

		/**@var $GlobaleSDK SDK\SDK */
		$GlobaleSDK = Mage::registry('globale_sdk');

		$OptionsRequestData = $this->buildOptionsRequestData($Product);
		if (!empty($OptionsRequestData)) {

			$PriceIncludesVAT = self::isPriceIncludesVAT();

			$OptionsResponse = $GlobaleSDK->Browsing()->GetCalculatedRawPrice($OptionsRequestData, $PriceIncludesVAT, true);

			if ($OptionsResponse->getSuccess()) {
				/**@var $OptionsSDKResult  Common\RawPriceResponseData[] */
				$OptionsSDKResult = $OptionsResponse->getData();

				$ProductOptionsMapping = $this->buildOptionsMapping($Product);
				if (!empty($ProductOptionsMapping)) {
					$this->changeOptionsProductPrices($Product, $ProductOptionsMapping, $OptionsSDKResult);
				}
			}
		}
	}


	/**
	 * @desc  Build  "Options" array that include Request\ProductRequestData of each Option
	 * @param Mage_Catalog_Model_Product $Product
	 * @return Common\Request\ProductRequestData[]
	 */
	protected function buildOptionsRequestData(Mage_Catalog_Model_Product $Product){

		$Options = $Product->getOptions();
		if(empty($Options)){
			return array();
		}
		$Product->setGlobaleOptionChanged(false);
		$OptionsRequestData = array();

		$LocalVATRateType = Mage::getModel('globale_base/product')->buildLocalVATRateType($Product);

		foreach ($Options as $OptionId => $Option){
			/**@var $Option Mage_Catalog_Model_Product_Option */
			$OptionValues = $Option->getValues();

			if(!empty($OptionValues)){
				//add options from values
				$ProductOptionRequestDataFromValues = $this->buildProductOptionRequestDataFromValues($OptionValues,$OptionId, $Product->getSku(), $LocalVATRateType);
				$OptionsRequestData = array_merge($OptionsRequestData,$ProductOptionRequestDataFromValues);
			}

			if(($Option->getPriceType() == 'fixed' || $Option->getPriceType() == 'by_char') && $Option->getData('price') ){
				$OptionSku = implode(self::DELIMITER, array($Product->getSku(), $OptionId));
				$ProductRequestData[$OptionSku]  = $this->buildRawPriceRequestData($Option->getData('price'),$LocalVATRateType);
				$OptionsRequestData = array_merge($OptionsRequestData,$ProductRequestData);
			}

		}
		return $OptionsRequestData;
	}


	/**
	 * Add "Option" of Option that include values
	 * @param Mage_Catalog_Model_Product_Option_Value[] $OptionValues
	 * @param int $OptionId
	 * @param string $Sku
	 * @param Common\VatRateType $LocalVATRateType
	 * @return Common\Request\ProductRequestData[] $OptionsRequestData
	 */
	protected function buildProductOptionRequestDataFromValues(array $OptionValues, $OptionId, $Sku, $LocalVATRateType){

		$OptionsRequestData = array();
		foreach ($OptionValues AS $OptionValueId => $OptionValue){

			if(($OptionValue->getPriceType() == 'fixed' || $OptionValue->getPriceType() == 'by_char') && $OptionValue->getPrice() ){
				$OptionSku = implode(self::DELIMITER, array($Sku, $OptionId, $OptionValueId));
				$ProductRequestData = $this->buildRawPriceRequestData($OptionValue->getPrice(),$LocalVATRateType);
				$OptionsRequestData[$OptionSku] = $ProductRequestData;
			}
		}
		return $OptionsRequestData;
	}


	/**
	 * Add "Option" of 'non value' Option
	 *
	 * @param Common\VatRateType $LocalVATRateType
	 * @param float $Price
	 * @return Common\Request\RawPriceRequestData $RawPriceRequestData
	 */
	protected function addProductOptionsFromData($LocalVATRateType,$Price){

		$RawPriceRequestData = $this->buildRawPriceRequestData($Price,$LocalVATRateType);
		return $RawPriceRequestData;
	}

	/**
	 * Build RawPriceRequestData object for "Option" item
	 * @param float $Price
	 * @param Common\VatRateType $LocalVATRateType
	 * @return Common\Request\RawPriceRequestData $ProductRequestData
	 */
	protected function buildRawPriceRequestData($Price, $LocalVATRateType){

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
	 * Build Mapping of "Option Product" of the Product that suppose to Return from SDK
	 * @param Mage_Catalog_Model_Product $Product
	 * @return array
	 */
	protected function buildOptionsMapping(Mage_Catalog_Model_Product $Product){
		$ProductOptionsMapping = array();

		$Options = $Product->getOptions();
		if(!empty($Options)){
			foreach ($Options as $OptionId => $Option){
				/**@var $Option Mage_Catalog_Model_Product_Option */
				$OptionValues = $Option->getValues();
				if(empty($OptionValues)){
					$ProductOptionsMapping[implode(self::DELIMITER,array($Product->getSku(),$OptionId))]
						= array(
						'ProductSku' => $Product->getSku(),
						'OptionId'   => $OptionId
					);
				}else{
					foreach ($OptionValues AS $OptionValueId => $OptionValue ){
						$ProductOptionsMapping[implode(self::DELIMITER,array($Product->getSku(),$OptionId, $OptionValueId))]
							= array(
							'ProductSku'    => $Product->getSku(),
							'OptionId'      => $OptionId,
							'OptionValueId' => $OptionValueId);
					}
				}
			}
		}
		return $ProductOptionsMapping;
	}

	/**
	 * Change Product Options Prices according to SDK response
	 * @param Mage_Catalog_Model_Product $Product
	 * @param array $ProductOptionsMapping
	 * @param Common\ProductResponseData[] $ProductsSDKResult
	 */
	protected function changeOptionsProductPrices(Mage_Catalog_Model_Product $Product, $ProductOptionsMapping, array $ProductsSDKResult ){

		foreach ($ProductOptionsMapping as $Key => $OptionMapping){
			if(!isset($ProductsSDKResult[$Key])){
				continue;
			}

			$ProductOptionResponseData = $ProductsSDKResult[$Key];
			$OptionNewPrice = $ProductOptionResponseData->getSalePrice();
			$OptionOriginalSalePrice = $ProductOptionResponseData->getOriginalSalePrice();
			$OptionOriginalListPrice = $ProductOptionResponseData->getOriginalListPrice();

			$Option = $Product->getOptionById($OptionMapping['OptionId']);

			if(isset($OptionMapping['OptionValueId']) ){
				$Value = $Option->getValueById($OptionMapping['OptionValueId']);
				$Value->setPrice($OptionNewPrice);
				$Value->setDefaultPrice($OptionNewPrice);
				$Value->setGlobaleOptionInfo($ProductOptionResponseData);
					//setGlobaleProductInfo
			}else{
				$Option->setGlobaleOptionInfo($ProductOptionResponseData);
				$Option->setPrice($OptionNewPrice);
				$Option->setDefaultPrice($OptionNewPrice);
			}
		}
		$Product->setGlobaleOptionChanged(true);
	}

	/**
	 * Add Product to Registry array - for future manipulations - (update Catalog Rule Price )
	 * Event ==> sales_quote_item_set_product
	 * @param Varien_Event_Observer $observer
	 */
	public function insertProductToRegistry(Varien_Event_Observer $observer)
	{
		if(Mage::registry('globale_user_supported')) {

			/**@var $Product Mage_Catalog_Model_Product */
			$Product = $observer->getProduct();

			//	$LocalVATRateType = self::buildLocalVATRateType($Product);
			$ProductRegisteredArray = Mage::registry('globale_products_array');

			if (!$ProductRegisteredArray) {
				$ProductRegisteredArray = array();
			}

			//if product already exist in array
			if (isset($ProductRegisteredArray[$Product->getEntityId()])) {
				return;
			}

			$ProductRegisteredArray[$Product->getEntityId()] = $Product;

			Mage::unregister('globale_products_array');
			Mage::register('globale_products_array', $ProductRegisteredArray);
		}
	}


	/**
	 * Convert Catalog Rule prices by using the Global-e SDK
	 * Observer => Product::updateCatalogRulePrices
	 * Events => globale_catalogRule_getRulePrices
	 * @param Varien_Event_Observer $observer
	 */
	public function updateCatalogRulePrices(Varien_Event_Observer $observer)
	{
		if (Mage::registry('globale_user_supported')) {
			/**@var $CatalogRuleResource Globale_Browsing_Model_Rewrite_CatalogRule_Resource_Rule */
			$CatalogRuleResource = $observer->getCatalogRuleResource();
			$RulePrices = $CatalogRuleResource->_RulePrices;

			if (empty($RulePrices)) {
				return;
			}

			$ProductRegisteredArray = Mage::registry('globale_products_array');

			foreach ($RulePrices AS $ProductId => &$rulePrice){

				if(!isset($ProductRegisteredArray[$ProductId])){
					continue;
				}
				$Product = $ProductRegisteredArray[$ProductId];

				if( $Product->hasGlobaleProductInfo() ){
					/**@var $Info Common\ProductResponseData **/
					$Info = $Product->getGlobaleProductInfo();

					if(!$Product->hasFinalPrice()){
						$Product->setFinalPrice($Info->getSalePrice());
					}

					$rulePrice = $Info->getSalePrice();
				}
			}
			$CatalogRuleResource->_RulePrices = $RulePrices;

		}
	}

	/**
	 * Beatify price amount of configurable price during calculating
	 * Event ==> catalog_product_type_configurable_price
	 * @param Varien_Event_Observer $observer
	 */
	public function beatifyProductConfigurablePrice(Varien_Event_Observer $observer){

		$Product = $observer->getEvent()->getProduct();

		if(Mage::registry('globale_user_supported') && $Product instanceof Mage_Catalog_Model_Product && $Product->hasGlobaleProductInfo() && $Product->getConfigurablePrice() !== null ){

			$ConfigurablePrice = $Product->getConfigurablePrice();
			$BeautyConfigurablePrice = Mage::helper('globale_browsing')->getBeautyAmount($ConfigurablePrice,true);
			$Product->setConfigurablePrice($BeautyConfigurablePrice);
		}
	}


    /**
     * Update Product View Config => set javascript prices to zero
     * Event => catalog_product_view_config
     * @param Varien_Event_Observer $observer
     */
    public function updateProductViewConfig(Varien_Event_Observer $observer){

        if (Mage::registry('globale_user_supported')) {
            $Response_object = $observer->getResponseObject();
            $Options = $Response_object->getAdditionalOptions();

			// use globale_max_decimal_places in javascript
			if (Mage::registry('globale_max_decimal_places') !== null) {

				$PriceFormat = Mage::app()->getLocale()->getJsPriceFormat();

				$PriceFormat['precision'] = Mage::registry('globale_max_decimal_places');
				$PriceFormat['requiredPrecision'] = Mage::registry('globale_max_decimal_places');
				$Options['priceFormat'] = $PriceFormat;
			}

            $Options['defaultTax'] = 0;
            $Options['currentTax'] = 0;

            $Response_object->setAdditionalOptions($Options);
        }

    }



     public function getUnknownVat(){

         $Rate = 0;
         $Name = 'Unknown';
         $VATRateTypeCode = 'Unknown';

         return new Common\VatRateType($Rate, $Name, $VATRateTypeCode);
     }








}

