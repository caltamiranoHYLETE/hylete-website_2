<?php
namespace GlobalE\SDK\API\Processors;

use GlobalE\SDK\API;
use GlobalE\SDK\Models\Common;
use GlobalE\SDK\Core;

/**
 * Global-e API method
 * @package GlobalE\SDK\API\Processors
 */
class RoundingRules extends API\Processor {

	/**
	 * API path Browsing/RoundingRules
	 * @var string
	 * @access protected
	 */
	protected $Path = 'Browsing/RoundingRules';

	/**
	 * Name of the common class API should return.
	 * @var string
	 */
	protected $ObjectResponse = "RoundingRule";

	/**
	 * RoundingRules constructor.
	 * @param Common\ApiParams $Params
	 * @access public
	 */
	public function __construct(Common\ApiParams $Params) {

		$this->setParams($Params);
	}

	/**
	 * Remove all parameter except merchantGUID in order to get all the rules.
	 * @param array $Uri
	 * @access protected
	 */
	protected function buildApiUri(array $Uri) {

		$Url = Core\Settings::get('API.BaseUrl') . $this->Path . '?' . $this->formatParameters(array('merchantGUID'=>$Uri['merchantGUID']));
		$this->setUrl($Url);
	}

	/**
	 * Return cache using different data combinations
	 * @param $cacheKey string
	 * @return bool|mixed|null|string
	 * @access protected
	 */
	protected function getFromCache($cacheKey){

		$response = false;
		if($this->isUseCache()){
			$response = Core\Cache::get($cacheKey);//call with both CurrencyCode & CountryCode params

			//if no cache : call with CountryCode = null
			if($response === false){
				$Uri = $this->getApiParams()->getUri();
				$currencyCode = $Uri['currencyCode'];
				$countryCode = null;
				$manualCacheKey = $this->createItemCacheKey($countryCode, $currencyCode);
				$response = Core\Cache::get($manualCacheKey);
			}

			//if no cache : call with currencyCode = null
			if($response === false){
				$Uri = $this->getApiParams()->getUri();
				$currencyCode = null;
				$countryCode = $Uri['countryCode'];
				$manualCacheKey = $this->createItemCacheKey($countryCode, $currencyCode);
				$response = Core\Cache::get($manualCacheKey);
			}

			//if no cache : call with currencyCode = null and currencyCode = null
			if($response === false){
				$currencyCode = null;
				$countryCode = null;
				$manualCacheKey = $this->createItemCacheKey($countryCode, $currencyCode);
				$response = Core\Cache::get($manualCacheKey);
			}

		}
		return $response;
	}

	/**
	 * Set response to cache by cache key
	 * Splitting common response according to cache logic.
	 * Saving to cache according to the CurrencyCode and CountryCode from common response
	 * @param string $cacheKey
	 * @param $response
	 * @param $ttl
	 * @access protected
	 */
	protected function setToCache($cacheKey, $response, $ttl){

		foreach ($response AS $key=>$responseItem){
			/**@var $responseItem \stdClass **/
			$currencyCode = $responseItem->CurrencyCode;
			$countryCode =  $responseItem->CountryCode;
			$itemCacheKey = $this->createItemCacheKey($countryCode, $currencyCode);
			parent::setToCache($itemCacheKey, array($responseItem), $ttl);
		}
	}

	/**
	 * Create Cache key according to country code and currency code
	 * @param $countryCode
	 * @param $currencyCode
	 * @return string
	 * @access protected
	 */
	protected function createItemCacheKey($countryCode, $currencyCode){

		$apiParams = clone $this->getApiParams();
		$apiParams->setUri(
			array(
				'currencyCode' => $currencyCode,
				'countryCode' => $countryCode
			)
		);
		return md5(serialize($apiParams));
	}

	/**
	 * Get the item with maximal Priority Level from the full list
	 * @return mixed
	 * @access public
	 */
	public function processRequest() {

		$fullResponse =  parent::processRequest();
		if(empty($fullResponse)){
			return null;
		}
		$matchedItem = $this->matchItem($fullResponse);
		return $matchedItem;
	}

	/**
	 * Get the matched item from response
	 * @param $fullResponse
	 * @return object
	 * @access protected
	 */
	protected function matchItem($fullResponse){

		$matchedLevel = 0;
		$matchedItem = null;

		foreach ($fullResponse AS $responseItem){
			$responseItemLevel = $this->calculatePriorityLevel($responseItem);
			if($responseItemLevel > $matchedLevel){
				$matchedLevel = $responseItemLevel;
				$matchedItem = $responseItem;
			}
		}
		return $matchedItem;
	}

	/**
	 * Calculate priority level
	 * @param object $responseItem
	 * @return int
	 * @access protected
	 */
	protected function calculatePriorityLevel($responseItem){

		$Uri = $this->getApiParams()->getUri();
		$paramCurrencyCode = $Uri['currencyCode'];
		$paramCountryCode = $Uri['countryCode'];

		switch(  array($responseItem->CurrencyCode, $responseItem->CountryCode)){

			case array($paramCurrencyCode, $paramCountryCode):
				$level = 4;
				break;
			case array($paramCurrencyCode, null):
				$level = 3;
				break;
			case array(null, $paramCountryCode):
				$level = 2;
				break;
			case array(null, null):
				$level = 1;
				break;
			default:
				$level = 0;
				break;
		}
		return $level;
	}

}