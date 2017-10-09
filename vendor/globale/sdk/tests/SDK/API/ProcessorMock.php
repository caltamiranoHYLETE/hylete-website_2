<?php
namespace GlobalE\Test\SDK\API;

use GlobalE\SDK\API;
use GlobalE\Test\MockTrait;
use GlobalE\SDK\Models\Common;

class ProcessorMock extends API\Processor {
	use MockTrait;

	protected $ObjectResponse = "AppSettings";


	public function setApiParams($ApiParams)
	{
		return parent::setApiParams($ApiParams); 
	}

	/**
	 * @param array $Uri
	 * @param $FieldName
	 * @param bool $json_encode
	 * @return string
	 */
	public function formatUrlArray(array &$Uri, $FieldName, $JsonEncode = false){
		return parent::formatUrlArray($Uri, $FieldName, $JsonEncode);
	}

	/**
	 * @return array
	 */
	public function getBody(){
		return parent::getBody();
	}

	/**
	 * @param array $Body
	 * @return ProcessorMock
	 */
	public function setBody($Body){
		parent::setBody($Body);
		return $this;
	}

	/**
	 * @return string
	 */
	public function getUrl(){
		return parent::getUrl();
	}

	/**
	 * @param string $Url
	 * @return ProcessorMock
	 */
	public function setUrl($Url){
		parent::setUrl($Url);
		return $this;
	}


	/**
	 * @return string
	 */
	public function getPath(){
		return $this->Path;
	}

	/**
	 * @param string $Path
	 */
	public function setPath($Path){
		$this->Path = $Path;
	}

	public function getCacheKey(){
		if($this->isMethodReturnExist(__FUNCTION__)){
			return $this->methodReturn(__FUNCTION__);
		}
		return parent::getCacheKey();
	}

	public function formatParameters($Uri){
		if($this->isMethodReturnExist(__FUNCTION__)){
			return $this->methodReturn(__FUNCTION__);
		}
		return parent::formatParameters($Uri);
	}
	public function buildApiBody(array $Body){
		if($this->isMethodReturnExist(__FUNCTION__)){
			return $this->methodReturn(__FUNCTION__);
		}
		parent::buildApiBody($Body);
	}

	public function buildApiUri($Uri) {
		if($this->isMethodReturnExist(__FUNCTION__)){
			return $this->methodReturn(__FUNCTION__);
		}
		parent::buildApiUri($Uri);
	}

	public function  decodeResponseData($response){
		if ($this->isMethodReturnExist(__FUNCTION__)) {
			return $this->methodReturn(__FUNCTION__);
		}
		return parent::decodeResponseData($response);
	}

	public function setParams(Common\ApiParams $params) {
		if ($this->isMethodReturnExist(__FUNCTION__)) {
			return $this->methodReturn(__FUNCTION__);
		}
		parent::setParams($params);
	}

}