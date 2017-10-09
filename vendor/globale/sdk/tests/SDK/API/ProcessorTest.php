<?php
namespace GlobalE\Test\SDK\API;

use GlobalE\SDK\Core;
use GlobalE\SDK\Models;
use GlobalE\SDK\Models\Common;
use GlobalE\SDK\Models\Common\Request;
use GlobalE\SDK\API\Common\Response;

class ProcessorTest extends \PHPUnit_Framework_TestCase {
	
	public function testGetCacheKey(){
		$mock = new ProcessorMock();

		$body=array('data'=> "someBody");
		$uri = array(
			'key1'=>'val1',
			'key2'=>'val2'
		);

		$apiParams = new Common\ApiParams();
		$apiParams->setBody($body);
		$apiParams->setUri($uri);

		$expected = md5(serialize($apiParams));

		$mock->setApiParams($apiParams);
		$actual = $mock->getCacheKey();
		$this->assertEquals($expected, $actual);
	}

	public function testFormatParameters(){
		$mock = new ProcessorMock();

		$Uri = array(
			'key1'	=> 'val1',
			'key2'	=>  'val2',
			'key3'	=> '$'
		);
		$expected = 'key1=val1&key2=val2&key3=%24';
		$actual = $mock->formatParameters($Uri);
		$this->assertEquals($expected, $actual);
	}

	public function testBuildApiBody(){
		$mock = new ProcessorMock();

		//case 1: count($Body) > 0
		$body = array(
			'key1'	=> 'val1',
			'key2'	=>  'val2'
		);
		$expected = Models\Json::encode($body);
		$mock->buildApiBody($body);
		$this->assertEquals($expected, $mock->getBody(),'case 1: count($Body) > 0');

		//case 2: count($Body) == 0
		$body = array();
		$expected = '';
		$mock->buildApiBody($body);
		$this->assertEquals($expected, $mock->getBody(),'case 1: count($Body) == 0');
	}

	public function testBuildApiUri(){
		$mock = new ProcessorMock();
		$mock->setMethodReturn('formatParameters','action=sdk');
		$mock->setPath('Browsing/Something');
		
		$expected = Core\Settings::get('API.BaseUrl').'Browsing/Something?action=sdk';
		$mock->buildApiUri(array());
		$this->assertEquals($expected,$mock->getUrl());
	}

	public function testDecodeResponseData(){
		$mock = new ProcessorMock();
		$response = json_encode(
			array(
				'ClientSettings'	=> 'val1',
				'ServerSettings'	=>  'val2'
			)
		);
		$AppSettingsCommon = new Response\AppSettings();
		$AppSettingsCommon->setClientSettings('val1');
		$AppSettingsCommon->setServerSettings('val2');

		$expected = array($AppSettingsCommon);
		$actual = $mock->decodeResponseData($response);
		$this->assertEquals($expected, $actual);
	}

	public function testSetParams(){
		$mock = new ProcessorMock();
		$mock->setPath('Path/Path');
		$params = new Common\ApiParams();
		$params->setBody(array('body'=>'body'))->setUri(array('key' => 'value'));

		$mock->setParams($params);
		$this->assertEquals('Path/Path',$mock->getPath() );
		$this->assertEquals('{"body":"body"}',$mock->getBody());
		$this->assertEquals(Core\Settings::get('API.BaseUrl').'Path/Path?merchantGUID='.Core\Settings::get('MerchantGUID').'&key=value',$mock->getUrl());
	}

	/**
	 * @dataProvider providerFormatUrlArray
	 * @param array $Uri
	 * @param string $FieldName
	 * @param boolean $JsonEncode
	 * @param string $Expected
	 */
	public function testFormatUrlArray(array $Uri, $FieldName, $JsonEncode, $Expected){

		$ProcessorMock = new ProcessorMock();
		$Actual = $ProcessorMock->formatUrlArray($Uri, $FieldName, $JsonEncode);
		$this->assertEquals($Expected,$Actual );
	}

	public function providerFormatUrlArray(){

		$Parcel = new Request\Parcel();
		$Parcel->setParcelCode('1');

		return array(

			// ### TEST 1 ###
			array(
				array(
					'Key' => 'Value',
					'Array' => array('Value1','Value2','Value3')
				),
				'Array',
				false,
				'&Array=Value1&Array=Value2&Array=Value3' // EXPECTED RESULT
			),

			// ### TEST 2 ###
			array(
				array(
					'Key' => 'Value',
					'Parcel' => array($Parcel,$Parcel,$Parcel)
				),
				'Parcel',
				true,
				'&Parcel=%7B%22ParcelCode%22%3A%221%22%7D&Parcel=%7B%22ParcelCode%22%3A%221%22%7D&Parcel=%7B%22ParcelCode%22%3A%221%22%7D' // EXPECTED RESULT
			),

			// ### TEST 3 ###
			array(
				array(
					'Key' => 'Value',
					'Parcel' => array($Parcel,$Parcel,$Parcel)
				),
				'Parcel123',
				true,
				'' // EXPECTED RESULT
			)

		);
	}



	

}
