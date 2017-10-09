<?php

namespace GlobalE\SDK\Models;

use GlobalE\SDK;
use GlobalE\SDK\Core;
use GlobalE\SDK\Models\Common;
use GlobalE\SDK\API\Processors;
use GlobalE\SDK\Models\Common\Request;

use GlobalE\SDK\API;

/**
 * Class Product
 * Model for collecting all Product Price data
 * @package GlobalE\SDK\Models
 */
class Product {

	/**
	 * Prefix that we will add to keys of $ProductCountry array. Take care in cases when SKU is big number
	 */
	const PRODUCT_COUNTRY_KEY_PREF = 'key-';

	/**
	 * Places for rounding
	 */
	const DEFAULT_DECIMAL_PLACES = 2;

	/**
	 * Time to store the cache (in seconds)
	 */
	const TTL = 1800; // 30 min

	/**
	 * Collection of products
	 * @var Common\Request\ProductRequestData[]
	 * @access private
	 */
	private $Products;

	/**
	 * Price include vat
	 * @var bool
	 * @access private
	 */
	private $PriceIncludesVAT;

	/**
	 * Collection of products countries
	 * @var API\Common\Response\ProductCountry[]
	 * @access private
	 */
	private $ProductCountry;

	/**
	 * Calculation model
	 * @var Calculation
	 * @access private
	 */
	private $CalculationModel;

	/**
	 * Country model
	 * @var Country
	 * @access private
	 */
	private $CountryModel;

	/**
	 * Currency model
	 * @var Currency
	 * @access private
	 */
	private $CurrencyModel;

	/**
	 * Product constructor.
	 * @param Common\Request\ProductRequestData[] $Products
	 * @param bool $PriceIncludesVAT
	 * @access public
	 */
	public function __construct(array $Products, $PriceIncludesVAT = false){
		$this->setProducts($Products);
		$this->setPriceIncludesVAT($PriceIncludesVAT);
		$this->CalculationModel = new Calculation();
		$this->CountryModel = Country::getSingleton();
		$this->CurrencyModel = Currency::getSingleton();
		$this->initCalculationModel();
	}

	/**
	 * Initialize the calculation rules and products countries
	 * @access protected
	 */
	protected function initCalculationModel(){
		$this->initCurrencyRules();
		$this->initCountryRules();
		$this->initProductCountry();
	}


	/**
	 * Initialize currency rates and decimal places for rounding, set the data in the calculation model
	 * @access protected
	 */
	protected function initCurrencyRules(){
	
		$CurrencyRates = $this->CurrencyModel->fetchCurrencyRates();

		$DecimalPlaces = self::DEFAULT_DECIMAL_PLACES;
		$CurrentCurrency = $this->CurrencyModel->getCurrentCurrency();
		
		if(isset($CurrentCurrency->MaxDecimalPlaces) && is_numeric($CurrentCurrency->MaxDecimalPlaces)) {
			$DecimalPlaces = $CurrentCurrency->MaxDecimalPlaces;
		}
		
		$this->CalculationModel->setCurrencyRates($CurrencyRates);
		$this->CalculationModel->setMaxDecimalPlaces($DecimalPlaces);
	}


	/**
	 * Initialize country coefficients and rounding rules
	 * @access protected
	 */
	protected function initCountryRules(){

		$CountryCoefficients = $this->CountryModel->fetchCountryCoefficients();
		$this->CalculationModel->setCountryCoefficients($CountryCoefficients);
		$RoundingRule = $this->CountryModel->fetchRoundingRules();
		$this->CalculationModel->setRoundingRule($RoundingRule);

		Core\Log::log(' RoundingRule '.json_encode($RoundingRule) , Core\Log::LEVEL_INFO);
		Core\Log::log(' CountryCoefficient '.json_encode($CountryCoefficients) , Core\Log::LEVEL_INFO);
	}



	/**
	 * Initialize products countries and set the data in the calculation model
	 * @access protected
	 */
	protected function initProductCountry(){

		$NonCachedProductsList = array();

		//$NonCachedProductsList will be update because it's send by reference
		$CachedProductCountryList = $this->getCachedProductCountry($NonCachedProductsList);

		//get Data from API
		$ProductCountryList = $this->getProductCountryAPIResults($NonCachedProductsList);
		$ProductCountryList = array_merge($ProductCountryList,$CachedProductCountryList);

		$this->setProductCountry($ProductCountryList);

		Core\Log::log(' ProductCountryList '.json_encode($ProductCountryList) , Core\Log::LEVEL_INFO);
	}


