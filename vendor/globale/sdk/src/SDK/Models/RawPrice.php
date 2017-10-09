<?php
namespace GlobalE\SDK\Models;

use GlobalE\SDK;
use GlobalE\SDK\Models\Common\Request;

/**
 * Class RawPrice
 * @package GlobalE\SDK\Models
 */
class RawPrice {

	/**
	 * Places for rounding
	 */
	const DEFAULT_DECIMAL_PLACES = 2;

	/**
	 * Raw prices
	 * @var array|Request\RawPriceRequestData[]
	 * @access private
	 */
	private $RawPrices;

	/**
	 * Flag if use/not use rounding
	 * @var bool
	 * @access private
	 */
	private $UseRounding;

	/**
	 * Flag if use/not use discount
	 * @var bool
	 * @access private
	 */
	private $IsDiscount;

	/**
	 * Price include vat
	 * @var bool
	 * @access private
	 */
	private $PriceIncludesVAT;

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
	 * RawPrice constructor.
	 * @param Request\RawPriceRequestData[] $RawPrices
	 * @param bool $PriceIncludesVAT
	 * @param bool $UseRounding
	 * @param bool $IsDiscount
	 * @access public
	 */
	public function __construct(array $RawPrices, $PriceIncludesVAT = false,$UseRounding = false, $IsDiscount = false) {
		$this->RawPrices = $RawPrices;
		$this->PriceIncludesVAT = $PriceIncludesVAT;
		$this->IsDiscount = $IsDiscount;
		$this->UseRounding = $UseRounding;
		$this->CalculationModel = new Calculation();
		$this->CountryModel = Country::getSingleton();
		$this->CurrencyModel= Currency::getSingleton();
		$this->initCalculationModel();
	}


	/**
	 * Initialize the calculation model properties
	 * @access protected
	 */
	protected function initCalculationModel(){
		$this->initCurrencyRules();
		$this->initCountryRules();
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
		$RoundingRule = $this->getCountryModel()->fetchRoundingRules();
		$this->CalculationModel->setRoundingRule($RoundingRule);
	}


	/**
	 * Build raw Price information result according to $RawPrice input data
	 * @return array
	 * @access protected
	 */
	public function buildRawPricesInformationResult(){
		$RawPricesResult = array();

		foreach ($this->getRawPrices() AS $Key => $RawPrice){

			//Calculate Real VATs (Local + Global-e ) and set to $RawPrice
			$RawPrice = $this->calculateRawDataVat($RawPrice);

			$RawPriceResult = new Common\RawPriceResponseData();
			$RawPriceResult->setVATRateType($RawPrice->getVATRateType());
			$RawPriceResult->setLocalVATRateType($RawPrice->getLocalVATRateType());
			$RawPriceResult->setRawPriceKey($RawPrice->getRawPriceKey());

			$this->getCalculationModel()->calculateDataPrices($RawPrice,$RawPriceResult, $this->isPriceIncludesVAT(),$this->isUseRounding(),$this->IsDiscount );


			//Collecting each $RawPriceResult to Output Array
			$RawPricesResult[$Key] = $RawPriceResult;
		}
		return $RawPricesResult;
	}


	/**
	 * Calculate LocalVATRateType/VATRateType and set into $RawPrice object
	 * @param Request\RawPriceRequestData $RawPrice
	 * @return Request\RawPriceRequestData
	 * @access protected
	 */
	protected function calculateRawDataVat(Request\RawPriceRequestData $RawPrice) {

		$Country = $this->CountryModel->getCountry();

		if (!$RawPrice->getLocalVATRateType()) {
			// VATRateType => LocalVatRateType provided by the Merchant.
			$RawPrice->setLocalVATRateType(SDK\SDK::$MerchantVatRateType);
		}
		//For the case when GE VATRateType won't be change - it will use LocalVATRateType
		$RawPrice->setVATRateType($RawPrice->getLocalVATRateType());

		//if Country setting UseCountryVAT = true
		if ($Country->UseCountryVAT) {
			if ($this->CountryModel->getVatRateType()) {
				// Checking if country has VATRateType Object (came from Countries API )
				$RawPrice->setVATRateType($this->CountryModel->getVatRateType());
			}
		}
		return $RawPrice;
	}



	/**
	 * Get raw prices
	 * @return Request\RawPriceRequestData[]
	 * @access public
	 */
	public function getRawPrices()
	{
		return $this->RawPrices;
	}

	/**
	 * Set raw prices
	 * @param Request\RawPriceRequestData[] $RawPrices
	 * @return RawPrice
	 * @access public
	 */
	public function setRawPrices($RawPrices)
	{
		$this->RawPrices = $RawPrices;
		return $this;
	}

	/**
	 * Get use/not use rounding
	 * @return boolean
	 * @access public
	 */
	public function isUseRounding()
	{
		return $this->UseRounding;
	}

	/**
	 * Set use/not use rounding
	 * @param boolean $UseRounding
	 * @return RawPrice
	 * @access public
	 */
	public function setUseRounding($UseRounding)
	{
		$this->UseRounding = $UseRounding;
		return $this;
	}

	/**
	 * Get use/not use discount
	 * @return boolean
	 * @access public
	 */
	public function isIsDiscount()
	{
		return $this->IsDiscount;
	}

	/**
	 * Set use/not use discount
	 * @param boolean $IsDiscount
	 * @return RawPrice
	 * @access public
	 */
	public function setIsDiscount($IsDiscount)
	{
		$this->IsDiscount = $IsDiscount;
		return $this;
	}

	/**
	 * Get price include vat
	 * @return boolean
	 * @access public
	 */
	public function isPriceIncludesVAT()
	{
		return $this->PriceIncludesVAT;
	}

	/**
	 * Set price include vat
	 * @param boolean $PriceIncludesVAT
	 * @return RawPrice
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
	 * @return RawPrice
	 * @access public
	 */
	public function setCalculationModel($CalculationModel)
	{
		$this->CalculationModel = $CalculationModel;
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
	 * @return RawPrice
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
	 * @return RawPrice
	 * @access public
	 */
	public function setCurrencyModel($CurrencyModel)
	{
		$this->CurrencyModel = $CurrencyModel;
		return $this;
	}

}