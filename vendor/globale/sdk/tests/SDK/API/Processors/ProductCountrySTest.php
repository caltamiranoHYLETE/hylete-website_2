<?php
namespace GlobalE\Test\SDK\API\Processors;

use GlobalE\SDK\Models\Common;
use GlobalE\SDK\Core;


class ProductCountrySTest extends \PHPUnit_Framework_TestCase {

	public function testFormatParameters(){


		$params = $this->createApiParams();
		$mock = new ProductCountrySMock($params);

		//case 1 : One productCode check
		$Uri = $params->getUri();
		$expected = 'merchantGUID='.Core\Settings::get('MerchantGUID').'&countryCode=IL&cultureCode=en-GB&productCode=acj0006s';
		$actual = $mock->formatParameters($Uri);
		$this->assertEquals($expected, $actual, 'case 1 : One productCode check failed');

		//case 2 : multi products check
		$Uri = array(
			'countryCode' => 'IL',
			'cultureCode' => 'en-GB',
			'productCode'=> array('acj0006s','msj013','msj012','acj0009s')
		);

		$expected = 'countryCode=IL&cultureCode=en-GB&productCode=acj0006s&productCode=msj013&productCode=msj012&productCode=acj0009s';
		$actual = $mock->formatParameters($Uri);
		$this->assertEquals($expected, $actual, 'case 2 : Multi products check  failed');
	}

	/**
	 * @return Common\ApiParams
	 */
	private function createApiParams(){
		$Uri = array(
			'countryCode' => 'IL',
			'cultureCode' => 'en-GB',
			'productCode'=> 'acj0006s'
		);

		$params = new Common\ApiParams();
		$params->setUri($Uri);
		return $params;
	}

	public function testSetToCache(){

		$params = $this->createApiParams();
		$mock = new ProductCountrySMock($params);
		
		$std_class1 = (object) array(
			'ProductCode' => 'acj0007s',
			'TTL'         => '86400',
			'SomeKey'     => 'SomeData acj0006s fff'
		);

		$std_class2 = (object) array(
			'ProductCode' => 'msj013',
			'TTL'         => '86400',
			'SomeKey'     => 'SomeData msj013 456'
		);

		$std_class3 = (object) array(
			'ProductCode' => 'msj012',
			'TTL'         => '86400',
			'SomeKey'     => 'SomeData for msj012'
		);

		$response = array( $std_class1,$std_class2,$std_class3);
		$mock->setToCache('someCashe', $response , 150);

		$CacheKey1 =  $mock->createItemCacheKey('acj0007s');
		$actual1 = $mock->getFromCache($CacheKey1);
		$this->assertEquals($std_class1, $actual1, ' Product Item acj0006s ');

		$CacheKey2 =  $mock->createItemCacheKey('msj013');
		$actual2 = $mock->getFromCache($CacheKey2);
		$this->assertEquals($std_class2, $actual2, ' Product Item msj013 ');

		$CacheKey3 =  $mock->createItemCacheKey('msj012');
		$actual3 = $mock->getFromCache($CacheKey3);
		$this->assertEquals($std_class3, $actual3, ' Product Item msj012 ');
	}
	
	public function testCreateItemCacheKey(){
		$params = $this->createApiParams();
		$mock = new ProductCountrySMock($params);

		$productCode = 'bgb675 dg@55';

		$paramsNew = clone $params;
		$paramsNew->setUri(array('productCode' => $productCode ));
		$expected = md5(serialize($paramsNew));

		$actual = $mock->createItemCacheKey($productCode);
		$this->assertEquals($expected, $actual);
	}

}