	/**
	 * Get products countries collection from cached and update the given array $nonCachedProductsList
	 * @param array $NonCachedProductsList
	 * @return API\Common\Response\ProductCountry[]
	 * @access protected
	 */
	protected function getCachedProductCountry(array &$NonCachedProductsList){
		$CachedProductCountry = array();

		/**@var $Customer Customer */
		$Customer = Customer::getSingleton();

		$ApiParams = new Common\ApiParams();
		$ApiParams->setUri(array(
			'countryCode' => $Customer->getInfo()->getCountryISO(),
			'cultureCode' => $Customer->getInfo()->getCultureCode()));

		foreach ($this->Products as $Product){
			$productCode = $Product->getProductCode();

			$ApiParams->setUri(array('productCode' => $productCode));
			$Processor = new Processors\ProductCountryS($ApiParams);

			$DataFromCache = $Processor->getDataFromCache();
			if($DataFromCache !== false){
				$CachedProductCountry[self::PRODUCT_COUNTRY_KEY_PREF.$productCode] = $DataFromCache;
			}else{
				$NonCachedProductsList[] = $productCode;
			}
		}
		return $CachedProductCountry;

	}

	/**
	 * Get the products countries collection from API service
	 * @param array $NonCachedProductsList
	 * @return API\Common\Response\ProductCountry[]
	 * @access protected
	 */
	protected function getProductCountryAPIResults($NonCachedProductsList){
		
		if(empty($NonCachedProductsList)){
			return array();
		}
		$ProductCountryAPIResults = array();
		$ProductsListResult = array();

		/**@var $Customer Customer */
		$Customer = Customer::getSingleton();

		$ApiParams = new Common\ApiParams();
		$ApiParams->setUri(array('countryCode' => $Customer->getInfo()->getCountryISO(),
								 'cultureCode' => $Customer->getInfo()->getCultureCode()));

		$NonCachedProductsListChunk = array_chunk($NonCachedProductsList,Core\Settings::get('API.BulkSize') );
		
		foreach ($NonCachedProductsListChunk as $ProductsList){
			
			$ApiParams->setUri(array('productCode' => $ProductsList));
			$Processor = new Processors\ProductCountryS($ApiParams);
			try{
                $ProductsListResultItem = $Processor->processRequest();
            }
            catch(\Exception $e) {
                //if productCountryS fails we can still calculate the price.
			    $ProductsListResultItem = array();
            }
			$ProductsListResult  = array_merge($ProductsListResult,$ProductsListResultItem);
		}
		
		foreach ($ProductsListResult AS $ProductResult) {
			/**@var $ProductResult API\Common\Response\ProductCountry */
			$productCode = $ProductResult->ProductCode;
			$ProductCountryAPIResults[self::PRODUCT_COUNTRY_KEY_PREF.$productCode] = $ProductResult;
		}
		
		return $ProductCountryAPIResults;
	}

	/**
	 * Update the products collection with information, according to $Products collection data
	 * @return Common\ProductResponseData[]
	 * @access public
	 */
	public function buildProductsInformationResult(){

		$ProductsResult = array();
		$ProductsCountry = $this->getProductCountry();

		foreach ($this->getProducts() AS $Product){

			$CachedMassage = 'From Cache ';
			$CacheKey = $this->buildCacheKey($Product);
			$ProductResult = Core\Cache::get($CacheKey);

			Core\Log::log('BuildProductsInformation Request '.json_encode($Product) , Core\Log::LEVEL_INFO);
			Core\Log::log('PriceIncludeVAT = '. $this->isPriceIncludesVAT(), Core\Log::LEVEL_INFO);

			if($ProductResult == null){

				$CachedMassage = '';

				/**@var $ProductCountry API\Common\Response\ProductCountry */
				$ProductCountry = $ProductsCountry[self::PRODUCT_COUNTRY_KEY_PREF.$Product->getProductCode()];

				$ProductResult = new Common\ProductResponseData();
				$ProductResult->setProductCode($Product->getProductCode());
				$ProductResult->setIsForbidden($ProductCountry->IsForbidden);
				$ProductResult->setIsRestricted($ProductCountry->IsRestricted);
				$ProductResult->setMarkedAsFixedPrice($Product->getIsFixedPrice());

				if($ProductCountry->RestrictionMessage != null){
					$ProductResult->setRestrictionMessage($ProductCountry->RestrictionMessage);
				}


				//Calculate Real VATs (Local + Global-e ) and set to $Product
				$Product = $this->calculateProductVat($Product);

				$this->CalculationModel->calculateDataPrices($Product,$ProductResult,$this->isPriceIncludesVAT());

				$ProductResult->setVATRateType($Product->getVATRateType());
				$ProductResult->setLocalVATRateType($Product->getLocalVATRateType());

				Core\Cache::set($CacheKey,$ProductResult,self::TTL);
			}

			Core\Log::log('BuildProductsInformation Result '.$CachedMassage.json_encode($ProductResult) , Core\Log::LEVEL_INFO);

			//Collecting each $ProductResult to output array
			$ProductsResult[$Product->getProductCode()] = $ProductResult;
		}
		return $ProductsResult;
	}

