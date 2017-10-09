<?php
namespace GlobalE\Test\SDK\Models;

use GlobalE\SDK\Models\Common;

use GlobalE\SDK\Models\Common\RawPriceResponseData;
use GlobalE\Test\SDK\Models\Common AS TestCommon;

class CalculationTest extends \PHPUnit_Framework_TestCase
{

	public function testRoundPrice(){
		$mock = new CalculationMock();
		$Amount = 25.73;

		//case 1 : no rounding
		$expected = 25.73;
		$actual = $mock->roundPrice($Amount);
		$this->assertEquals($expected, $actual,'Case 1 : no rounding');

		//Case 2 with rounding
		$mock->setRoundingRule($this->buildRoundingRule());
		$expected = 25.99;
		$actual = $mock->roundPrice($Amount);
		$this->assertEquals($expected, $actual,'Case 2 : with rounding');
		
	}

	public function testFormatPrice(){
		$mock = new CalculationMock();
		$mock->setMaxDecimalPlaces(3);

		$Amount = 25.73897;
		$expected = 25.739;
		$actual= $mock->formatPrice($Amount);
		$this->assertEquals($expected, $actual,'',0.0001);
	}
	
	public function testConvertPrice(){
		$mock = new CalculationMock();

		$CurrencyRates = (object) array('Rate' => 1.33356);
		$mock->setCurrencyRates($CurrencyRates);

		$Amount = 19.50;
		$expected = 26.00442;
		$actual = $mock->convertPrice($Amount);
		$this->assertEquals($expected, $actual,'',0.0001);
	}

	public function testConvertToOriginalPrice(){
		$mock = new CalculationMock();

		$CurrencyRates = (object) array('Rate' => 1.33356);
		$mock->setCurrencyRates($CurrencyRates);

		$Amount = 19.50;
		$expected = 14.62251;
		$actual = $mock->convertToOriginalPrice($Amount);
		$this->assertEquals($expected, $actual,'',0.0001);
	}

	public function testApplyPriceCoefficients(){
		$mock = new CalculationMock();
		$CountryCoefficients = (object) array('Rate' => 0.93);
		$mock->setCountryCoefficients($CountryCoefficients);
		$Amount = 17.33;
		$expected = 16.1169;
		$actual = $mock->applyPriceCoefficients($Amount);
		$this->assertEquals($expected, $actual,'',0.0001);
	}

	public function testAddVat(){
		$mock = new CalculationMock();
		$Amount = 120.47;
		$Rate = 13;
		$expected = 136.1311;

		$actual = $mock->addVat($Amount, $Rate);
		$this->assertEquals($expected, $actual,'',0.0001);
	}

	public function testRemoveVat(){
		$mock = new CalculationMock();
		$Amount = 140.20;
		$Rate = 19;
		$expected = 117.81512605042;

		$actual = $mock->removeVat($Amount, $Rate);
		$this->assertEquals($expected, $actual,'',0.0001);
	}


	/**
	 * @dataProvider providerCalculateItemTax
	 *
	 * @param $PriceItem
	 * @param bool $PriceIncludesVAT
	 * @param bool $IncludeVatType
	 * @param float $expected
	 * @param string $CaseMassage
	 */
	public function testCalculateItemTax($PriceItem, $PriceIncludesVAT, $IncludeVatType, $expected, $CaseMassage){
		$mock = new CalculationMock();

		$CountryCoefficients = (object) array('IncludeVAT'=> $IncludeVatType);
		$mock->setCountryCoefficients($CountryCoefficients);

		/**@var $PriceItem Common\Request\ItemDataRequestInterface */

		$actual = $mock->calculateItemTax($PriceItem,$PriceItem->getOriginalSalePrice(), $PriceIncludesVAT);
		$this->assertEquals($expected, $actual, $CaseMassage,0.0001);
	}


