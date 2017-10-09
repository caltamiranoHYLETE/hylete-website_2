<?php
namespace GlobalE\SDK\API\Processors;

use GlobalE\SDK\API;
use GlobalE\SDK\Models\Common;

/**
 * Global-e API method
 * @package GlobalE\SDK\API\Processors
 */
class ProductCountryS extends API\Processor {

	/**
	 * API path Browsing/ProductCountryS
	 * @var string
	 * @access protected
	 */
	protected $Path = 'Browsing/ProductCountryS';

	/**
	 * Name of the common class API should return.
	 * @var string
	 */
	protected $ObjectResponse = "ProductCountry";

	/**
	 * ProductCountryS constructor.
	 * @param Common\ApiParams $Params
	 * @access public
	 */
	public function __construct(Common\ApiParams $Params) {
		$this->setParams($Params);
	}

	/**
	 * Get data from cache
	 * @return bool|mixed|null|string
	 * @access public
	 */
	public function getDataFromCache()	{
		if($this->isUseCache()){
			$cacheKey = $this->getCacheKey();
			return parent::getFromCache($cacheKey);
		}
		return false;
	}

	/**
	 * Create HTTP query URI from parameters in unconventional way, using the same GET parameter name
	 * @param array $Uri
	 * @return string
	 * @access protected
	 */
	protected function formatParameters(array $Uri)	{

		$BulkProductsUrlString = $this->formatUrlArray($Uri, 'productCode');
		$urlString = parent::formatParameters($Uri);
		$urlString .= $BulkProductsUrlString;

		return $urlString;
	}


	/**
	 * Splitting common response according to cache logic
	 * @param string $cacheKey
	 * @param $response
	 * @param $ttl
	 * @access protected
	 */
	protected function setToCache($cacheKey, $response, $ttl)
	{
		foreach ($response AS $responseItem){
			/**@var $responseItem \stdClass **/
			$productCode = $responseItem->ProductCode;
			$ttl = $responseItem->TTL;
			$itemCacheKey = $this->createItemCacheKey($productCode);
			parent::setToCache($itemCacheKey, $responseItem, $ttl);
		}
	}

	/**
	 * Create Cache key according to product code
	 * @param string $productCode
	 * @return string
	 * @access protected
	 */
	protected function createItemCacheKey($productCode){

		$apiParams = clone $this->getApiParams();
		$apiParams->setUri(array('productCode' => $productCode ));
		return md5(serialize($apiParams));
	}


}