	/**
	 * Create unique cache key $Product | $CustomerInfo | $BaseInfo
	 * @param Request\ProductRequestData $Product
	 * @return string
	 * @access protected
	 */
	protected function buildCacheKey(SDK\Models\Common\Request\ProductRequestData $Product){

		/**@var $Customer Customer */
		$Customer = Customer::getSingleton();
		$CustomerInfo = $Customer->getInfo();
		$BaseInfo = SDK\SDK::$baseInfo;

		$key = md5(serialize($Product).'|'.serialize($CustomerInfo).'|'.serialize($BaseInfo));
		return $key;
	}


	/**
	 * Calculate LocalVATRateType/VATRateType and set into product
	 * @param Common\Request\ProductRequestData $Product
	 * @return Common\Request\ProductRequestData
	 * @access protected
	 */
	protected function calculateProductVat(Common\Request\ProductRequestData $Product) 	{

		$Country = $this->CountryModel->getCountry();

		if (!$Product->getLocalVATRateType()) {
			// VATRateType => LocalVatRateType provided by the Merchant.
			$Product->setLocalVATRateType(SDK\SDK::$MerchantVatRateType);
		}
		//For the case when GE VATRateType won't be change - it will use LocalVATRateType
		$Product->setVATRateType($Product->getLocalVATRateType());

		//if Country setting UseCountryVAT = true
		if ($Country->UseCountryVAT) {
			$ProductCountryList = $this->getProductCountry();
			$ProductCountry = $ProductCountryList[self::PRODUCT_COUNTRY_KEY_PREF.$Product->getProductCode()];

			/**@var $ProductCountry \stdClass */
			// Checking if product has VATRateType Object (came from ProductCountryS API )
			if ($ProductCountry->VATRateType) {
				$Product->setVATRateType(
					new Common\VatRateType(
						$ProductCountry->VATRateType->Rate,
						$ProductCountry->VATRateType->Name,
						$ProductCountry->VATRateType->VATRateTypeCode
					)
				);
			}
			elseif ($this->CountryModel->getVatRateType()) {
				// Checking if country has VATRateType Object (came from Countries API )
				$Product->setVATRateType($this->CountryModel->getVatRateType());
			}
		}
		return $Product;
	}

	
	/**
	 * Get collection of products
	 * @return Common\Request\ProductRequestData[]
	 * @access public
	 */
	public function getProducts()
	{
		return $this->Products;
	}

	/**
	 * Set collection of products
	 * @param Common\Request\ProductRequestData[] $Products
	 * @return Product
	 * @access public
	 */
	public function setProducts($Products)
	{
		$this->Products = $Products;
		return $this;
	}

	/**
	 * Get Price include/not include vat
	 * @return boolean
	 * @access public
	 */
	public function isPriceIncludesVAT()
	{
		return $this->PriceIncludesVAT;
	}

	/**
	 * Set Price include/not include vat
	 * @param boolean $PriceIncludesVAT
	 * @return Product
	 * @access public
	 */
	public function setPriceIncludesVAT($PriceIncludesVAT)
	{
		$this->PriceIncludesVAT = $PriceIncludesVAT;
		return $this;
	}

	/**
	 * Get calculation model
	 * @return Calculation
	 * @access public
	 */
	public function getCalculationModel()
	{
		return $this->CalculationModel;
	}

	/**
	 * Set calculation model
	 * @param Calculation $CalculationModel
	 * @return Product
	 * @access public
	 */
	public function setCalculationModel($CalculationModel)
	{
		$this->CalculationModel = $CalculationModel;
		return $this;
	}

	/**
	 * Get collection products countries
	 * @return API\Common\Response\ProductCountry[]
	 * @access public
	 */
	public function getProductCountry()
	{
		return $this->ProductCountry;
	}

	/**
	 * Set collection products countries
	 * @param API\Common\Response\ProductCountry[] $ProductCountry
	 * @return Product
	 * @access public
	 */
	public function setProductCountry($ProductCountry)
	{
		$this->ProductCountry = $ProductCountry;
		return $this;
	}

	/**
	 * Get country model
	 * @return Country
	 * @access public
	 */
	public function getCountryModel()
	{
		return $this->CountryModel;
	}

	/**
	 * Set country model
	 * @param Country $CountryModel
	 * @return Product
	 * @access public
	 */
	public function setCountryModel($CountryModel)
	{
		$this->CountryModel = $CountryModel;
		return $this;
	}

	/**
	 * Get currency model
	 * @return Currency
	 * @access public
	 */
	public function getCurrencyModel()
	{
		return $this->CurrencyModel;
	}

	/**
	 * Set currency model
	 * @param Currency $CurrencyModel
	 * @return Product
	 * @access public
	 */
	public function setCurrencyModel($CurrencyModel)
	{
		$this->CurrencyModel = $CurrencyModel;
		return $this;
	}

}