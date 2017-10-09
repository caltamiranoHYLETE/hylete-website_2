<?php
namespace GlobalE\SDK\Models;

use GlobalE\SDK\Models\Common;
use GlobalE\SDK\Core;
use GlobalE\SDK\Models\Common\ItemDataResponseInterface;
use GlobalE\SDK\API;

/**
 * Class Calculation
 * @package GlobalE\SDK\Models
 */
class Calculation {

	/**
	 * Currency rates
	 * @var API\Common\Response\CurrencyRate
	 * @access private
	 */
	private $CurrencyRates;

	/**
	 * Country oefficients
	 * @var API\Common\Response\CountryCoefficient
	 * @access private
	 */
	private $CountryCoefficients;

	/**
	 * Maximum places for rounding
	 * @var int
	 * @access private
	 */
	private $MaxDecimalPlaces;

	
	/**
	 * Rounding rules
	 * @var API\Common\Response\RoundingRule| null
	 * @access private
	 */
	private $RoundingRule;




	/**
	 * Prices Calculation for Product/RawPrice Objects
	 *
	 * @param Common\Request\ItemDataRequestInterface $PriceRequestData
	 * @param ItemDataResponseInterface $PriceResultData
	 * @param $PriceIncludesVAT
	 * @param $UseRounding
	 * @param $IsDiscount
	 */
	public function calculateDataPrices(Common\Request\ItemDataRequestInterface $PriceRequestData, ItemDataResponseInterface $PriceResultData, $PriceIncludesVAT, $UseRounding = true, $IsDiscount = false ){

		if($PriceRequestData->getIsFixedPrice()){
			//Result List Price came from OriginalSalePrice --> FIXED
            // ListPrice should be taken from OriginalListPrice!
			//$ListPrice = $PriceRequestData->getOriginalSalePrice();
			$ListPrice = $PriceRequestData->getOriginalListPrice();

			//Result Sale Price came from OriginalSalePrice --> FIXED
			$SalePrice = $PriceRequestData->getOriginalSalePrice();

			//No rounding in Fixed Price
			$SalePriceBeforeRounding = $PriceRequestData->getOriginalSalePrice();

			//Original Sale Price same as Request OriginalSalePrice --> FIXED
			$OriginalSalePrice = $PriceRequestData->getOriginalSalePrice();

		}else{
			Core\Log::log('ListPrice Calculation' , Core\Log::LEVEL_INFO);
			//Result List Price => calculation flow with OriginalListPrice as Price input
			$ListPrice = $this->calculateDataPrice($PriceRequestData, $PriceRequestData->getOriginalListPrice(), $PriceIncludesVAT, $UseRounding, $IsDiscount);

			Core\Log::log('SalePrice Calculation', Core\Log::LEVEL_INFO);
			//Result Sale Price => calculation flow with  OriginalSalePrice as Price input
			$SalePrice = $this->calculateDataPrice($PriceRequestData, $PriceRequestData->getOriginalSalePrice(), $PriceIncludesVAT, $UseRounding, $IsDiscount);

			//Result Sale Price Before rounding => sale price calculation but without Marketing Rounding
			$SalePriceBeforeRounding = $this->calculateDataPrice($PriceRequestData, $PriceRequestData->getOriginalSalePrice(), $PriceIncludesVAT, false, $IsDiscount);

            //Original Sale Price
            $OriginalSalePrice = $PriceRequestData->getOriginalSalePrice();
		}

		//Result OriginalListPrice => same as input OriginalListPrice
		$PriceResultData->setOriginalListPrice($PriceRequestData->getOriginalListPrice());

		$PriceResultData->setOriginalSalePrice($OriginalSalePrice);

		$PriceResultData->setListPrice($ListPrice);
		$PriceResultData->setSalePrice($SalePrice);

		$PriceResultData->setSalePriceBeforeRounding($SalePriceBeforeRounding);
	}