	/**
	 * @dataProvider providerCalculateRawPrice
	 *
	 * @param $priceRequestData
	 * @param $PriceIncludesVAT
	 * @param $UseRounding
	 * @param $IsDiscount
	 * @param $expected
	 * @param $CaseMassage
	 */
	public function testCalculateRawPrice($priceRequestData, $PriceIncludesVAT, $UseRounding, $IsDiscount, $expected,$CaseMassage){
		$mock = new CalculationMock();

		$CountryCoefficients = (object) array('Rate' => 0.93,'IncludeVAT' => 6 );
		$mock->setCountryCoefficients($CountryCoefficients);

		$CurrencyRates = (object) array('Rate' => 1.33356);
		$mock->setCurrencyRates($CurrencyRates);
		$mock->setMaxDecimalPlaces(2);

		/**@var $priceRequestData Common\Request\RawPriceRequestData */

		$actual = $mock->calculateRawPrice($priceRequestData,$priceRequestData->getOriginalSalePrice(), $PriceIncludesVAT, $UseRounding, $IsDiscount);
		$this->assertEquals($expected, $actual, $CaseMassage);

	}


	/**
	 * @dataProvider providerCalculatePrice
	 *
	 * @param $Product
	 * @param $PriceIncludesVAT
	 * @param $expected
	 * @param $CaseMassage
	 */
	public function testCalculatePrice($Product,$PriceIncludesVAT, $expected,$CaseMassage ){
		$mock = new CalculationMock();

		$CountryCoefficients = (object) array('Rate' => 0.97,'IncludeVAT' => 6 );
		$mock->setCountryCoefficients($CountryCoefficients);

		$CurrencyRates = (object) array('Rate' => 1.892);
		$mock->setCurrencyRates($CurrencyRates);
		$mock->setMaxDecimalPlaces(3);

		/**@var $Product Common\Request\ProductRequestData **/
		$actual = $mock->calculatePrice($Product,$Product->getOriginalSalePrice(), $PriceIncludesVAT);
		$this->assertEquals($expected, $actual, $CaseMassage);

	}

	/**
	 *
	 * @dataProvider providerCalculateDataPrice
	 *
	 * @param Common\Request\ItemDataRequestInterface $PriceRequestData
	 * @param $expected
	 * @param $exception
	 * @param $CaseMassage
	 */
	public function testCalculateDataPrice(Common\Request\ItemDataRequestInterface $PriceRequestData, $expected, $exception, $CaseMassage ){

		$mock = new CalculationMock();
		$mock->setMethodReturns(
			array(
				'calculateRawPrice' => 15.44,
				'calculatePrice'	=> 12.71
			)
		);
		$OriginalPrice = 10;
		$PriceIncludesVAT = 12;
		$UseRounding =false;
		$IsDiscount = false;

		try{
			$actual = $mock->calculateDataPrice($PriceRequestData,$OriginalPrice, $PriceIncludesVAT, $UseRounding, $IsDiscount);
			if($exception) {
				$this->fail($CaseMassage);
			}else{
				$this->assertEquals($expected, $actual, $CaseMassage);
			}
		}catch (\Exception $e){
			if($exception) {
				$this->assertEquals($expected, $e->getMessage(), $CaseMassage);
			}else{
				$this->fail($CaseMassage);
			}
		}
	}


	/**
	 * @dataProvider providerCalculateDataPrices
	 *
	 * @param $PriceRequestData
	 * @param $ExpectedResultData
	 * @param $CaseMassage
	 */
	public function testCalculateDataPrices(Common\Request\ItemDataRequestInterface $PriceRequestData, $ExpectedResultData, $CaseMassage  ){
		$mock = new CalculationMock();
		$mock->setMethodReturn('calculateDataPrice', true);

		$CurrencyRates = (object) array('Rate' => 1.892);
		$mock->setCurrencyRates($CurrencyRates);

		$PriceResultData = new RawPriceResponseData();
		$PriceResultData->setMarkedAsFixedPrice($PriceRequestData->getIsFixedPrice());
		$PriceIncludesVAT = true;

		$mock->calculateDataPrices($PriceRequestData, $PriceResultData, $PriceIncludesVAT);

		$this->assertEquals($ExpectedResultData,$PriceResultData,$CaseMassage,0.0001);
	}


