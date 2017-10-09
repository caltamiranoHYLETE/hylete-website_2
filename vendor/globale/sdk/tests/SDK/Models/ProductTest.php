<?php
namespace GlobalE\Test\SDK\Models;

use GlobalE\SDK\Models\Common;
use GlobalE\SDK\Core;
use GlobalE\SDK\Models\Common\Request;

class ProductTest extends \PHPUnit_Framework_TestCase
{
	
	public function testInitCurrencyRules(){
		$mock = new ProductMock(array());
		$mock->initCurrencyRules();

		$actual = $mock->getCalculationModel()->getCurrencyRates()->Rate;
		$expected = 1.346579823;
		$this->assertEquals($expected, $actual, 'Stub Currency rates check', 0.00001);

		$actual = $mock->getCalculationModel()->getMaxDecimalPlaces();
		$expected = 2;
		$this->assertEquals($expected, $actual, 'Stub Max Decimal Places check');
	}
	
	
	public function testInitCountryRules(){
		$mock = new ProductMock(array());
		$CountryModel = $mock->getCountryModel();
		/**@var $CountryModel CountryMock */
		$CountryModel->setMethodReturn('fetchCountryCoefficients', $this->fetchCountryCoefficients() );
		$mock->initCountryRules();


		$actual = $mock->getCalculationModel()->getRoundingRule()->RoundingRuleId;
		$expected = 25; //EUR
		$this->assertEquals($expected, $actual, 'Rounding Rule check');

		$actual = $mock->getCalculationModel()->getCountryCoefficients()->Rate;
		$expected = 0.93;
		$this->assertEquals($expected, $actual, 'Country Coefficients check');
	}

	private function fetchCountryCoefficients(){
		return json_decode('{"Rate": 0.930000,"IncludeVAT": 6,"CountryCode": "DE","ProductClassCode": null}');
	}


	/**
	 * @dataProvider providerCalculateProductVat
	 *
	 * @param Request\ProductRequestData $Product
	 * @param bool $UseCountryVAT
	 * @param Common\VatRateType | null $ProductVATRateType
	 * @param Common\VatRateType | null $CountryVatRateType
	 * @param Request\ProductRequestData $Expected
	 * @param string $Massage
	 */
	public function testCalculateProductVat($Product,$UseCountryVAT,$ProductVATRateType,$CountryVatRateType,$Expected,$Massage){
		$mock = new ProductMock(array($Product));
		/**@var $countryModel CountryMock */
		$countryModel = $mock->getCountryModel();
		$countryModel->getCountry()->UseCountryVAT = $UseCountryVAT ;
		$countryModel->nullifyVatRateType();
		if($CountryVatRateType){
			$countryModel->setVatRateType($CountryVatRateType);
		}
		$mock->getCountryModel();
		$mock->setProductCountry(
			array(
				$Product->getProductCode() => (object)array('VATRateType' => $ProductVATRateType)
			)
		);

		$Actual = $mock->calculateProductVat($Product);
		$this->assertEquals($Expected, $Actual, $Massage);

	}


	public function providerCalculateProductVat()
	{
		$expectedArray = $this->buildExpectedArray();


		return array(
			array(
				$this->buildProduct(false),
				false,
				$this->buildProductVatRate(),
				$this->buildCountryVatRate(),
				$expectedArray[1],
				'Product without Local VatRates, UseCountryVAT = false'
			),
			array(
				$this->buildProduct(true),
				false,
				$this->buildProductVatRate(),
				$this->buildCountryVatRate(),
				$expectedArray[2],
				'Product has Local VatRates, UseCountryVAT = false'
			),
			array(
				$this->buildProduct(true),
				true,
				null,
				null,
				$expectedArray[3],
				'Product has Local VatRates, UseCountryVAT = true, NO ProductVat and NO CountryVat  '
			),
			array(
				$this->buildProduct(true),
				true,
				$this->buildProductVatRate(),
				$this->buildCountryVatRate(),
				$expectedArray[4],
				'Product has Local VatRates, UseCountryVAT = true, has ProductVat and CountryVat  '
			),
			array(
				$this->buildProduct(true),
				true,
				null,
				$this->buildCountryVatRate(),
				$expectedArray[5],
				'Product has Local VatRates, UseCountryVAT = true, NO ProductVat but has CountryVat  '
			),

		);
	}


	private function buildProductVatRate() {
		return (object)array(
			'Rate' => 25,
			'Name' => 'ProductVat',
			'VATRateTypeCode' => "130"
		);
	}

	private function buildCountryVatRate(){
		return new Common\VatRateType(22,'Country' ,"225");
	}

