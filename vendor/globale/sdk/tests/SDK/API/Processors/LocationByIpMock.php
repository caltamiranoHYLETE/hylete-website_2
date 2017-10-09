<?php
namespace GlobalE\Test\SDK\API\Processors;

use GlobalE\SDK\API\Processors;
use GlobalE\Test\MockTrait;

class LocationByIpMock extends Processors\LocationByIp {
	use MockTrait;

	public function IPv4To6($Ip) {
		if($this->isMethodReturnExist(__FUNCTION__)){
			return $this->methodReturn(__FUNCTION__);
		}
		return parent::IPv4To6($Ip);
	}

	public function getIpVersion($ip) {
		if($this->isMethodReturnExist(__FUNCTION__)){
			return $this->methodReturn(__FUNCTION__);
		}
		return parent::getIpVersion($ip);
	}

	public function normalizeIpToIPv6($ip){
		if($this->isMethodReturnExist(__FUNCTION__)){
			return $this->methodReturn(__FUNCTION__);
		}
		return parent::normalizeIpToIPv6($ip);
	}

	public function getBody(){
		return parent::getBody();
	}
	public function getUrl(){
		return parent::getUrl();
	}

	public function getPath(){
		return $this->Path;
	}


}