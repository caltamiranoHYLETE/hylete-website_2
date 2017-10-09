<?php
namespace GlobalE\Test\SDK\API\Processors;

use GlobalE\SDK\API\Processors;
use GlobalE\Test\MockTrait;

class ProductCountrySMock extends Processors\ProductCountryS
{
	use MockTrait;

	public function formatParameters(array $Uri){

		return parent::formatParameters($Uri);
	}

	public function setToCache($cacheKey, $response, $ttl)
	{
		parent::setToCache($cacheKey, $response, $ttl);
	}

	public function createItemCacheKey($productCode){
		return parent::createItemCacheKey($productCode);
	}
	
	public function getFromCache($cacheKey)
	{
		return parent::getFromCache($cacheKey); 
	}

}