	/**
	 * Beautify the Amount by formatting and Rounding according to Global-e logic
	 * @param float $Amount
	 * @param boolean $UseRounding
	 * @return float
	 */
	public function beautifierAmount($Amount,$UseRounding){

		//Formatting Price according to system rules
		$FormattedAmount = $this->formatPrice($Amount);

		//Applying Rounding rules
		if($UseRounding){
			$FinalAmount = $this->roundPrice($FormattedAmount);
		}else{
			$FinalAmount = $FormattedAmount;
		}
		return $FinalAmount;
	}

	/**
	 * Calculate Price according to Input Object type
	 *
	 * @param Common\Request\ItemDataRequestInterface $PriceRequestData
	 * @param $OriginalPrice
	 * @param $PriceIncludesVAT
	 * @param $UseRounding
	 * @param $IsDiscount
	 * @return float
	 * @throws \Exception
	 */
	protected function calculateDataPrice(Common\Request\ItemDataRequestInterface $PriceRequestData, $OriginalPrice, $PriceIncludesVAT, $UseRounding, $IsDiscount){

		if($PriceRequestData instanceof Common\Request\RawPriceRequestData) {
			$Price = $this->calculateRawPrice($PriceRequestData, $OriginalPrice, $PriceIncludesVAT, $UseRounding, $IsDiscount);

		}elseif ($PriceRequestData instanceof Common\Request\ProductRequestData ){
			$Price = $this->calculatePrice($PriceRequestData, $OriginalPrice, $PriceIncludesVAT, $UseRounding);

		}else{
			throw new \Exception('Unexpected  PriceRequestData type '.get_class($PriceRequestData));
		}
		return $Price;
	}



	/**
	 * Price amount calculation
	 * @param Common\Request\ProductRequestData $Product
	 * @param float $OriginalPrice - Could be OriginalListPrice or OriginalSalePrice
	 * @param bool $PriceIncludesVAT
	 * @param bool $UseRounding
	 * @return float
	 */
	protected function calculatePrice(Common\Request\ProductRequestData $Product, $OriginalPrice, $PriceIncludesVAT, $UseRounding){

		//Applying VAT rules
		$CalculatedTaxPrice = $this->calculateItemTax( $Product, $OriginalPrice, $PriceIncludesVAT);

		// Applying Price Coefficient rule
		$AppliedPriceCoefficients = $this->applyPriceCoefficients($CalculatedTaxPrice);

		//Converting Price to user currency
		$ConvertedPrice = $this->convertPrice($AppliedPriceCoefficients);

		//Formatting Price according to system rules
		$FormattedPrice = $this->formatPrice($ConvertedPrice);

		//Applying Rounding rules
		if($UseRounding){
			$Price = $this->roundPrice($FormattedPrice);
		}else{
			$Price = $FormattedPrice;
		}

		$logData = array(
			'Product'                  => $Product->getProductCode(),
			'OriginalPrice'            => $OriginalPrice,
			'CalculatedTaxPrice'       => $CalculatedTaxPrice,
			'PriceCoefficients'        => $this->getCountryCoefficients()->Rate,
			'AppliedPriceCoefficients' => $AppliedPriceCoefficients,
			'CurrencyRate'             => $this->getCurrencyRates()->Rate,
			'ConvertedPrice'           => $ConvertedPrice,
			'MaxDecimalPlaces'         => $this->getMaxDecimalPlaces(),
			'FormattedPrice'           => $FormattedPrice,
			'Price'                    => $Price
		);

		Core\Log::log('calculatePrice '.json_encode($logData) , Core\Log::LEVEL_INFO);
		return $Price;
	}


