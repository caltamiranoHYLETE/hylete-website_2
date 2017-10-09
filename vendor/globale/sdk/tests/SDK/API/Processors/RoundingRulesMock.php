<?php
namespace GlobalE\Test\SDK\API\Processors;

use GlobalE\SDK\API\Processors;
use GlobalE\Test\MockTrait;
use GlobalE\SDK\Core;

class RoundingRulesMock extends Processors\RoundingRules {
	use MockTrait;
	
	public function calculatePriorityLevel($responseItem)
	{
		if($this->isMethodReturnExist(__FUNCTION__)){
			return  $responseItem['level'];
		}
		return parent::calculatePriorityLevel($responseItem);
	}

	public function matchItem($fullResponse)
	{
		return parent::matchItem($fullResponse);
	}
	
	public function createItemCacheKey($countryCode, $currencyCode)
	{
		return parent::createItemCacheKey($countryCode, $currencyCode); 
	}

	public function setToCache($cacheKey, $response, $ttl)
	{
		parent::setToCache($cacheKey, $response, $ttl); 
	}

	public function _getFromCache($cacheKey){
		$response = Core\Cache::get($cacheKey);
		return $response;
	}
	
	public function setUseCache($UseCache)
	{
		return parent::setUseCache($UseCache);
	}

	public function getFromCache($cacheKey)
	{
		return parent::getFromCache($cacheKey); 
	}

	public function buildApiUri(array $Uri)
	{
		parent::buildApiUri($Uri); 
	}
	
	public function getUrl()
	{
		return parent::getUrl(); 
	}
	
}