	/**
	 * @disc dataProvider for testCalculateDataPrices
	 * @return array
	 */
	public function providerCalculateDataPrices(){

		$PriceRequestData1 = new Common\Request\RawPriceRequestData();
		$PriceRequestData1->setOriginalListPrice(32);
		$PriceRequestData1->setOriginalSalePrice(32);
		$PriceRequestData1->setIsFixedPrice(false);

		$ExpectedResultData1 = new RawPriceResponseData();
		$ExpectedResultData1->setOriginalListPrice(32);
		$ExpectedResultData1->setOriginalSalePrice(27.0613);
		$ExpectedResultData1->setListPrice(51.2);
		$ExpectedResultData1->setSalePrice(51.2);
		$ExpectedResultData1->setMarkedAsFixedPrice(false);

		$PriceRequestData2 = new Common\Request\RawPriceRequestData();
		$PriceRequestData2->setOriginalListPrice(32);
		$PriceRequestData2->setOriginalSalePrice(25);
		$PriceRequestData2->setIsFixedPrice(false);

		$ExpectedResultData2 = new RawPriceResponseData();
		$ExpectedResultData2->setOriginalListPrice(32);
		$ExpectedResultData2->setOriginalSalePrice(21.1416);
		$ExpectedResultData2->setListPrice(51.2);
		$ExpectedResultData2->setSalePrice(40.00);
		$ExpectedResultData2->setMarkedAsFixedPrice(false);

		$PriceRequestData3 = new Common\Request\RawPriceRequestData();
		$PriceRequestData3->setOriginalListPrice(23);
		$PriceRequestData3->setOriginalSalePrice(39.99);
		$PriceRequestData3->setIsFixedPrice(true);

		$ExpectedResultData3 = new RawPriceResponseData();
		$ExpectedResultData3->setOriginalListPrice(23);
		$ExpectedResultData3->setOriginalSalePrice(21.13636);
		$ExpectedResultData3->setListPrice(39.99);
		$ExpectedResultData3->setSalePrice(39.99);
		$ExpectedResultData3->setMarkedAsFixedPrice(true);


		return array(
			array($PriceRequestData1,$ExpectedResultData1,'Case 1 : OriginalListPrice = OriginalSalePrice, not fixed '),
			array($PriceRequestData2,$ExpectedResultData2,'Case 2 : OriginalListPrice != OriginalSalePrice, not fixed '),
			array($PriceRequestData3,$ExpectedResultData3,'Case 3 : Fixed Price ')
		);

	}

	/**
	 *  @disc dataProvider for testCalculateDataPrice
	 * @return array
	 */
	public function providerCalculateDataPrice(){
		return array(
			array(new Common\Request\RawPriceRequestData(), 15.44, false, 'Case 1: PriceRequestData instanceof RawPriceRequestData ' ),
			array(new Common\Request\ProductRequestData(), 12.71, false, 'Case 2: PriceRequestData instanceof ProductRequestData ' ),
			array(new TestCommon\TestRequestData(), 'Unexpected  PriceRequestData type GlobalE\Test\SDK\Models\Common\TestRequestData', true, 'Case 3: PriceRequestData Unexpected type ' )
		);
	}

	/**
	 * @disc dataProvider for testCalculatePrice
	 * @return array
	 */
	public function providerCalculatePrice(){
		return array(
			array($this->buildPriceItem(), false,229.405,'Case 1: PriceIncludesVAT = false '),
			array($this->buildPriceItem(), true,191.171,'Case 2: PriceIncludesVAT = true ')

		);
	}


