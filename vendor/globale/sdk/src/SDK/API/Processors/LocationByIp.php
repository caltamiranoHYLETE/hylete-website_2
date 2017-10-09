<?php
namespace GlobalE\SDK\API\Processors;

use GlobalE\SDK\API;
use GlobalE\SDK\Models\Common;

/**
 * Global-e API method
 * @package GlobalE\SDK\API\Processors
 */
class LocationByIp extends API\Processor {

	/**
	 * API path Browsing/LocationByIP
	 * @var string
	 * @access protected
	 */
	protected $Path = 'Browsing/LocationByIP';

	/**
	 * Name of the common class API should return.
	 * @var string
	 */
	protected $ObjectResponse = "LocationByIp";

	/**
	 * LocationByIp constructor.
	 * @param Common\ApiParams $params
	 * @access public
	 */
	public function __construct(Common\ApiParams $params) {
		$this->setUseCache(false);
		$this->setParams($params);
	}

	/**
	 * Override setParams in order to convert the IP from V4 to V6
	 * @param Common\ApiParams $Params
	 * @access public
	 */
	public function setParams(Common\ApiParams $Params) {

		$ParamsUri = $Params->getUri();
		$Ip = $this->normalizeIpToIPv6($ParamsUri['IP']);
		$Params->setUri(array('IP' => $Ip));

		parent::setParams($Params);
	}

	/**
	 * Check if IP address type IPV4 and need to be converted
	 * @param $ip
	 * @return string
	 * @access protected
	 */
	protected function normalizeIpToIPv6($ip){
		if($this->getIpVersion($ip)!= '6' ){
			$ip = $this->IPv4To6($ip);
		}
		return $ip;
	}


	/**
	 * Decode response data from json string to object
	 * @param string $Response
	 * @param boolean $DecodeBigInt
	 * @return object
	 * @throws \Exception
	 * @access protected
	 */
	protected function decodeResponseData($Response,$DecodeBigInt = true){

		return parent::decodeResponseData($Response,$DecodeBigInt);
	}

	/**
	 * Get the IP address type IPV4/IPV6
	 * @param $ip
	 * @return string
	 * @access protected
	 */
	protected function getIpVersion($ip) {
		return strpos($ip, ":") === false ? '4' : '6';
	}


	/**
	 * Convert IP address from IPV4 to IPV6
	 * @param $Ip string Address in dot notation (192.168.1.100)
	 * @return string string IPv6 formatted address or false if invalid input
	 * @access protected
	 */
	protected function IPv4To6($Ip) {
		static $Mask = '::ffff:'; // This tells IPv6 it has an IPv4 address
		$IPv6 = (strpos($Ip, '::') === 0);
		$IPv4 = (strpos($Ip, '.') > 0);
		if (!$IPv4 && !$IPv6) return false;
		if ($IPv6 && $IPv4) $Ip = substr($Ip, strrpos($Ip, ':')+1); // Strip IPv4 Compatibility notation
		elseif (!$IPv4) return $Ip; // Seems to be IPv6 already?
		$Ip = array_pad(explode('.', $Ip), 4, 0);
		if (count($Ip) > 4) return false;
		for ($i = 0; $i < 4; $i++) if ($Ip[$i] > 255) return false;
		$Part7 = base_convert(($Ip[0] * 256) + $Ip[1], 10, 16);
		$Part8 = base_convert(($Ip[2] * 256) + $Ip[3], 10, 16);
		return $Mask.$Part7.':'.$Part8;
	}
	
}