	/**
	 * Calculate raw price amount
	 * @param Common\Request\RawPriceRequestData $priceRequestData
	 * @param float $OriginalPrice - Could be OriginalListPrice or OriginalSalePrice
	 * @param bool $PriceIncludesVAT
	 * @param bool $UseRounding
	 * @param bool $IsDiscount
	 * @return float
	 * @access public
	 */
	protected function calculateRawPrice(Common\Request\RawPriceRequestData $priceRequestData, $OriginalPrice, $PriceIncludesVAT, $UseRounding, $IsDiscount){

		//Applying VAT rules
		$CalculatedTaxPrice = $this->calculateItemTax($priceRequestData, $OriginalPrice, $PriceIncludesVAT);

		// Do NOT Apply Price Coefficient rule
		if(!$IsDiscount){
			$AppliedPriceCoefficients = $this->applyPriceCoefficients($CalculatedTaxPrice);
		}else{
			$AppliedPriceCoefficients = $CalculatedTaxPrice;
		}

		//Converting Price to user currency
		$ConvertedPrice = $this->convertPrice($AppliedPriceCoefficients);

		//Formatting Price according to system rules
		$FormattedPrice = $this->formatPrice($ConvertedPrice);

		//Applying Rounding rules
		if($UseRounding){
			$Price = $this->roundPrice($FormattedPrice);
		}else{
			$Price = $FormattedPrice;
		}

		$logData = array(

			'OriginalPrice'            => $OriginalPrice,
			'CalculatedTaxPrice'       => $CalculatedTaxPrice,
			'IsDiscount'               => $IsDiscount,
			'PriceCoefficients'        => $this->getCountryCoefficients()->Rate,
			'AppliedPriceCoefficients' => $AppliedPriceCoefficients,
			'CurrencyRate'             => $this->getCurrencyRates()->Rate,
			'ConvertedPrice'           => $ConvertedPrice,
			'MaxDecimalPlaces'         => $this->getMaxDecimalPlaces(),
			'UseRounding'              => $UseRounding,
			'FormattedPrice'           => $FormattedPrice,
			'Price'                    => $Price
		);
		Core\Log::log(' calculateRawPrice '.json_encode($logData) , Core\Log::LEVEL_INFO);

		return $Price;
	}


	

	/**
	 * Calculate item price amount with additional tax
	 * @param Common\Request\ItemDataRequestInterface $PriceItem
	 * @param float $OriginalPrice - Could be OriginalListPrice or OriginalSalePrice
	 * @param bool $PriceIncludesVAT
	 * @return float
	 * @access protected
	 */
	protected function calculateItemTax(Common\Request\ItemDataRequestInterface $PriceItem, $OriginalPrice, $PriceIncludesVAT) {

		$Price = $OriginalPrice;

		$CountryCoefficients = $this->getCountryCoefficients();
		$IncludeVatType = $CountryCoefficients->IncludeVAT;
		
		$LocalVATRate = $PriceItem->getLocalVATRateType()->Rate;
		$GlobaleVATRate = $PriceItem->getVATRateType()->Rate;

		//Always remove Local VAT if PriceIncludesVAT
		if($PriceIncludesVAT){
			$Price = $this->removeVat($Price, $LocalVATRate);
		}


		switch ($IncludeVatType) {

			case Currency::HideVAT :
				//if HideVAT (type = 0) - remove local VAT 
				break;

			case Currency::PocketVAT :
				//if PocketVAT (type = 4) and price doesn't include VAT ==> add Local VAT
				$Price = $this->addVat($Price, $LocalVATRate);
				break;

			case Currency::ForceVAT :
				//if ForceVAT (type = 6) -> remove local VAT and add GE VAT
				$Price = $this->addVat($Price, $GlobaleVATRate);
				break;


			case Currency::ForceAndHideVAT ://(type = 8)
				//@todo Implement
				break;
		}

		$logData = array(
			'PriceBefore'      => $OriginalPrice,
			'IncludeVatType'   => $IncludeVatType,
			'LocalVATRate'	   => $LocalVATRate,
			'GlobaleVATRate'   => $GlobaleVATRate,
			'PriceAfter'       => $Price
		);

		if($PriceItem instanceof Common\Request\ProductRequestData){
			$logData['Product'] = $PriceItem->getProductCode();
		}

		Core\Log::log('calculateItemTax =>'.json_encode($logData) , Core\Log::LEVEL_INFO);

		return $Price;
	}