	/**
	 * @disc dataProvider for testCalculateRawPrice
	 * @return array
	 */
	public function providerCalculateRawPrice(){
		return array(
			array($this->buildRawPrice(), false, false, false, 155.03, 'Case 1: PriceIncludesVAT = false, UseRounding =false,IsDiscount = false '),
			array($this->buildRawPrice(), false, false, true,  166.7,  'Case 2: PriceIncludesVAT = false, UseRounding =false,IsDiscount = true '),
			array($this->buildRawPrice(), false, true,  false, 155.03, 'Case 3: PriceIncludesVAT = false, UseRounding =true,IsDiscount = false '),
			array($this->buildRawPrice(), false, true,  true,  166.7,  'Case 4: PriceIncludesVAT = false, UseRounding =true,IsDiscount = true '),
			array($this->buildRawPrice(), true,  false, false, 129.19, 'Case 5: PriceIncludesVAT = true, UseRounding =false,IsDiscount = false '),
			array($this->buildRawPrice(), true,  false, true,  138.91, 'Case 6: PriceIncludesVAT = true, UseRounding =false,IsDiscount = true '),
			array($this->buildRawPrice(), true,  true,  false, 129.19, 'Case 7: PriceIncludesVAT = true, UseRounding =true,IsDiscount = false '),
			array($this->buildRawPrice(), true,  true,  true,  138.91, 'Case 8: PriceIncludesVAT = true, UseRounding =true,IsDiscount = true ')
		);
	}

	private function buildRawPrice(){
		$RawPrice = new Common\Request\RawPriceRequestData();
		$RawPrice->setOriginalListPrice(100);
		$RawPrice->setOriginalSalePrice(100);
		$RawPrice->setIsFixedPrice(false);
		$RawPrice->setLocalVATRateType(new Common\VatRateType(20,'Local',11));
		$RawPrice->setVATRateType(new Common\VatRateType(25,'GE',22 ));
		return $RawPrice;
	}


	/**
	 * @disc dataProvider for testCalculateItemTax
	 * @return array
	 */
	public function providerCalculateItemTax(){
		return array(
			array($this->buildPriceItem(), true, 0, 83.333333,'Case 1: PriceIncludesVAT = true, $IncludeVatType = 0 '),
			array($this->buildPriceItem(), true, 4, 83.333333,'Case 2: PriceIncludesVAT = true, $IncludeVatType = 4 '),
			array($this->buildPriceItem(), true, 6, 104.166666,'Case 3: PriceIncludesVAT = true, $IncludeVatType = 6 '),
			array($this->buildPriceItem(), false, 0, 100,'Case 4: PriceIncludesVAT = false, $IncludeVatType = 0 '),
			array($this->buildPriceItem(), false, 4, 120,'Case 5: PriceIncludesVAT = false, $IncludeVatType = 4 '),
			array($this->buildPriceItem(), false, 6, 125,'Case 6: PriceIncludesVAT = false, $IncludeVatType = 6 '),

		);
	}

	private function buildPriceItem(){
		$PriceItem = new Common\Request\ProductRequestData();
		$PriceItem->setOriginalListPrice(100);
		$PriceItem->setOriginalSalePrice(100);
		$PriceItem->setLocalVATRateType(new Common\VatRateType(20,'Local',11));
		$PriceItem->setVATRateType(new Common\VatRateType(25,'GE',22 ));
		return $PriceItem;
	}



	private function buildRoundingRule()
	{
		$json = '{
		  "RoundingRuleId": 15,
		  "CurrencyCode": "USD",
		  "CountryCode": "US",
		  "RoundingRanges": [
			{
			  "From": 0,
			  "To": 1,
			  "Threshold": 1,
			  "LowerTarget": 0.99,
			  "UpperTarget": 0.99,
			  "RangeBehavior": 1,
			  "TargetBehaviorHelperValue": 0,
			  "RoundingExceptions": [
				
			  ]
			},
			{
			  "From": 1,
			  "To": 250,
			  "Threshold": 0.39,
			  "LowerTarget": 0.99,
			  "UpperTarget": 0.99,
			  "RangeBehavior": 2,
			  "TargetBehaviorHelperValue": 0,
			  "RoundingExceptions": [
				
			  ]
			},
			{
			  "From": 250,
			  "To": 7.9228162514264e+28,
			  "Threshold": 0.39,
			  "LowerTarget": 1,
			  "UpperTarget": 1,
			  "RangeBehavior": 2,
			  "TargetBehaviorHelperValue": 0,
			  "RoundingExceptions": [
				
			  ]
			}
		  ]
		}';
		return json_decode($json,false);
	}

}