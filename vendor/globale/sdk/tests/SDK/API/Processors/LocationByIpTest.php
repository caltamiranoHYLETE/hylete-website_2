<?php
namespace GlobalE\Test\SDK\API\Processors;

use GlobalE\SDK\Models\Common;
use GlobalE\SDK\Core;

class LocationByIpTest extends \PHPUnit_Framework_TestCase{

	/**
	 * @dataProvider providerIpListIPv4To6
	 * @param $IPv4
	 * @param $IPv6
	 */
	public function testIPv4To6($IPv4,$IPv6){

		$params = new Common\ApiParams();
		$params->setUri(array('IP' => '212.143.40.246'));
		$mock = new LocationByIpMock($params);
		$actual = $mock->IPv4To6($IPv4);

		$this->assertEquals($IPv6,$actual);
	}

	/**
	 * @param $ip
	 * @param $version
	 * @dataProvider providerGetIpVersion
	 */
	public function testGetIpVersion($ip,$version){
		$params = new Common\ApiParams();
		$params->setUri(array('IP' => '212.143.40.246'));
		$mock = new LocationByIpMock($params);
		$actual = $mock->getIpVersion($ip);

		$this->assertEquals($version,$actual);
	}


	/**
	 * @dataProvider providerNormalizeIpToIPv6
	 * @param $ip
	 * @param $version
	 * @param $ipv6
	 */
	public function testNormalizeIpToIPv6($ip,$ipv6){
		$params = new Common\ApiParams();
		$params->setUri(array('IP' => '212.143.40.246'));
		$mock = new LocationByIpMock($params);
		$actual = $mock->normalizeIpToIPv6($ip);
		$this->assertEquals($ipv6,$actual);
	}

	
	public function testSetParams(){
		$params = new Common\ApiParams();
		$params->setUri(array('IP' => '212.143.40.246'));

		$mock = new LocationByIpMock($params);
		$mock->setParams($params);

		$this->assertFalse($mock->isUseCache());
		$this->assertEquals('Browsing/LocationByIP',$mock->getPath());
		$this->assertEquals(Core\Settings::get('API.BaseUrl').'Browsing/LocationByIP?merchantGUID='.Core\Settings::get('MerchantGUID').'&IP=%3A%3Affff%3Ad48f%3A28f6',$mock->getUrl());
		$this->assertEmpty($mock->getBody());
	}


	/**
	 * @disc dataProvider
	 * @return array
	 */
	public function providerIpListIPv4To6(){
		return array(
			array('212.143.40.246','::ffff:d48f:28f6'),
			array('83.194.54.233','::ffff:53c2:36e9'),
			array('52.40.193.216','::ffff:3428:c1d8'),
		);
	}

	/**
	 * @dics dataProvider
	 * @return array
	 */
	public function providerGetIpVersion(){
		return array(
			array('212.143.40.246','4'),
			array('2a01:258:8:2::4','6'),
			array('52.40.193.216','4'),
			array('ddddddddd','4'),
			array('bu:bu','6')
		);
	}


	/**
	 * @dics dataProvider
	 * @return array
	 */
	public function providerNormalizeIpToIPv6(){
		return array(
			array('212.143.40.246','::ffff:d48f:28f6'),
			array('::ffff:3428:c1d8','::ffff:3428:c1d8')
		);
	}



}
