<?php
namespace GlobalE\Test\SDK\API\Processors;

use GlobalE\SDK\Models\Common;
use GlobalE\SDK\Core;

class RoundingRulesTest extends \PHPUnit_Framework_TestCase {
	
	/**
	 * @dataProvider providerCalculatePriorityLevel
	 * @param $responseItem
	 * @param $expectedLevel
	 */
	public function testCalculatePriorityLevel($responseItem, $expectedLevel){

		$params = $this->createApiParams();
		$mock = new RoundingRulesMock($params) ;
		$actualLevel = $mock->calculatePriorityLevel($responseItem);
		$this->assertEquals($expectedLevel, $actualLevel);
	}

	public function testMatchItem(){
		$params = $this->createApiParams();
		$mock = new RoundingRulesMock($params) ;
		$mock->setMethodReturn('calculatePriorityLevel','');

		$fullResponse  = array(
			array('id'=>1 , 'level'=> 0),
			array('id'=>2 , 'level'=> 3),
			array('id'=>3 , 'level'=> 4),
			array('id'=>4 , 'level'=> 1)
		);
		$expected = array('id'=>3 , 'level'=> 4);

		$actual = $mock->matchItem($fullResponse);
		$this->assertEquals($expected, $actual);
	}

	public function testCreateItemCacheKey(){
		$params = $this->createApiParams();
		$mock = new RoundingRulesMock($params) ;

		$countryCode = 'GB';
		$currencyCode = 'USD';

		$paramsNew = clone $params;
		$paramsNew->setUri(
			array(
				'countryCode' => $countryCode,
				'currencyCode' => $currencyCode,
			)
		);
		$expected = md5(serialize($paramsNew));

		$actual = $mock->createItemCacheKey($countryCode, $currencyCode);
		$this->assertEquals($expected, $actual);
	}

	public function testSetToCache(){

		$params = $this->createApiParams();
		$mock = new RoundingRulesMock($params);

		$std_class1 = (object) array(
			'CurrencyCode'   => 'AUD',
			'CountryCode'    => 'AU',
			'RoundingRuleId' => 18,
			'RoundingRanges' => array('key1'=>'value1')
		);

		$std_class2 = (object) array(
			'CurrencyCode'   => 'ZMW',
			'CountryCode'    => null,
			'RoundingRuleId' => 29,
			'RoundingRanges' => array('key22'=>'value22')
		);
		$response = array( $std_class1,$std_class2);
		$mock->setToCache('someCashe', $response , 1500);


		$CacheKey1 =  $mock->createItemCacheKey('AU','AUD');
		$actual1 = $mock->_getFromCache($CacheKey1);
		$this->assertEquals(array($std_class1), $actual1, 'First Item test failed');

		$CacheKey2 =  $mock->createItemCacheKey(null,'ZMW');
		$actual2 = $mock->_getFromCache($CacheKey2);
		$this->assertEquals(array($std_class2), $actual2, 'Second Item test failed ');
	}

	public function testGetFromCache (){
		$params = $this->createApiParams();
		$mock = new RoundingRulesMock($params);
		$cacheKey = $mock->createItemCacheKey('IL','ILS' );

		//case 1 : no cache
		$mock->setUseCache(false);
		$actual = $mock->getFromCache($cacheKey);
		$this->assertFalse($actual,'Case 1 : cache disable ');

		$mock->setUseCache(true);


		$std_class1 = (object) array(
			'CurrencyCode'   => 'ILS',
			'CountryCode'    => 'IL',
			'RoundingRuleId' => 18,
			'RoundingRanges' => array('key18'=>'value18')
		);

		$std_class2 = (object) array(
			'CurrencyCode'   => 'ILS',
			'CountryCode'    => null,
			'RoundingRuleId' => 29,
			'RoundingRanges' => array('key29'=>'value29')
		);

		$std_class3 = (object) array(
			'CurrencyCode'   => null,
			'CountryCode'    => 'IL',
			'RoundingRuleId' => 298,
			'RoundingRanges' => array('key298'=>'value298')
		);
		$std_class4 = (object) array(
			'CurrencyCode'   => null,
			'CountryCode'    => null,
			'RoundingRuleId' => 1,
			'RoundingRanges' => array('key1'=>'value1')
		);
		$response = array( $std_class1,$std_class2,$std_class3,$std_class4);
		$mock->setToCache('someCashe', $response , 1500);


		//case 2 : Cached record has both countryCode and currencyCode => 'IL','ILS'
		$expected = array($std_class1);
		$actual = $mock->getFromCache($cacheKey);
		$this->assertEquals($expected, $actual,' Case 2: Cached record has both CountryCode and CurrencyCode ' );
		
		//case 3 : Cached record with CountryCode = null => null,'ILS'

		//clear the  'IL','ILS' record
		Core\Cache::clear($cacheKey);

		$expected = array($std_class2);
		$actual = $mock->getFromCache($cacheKey);
		$this->assertEquals($expected, $actual,' Case 3: Cached record with CountryCode = null ');

		//case 4 : Cached record with currencyCode = null => 'IL', null

		//clear the null,'ILS' record
		$cacheKey2 = $mock->createItemCacheKey(null,'ILS' );
		Core\Cache::clear($cacheKey2);

		$expected = array($std_class3);
		$actual = $mock->getFromCache($cacheKey);
		$this->assertEquals($expected, $actual,' Case 4: Cached record with CurrencyCode = null ');

		//case 5: Cached record has countryCode and currencyCode == null

		//clear the 'IL', null record
		$cacheKey3 = $mock->createItemCacheKey('IL',null );
		Core\Cache::clear($cacheKey3);

		$expected = array($std_class4);
		$actual = $mock->getFromCache($cacheKey);
		$this->assertEquals($expected, $actual,' Case 5: Cached record with CurrencyCode/CountryCode = null ');

		$cacheKey4 = $mock->createItemCacheKey(null,null );
		Core\Cache::clear($cacheKey4);

	}

	public function testBuildApiUri(){
		$params = $this->createApiParams();
		$mock = new RoundingRulesMock($params);
		$mock->buildApiUri($params->getUri());
		$expected = Core\Settings::get('API.BaseUrl') .'Browsing/RoundingRules?merchantGUID='.Core\Settings::get('MerchantGUID');
		$actual = $mock->getUrl();
		$this->assertEquals($expected, $actual);
	}


	/**
	 * @return Common\ApiParams
	 */
	private function createApiParams(){
		$Uri = array(
			'countryCode' => 'IL',
			'currencyCode' => 'ILS'
		);

		$params = new Common\ApiParams();
		$params->setUri($Uri);
		return $params;
	}


	/**
	 * @disc dataProvider
	 * @return array
	 */
	public function providerCalculatePriorityLevel() {
		return array(
			array(
				(object)array('CurrencyCode' => 'ILS', 'CountryCode' => 'IL') ,
				4
			),
			array(
				(object)array('CurrencyCode' => 'ILS', 'CountryCode' => null) ,
				3
			),
			array(
				(object)array('CurrencyCode' => null, 'CountryCode' => 'IL') ,
				2
			),
			array(
				(object)array('CurrencyCode' => null, 'CountryCode' => null) ,
				1
			),
			array(
				(object)array('CurrencyCode' => 'GBP', 'CountryCode' => 'GB') ,
				0
			)

		);
	}

}