	private function buildExpectedArray(){
		$ExpectedArray = array();

		$ExpectedArray[1] = new Request\ProductRequestData();
		$ExpectedArray[1]->setProductCode('sku0121');
		$ExpectedArray[1]->setOriginalListPrice(100);
		$ExpectedArray[1]->setIsFixedPrice(false);
		$ExpectedArray[1]->setLocalVATRateType(new Common\VatRateType(20,'UK_INTERNAL',"1"));
		$ExpectedArray[1]->setVATRateType(new Common\VatRateType(20,'UK_INTERNAL',"1"));

		$ExpectedArray[2] = new Request\ProductRequestData();
		$ExpectedArray[2]->setProductCode('sku0121');
		$ExpectedArray[2]->setOriginalListPrice(100);
		$ExpectedArray[2]->setIsFixedPrice(false);
		$ExpectedArray[2]->setLocalVATRateType(new Common\VatRateType(20,'Local',"11"));
		$ExpectedArray[2]->setVATRateType(new Common\VatRateType(20,'Local',"11"));

		$ExpectedArray[3] = new Request\ProductRequestData();
		$ExpectedArray[3]->setProductCode('sku0121');
		$ExpectedArray[3]->setOriginalListPrice(100);
		$ExpectedArray[3]->setIsFixedPrice(false);
		$ExpectedArray[3]->setLocalVATRateType(new Common\VatRateType(20,'Local',"11"));
		$ExpectedArray[3]->setVATRateType(new Common\VatRateType(20,'Local',"11"));

		$ExpectedArray[4] = new Request\ProductRequestData();
		$ExpectedArray[4]->setProductCode('sku0121');
		$ExpectedArray[4]->setOriginalListPrice(100);
		$ExpectedArray[4]->setIsFixedPrice(false);
		$ExpectedArray[4]->setLocalVATRateType(new Common\VatRateType(20,'Local',"11"));
		$ExpectedArray[4]->setVATRateType(new Common\VatRateType(25,'ProductVat',"130"));

		$ExpectedArray[5] = new Request\ProductRequestData();
		$ExpectedArray[5]->setProductCode('sku0121');
		$ExpectedArray[5]->setOriginalListPrice(100);
		$ExpectedArray[5]->setIsFixedPrice(false);
		$ExpectedArray[5]->setLocalVATRateType(new Common\VatRateType(20,'Local',"11"));
		$ExpectedArray[5]->setVATRateType(new Common\VatRateType(22,'Country',"225"));


		return $ExpectedArray;

	}

	private function buildProduct($HasLocalVatRateType = true, $sku = 'sku0121' ){
		$Product = new Request\ProductRequestData();
		$Product->setProductCode($sku);
		$Product->setOriginalListPrice(100);
		$Product->setIsFixedPrice(false);
		$Product->setVATRateType(new Common\VatRateType(25,'GE',"22" ));

		if($HasLocalVatRateType){
			$Product->setLocalVATRateType(new Common\VatRateType(20,'Local',"11"));
		}
		return $Product;
	}





	public function testGetCachedProductCountry(){
		
		$mock = new ProductMock(array(
			$this->buildProduct(true,'sku123'),
			$this->buildProduct(true,'sku456')
		));

		$key1 = $this->getCacheKeyForProduct('sku123');
		Core\Cache::clear($key1);
		$key2 = $this->getCacheKeyForProduct('sku456');
		Core\Cache::clear($key2);


		$NonCached = array();
		// Case 1 : No cached products
		$actual = $mock->getCachedProductCountry($NonCached);
		$this->assertEmpty($actual,' Case 1 : No cached products - cached data');

		$expectedNonCached = array('sku123','sku456');
		$this->assertEquals($expectedNonCached, $NonCached,' Case 1 : No cached products - non  cached data');


		// Case 2 : has One cached product
		$NonCached = array();

		$key1 = $this->getCacheKeyForProduct('sku123');
		$data1 = '{
			"ProductCode": "sku123",
			"VATRateType": {
			  "VATRateTypeCode": "Global-e_13",
			  "Name": "Germany (DE) Default VAT",
			  "Rate": 19.000000,
			  "InternalVATRateTypeId": 13
			},
			"CountryCode": "DE",
			"IsRestricted": false,
			"RestrictionMessage": "",
			"IsVerified": false,
			"TTL": 86400,
			"IsForbidden": false,
			"ForbiddenMessage": ""
		  }';

		$productData = json_decode($data1);
		Core\Cache::set($key1,$productData,125);

		$expectedNonCached = array('sku456');

		$expected = array('sku123'=>$productData);


		$actual = $mock->getCachedProductCountry($NonCached);
		$this->assertEquals($expectedNonCached, $NonCached,' Case 1 : No cached products - non  cached data');

		$this->assertEquals($expected, $actual);
		Core\Cache::clear($key1);
	}

	protected function getCacheKeyForProduct($ProductCode)
	{
		$ApiParams = new Common\ApiParams();
		$ApiParams->setUri(array
			(
				'merchantGUID' => Core\Settings::get('MerchantGUID'),
				'countryCode' => "DE",
				'cultureCode' => "de",
				'productCode' => $ProductCode
			)
		);
		$ApiParams->setPath('Browsing/ProductCountryS');

		$key = md5(serialize($ApiParams));
		return $key;

	}
	
//@TODO ==> tests for getProductCountryAPIResults , buildProductsInformationResult

}