	/**
	 * Subtract the vat from the amount
	 * @param float $Amount
	 * @param float $Rate
	 * @return float
	 * @access protected
	 */
	protected function removeVat($Amount, $Rate){

		$Multiplier = 1 + ($Rate/100) ;
		$Result = $Amount / $Multiplier;
		return $Result;

	}

	/**
	 * Add vat to the amount
	 * @param float $Amount
	 * @param float $Rate
	 * @return mixed
	 * @access protected
	 */
	protected function addVat($Amount, $Rate){
		$Multiplier = 1 + ($Rate/100) ;
		$Result = $Amount * $Multiplier;
		return $Result;
	}

	/**
	 * Apply coefficients rates to amount
	 * @param $Amount
	 * @return float
	 * @access protected
	 */
	protected function applyPriceCoefficients($Amount){
		$CountryCoefficients = $this->getCountryCoefficients();
		$Result = $Amount * $CountryCoefficients->Rate;
		return $Result;
	}

	/**
	 * Convert price amount
	 * @param float $Amount
	 * @return float
	 * @access protected
	 */
	protected function convertPrice($Amount){
		$CurrencyRates = $this->getCurrencyRates();
		$Result = $Amount * $CurrencyRates->Rate;
		return $Result;
	}

	/**
	 * Get the price in the right format by customer details
	 * @param float $Amount
	 * @return float
	 * @access public
	 */
	protected function formatPrice($Amount) {

		$MaxDecimalPlaces = $this->getMaxDecimalPlaces();
		// round the price with the customer currency
		$FormattedPrice = round($Amount, $MaxDecimalPlaces);
		return $FormattedPrice;
	}



	/**
	 * Round price amount by the rounding rules
	 * @param float $Amount
	 * @return float
	 * @access protected
	 */
	protected function roundPrice($Amount){

		$RoundingRule = $this->getRoundingRule();

		if(!$RoundingRule){
			return $Amount;
		}
		$roundingModel = new Rounding($Amount,$RoundingRule);
		$Result = $roundingModel->round();
		return $Result;
	}

	

	/**
	 * Get currency rates
	 * @return API\Common\Response\CurrencyRate
	 * @access public
	 */
	public function getCurrencyRates()
	{
		return $this->CurrencyRates;
	}

	/**
	 * Set currency rates
	 * @param API\Common\Response\CurrencyRate $CurrencyRates
	 * @return Calculation
	 * @access public
	 */
	public function setCurrencyRates($CurrencyRates)
	{
		$this->CurrencyRates = $CurrencyRates;
		return $this;
	}

	/**
	 * Get country coefficients
	 * @return API\Common\Response\CountryCoefficient
	 * @access public
	 */
	public function getCountryCoefficients()
	{
		return $this->CountryCoefficients;
	}

	/**
	 * Set country coefficients
	 * @param API\Common\Response\CountryCoefficient $CountryCoefficients
	 * @return Calculation
	 * @access public
	 */
	public function setCountryCoefficients($CountryCoefficients)
	{
		$this->CountryCoefficients = $CountryCoefficients;
		return $this;
	}
	

	/**
	 * Get rounding rules
	 * @return API\Common\Response\RoundingRule
	 * @access public
	 */
	public function getRoundingRule()
	{
		return $this->RoundingRule;
	}

	/**
	 * Set rounding rules
	 * @param API\Common\Response\RoundingRule $RoundingRule
	 * @return Calculation
	 * @access public
	 */
	public function setRoundingRule($RoundingRule)
	{
		$this->RoundingRule = $RoundingRule;
		return $this;
	}

	/**
	 * Get maximum places for rounding
	 * @return int
	 * @access public
	 */
	public function getMaxDecimalPlaces()
	{
		return $this->MaxDecimalPlaces;
	}

	/**
	 * Set maximum places for rounding
	 * @param int $MaxDecimalPlaces
	 * @return Calculation
	 * @access public
	 */
	public function setMaxDecimalPlaces($MaxDecimalPlaces)
	{
		$this->MaxDecimalPlaces = $MaxDecimalPlaces;
		return $this;
	}



}