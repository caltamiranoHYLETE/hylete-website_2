<?php
namespace GlobalE\SDK\Models;


/**
 * Class for Beautify amount without any other price changes
 * Class AmountBeautifier
 * @package GlobalE\SDK\Models
 */
class AmountBeautifier {

	/**
	 * Places for rounding
	 */
	const DEFAULT_DECIMAL_PLACES = 2;

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


	public function __construct(){

		$this->CalculationModel = new Calculation();
		$this->CountryModel = Country::getSingleton();
		$this->CurrencyModel= Currency::getSingleton();
		$this->initCalculationModel();
	}


	/**
	 * Beautifier Amount by Formatting and Rounding by Global-e logic
	 * @param float $Amount
	 * @param boolean $UseRounding - Flag if use/not use rounding
	 * @return float
	 */
	public function beautifierAmount($Amount,$UseRounding){

		$BeautifierAmount = $this->CalculationModel->beautifierAmount($Amount,$UseRounding);
		return $BeautifierAmount;
	}

	/**
	 * Init CurrencyModel needed settings
	 */
	protected function initCalculationModel(){

		//setMaxDecimalPlaces
		$DecimalPlaces = self::DEFAULT_DECIMAL_PLACES;
		$CurrentCurrency = $this->CurrencyModel->getCurrentCurrency();

		if(isset($CurrentCurrency->MaxDecimalPlaces) && is_numeric($CurrentCurrency->MaxDecimalPlaces)) {
			$DecimalPlaces = $CurrentCurrency->MaxDecimalPlaces;
		}
		$this->CalculationModel->setMaxDecimalPlaces($DecimalPlaces);

		//set Rounding Rule
		$RoundingRule = $this->CountryModel->fetchRoundingRules();
		$this->CalculationModel->setRoundingRule($RoundingRule);

	}




}