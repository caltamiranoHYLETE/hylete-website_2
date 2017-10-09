<?php
namespace GlobalE\SDK\Models;

use GlobalE\SDK;
use GlobalE\SDK\Core;
use GlobalE\SDK\Models;
use GlobalE\SDK\API\Processors;

class App {


	/**
	 * Init Customer object according to browsing data ( Cookie / IP )
	 */
	public function initCustomer()
	{

		/**@var $customer Models\Customer */
		$customer = Models\Customer::getSingleton();
		// initialize client IP
		$customer->setIp();
		$GeUrlParams = $customer->getGeUrlParams();
		if ($customer->hasCustomerCookie() && empty($GeUrlParams)) {
			Core\Log::log('Customer information was updated from cookie', Core\Log::LEVEL_DEBUG);
			$customerInfo = $customer->fetchCustomerCookie();
			$customer->setInfo(
				new Common\CustomerInfo(
					$customerInfo->countryISO,
					$customerInfo->currencyCode,
					$customerInfo->cultureCode
				)
			);
		}
		else{
			$customer->initCustomerInfo();
			Core\Log::log('Customer information was updated api calls', Core\Log::LEVEL_DEBUG);
		}

		Core\Log::log("Customer Object : " . Models\Json::encode($customer->getInfo()), Core\Log::LEVEL_INFO);
	}

	/**
	 * Init SDK Settings -> AppVersion
	 */
	public function initAppVersion()
	{
		$ApiParams = new Common\ApiParams();

		//add WebStoreCode and WebStoreInstance if exists
		$WebStoreCode = Core\Settings::get('EnvDetails.WebStoreCode') ;
		$WebStoreInstanceCode = Core\Settings::get('EnvDetails.WebStoreInstanceCode');

		if(!empty($WebStoreCode) && !empty($WebStoreInstanceCode)){
			$AppVersionWebStoreDetails = new Common\Request\AppVersionWebStoreDetails();
			$AppVersionWebStoreDetails->setWebStoreCode($WebStoreCode);
			$AppVersionWebStoreDetails->setWebStoreInstanceCode($WebStoreInstanceCode);
			$ApiParams->setBody((array)$AppVersionWebStoreDetails);
		}

		$AppVersionAPI = new Processors\AppVersion($ApiParams);
		$AppVersion = $AppVersionAPI->processRequest();
		$AppVersion = $AppVersion[0];
		Core\Settings::setBulk($AppVersion, "AppVersion");
		Core\Log::log('call api method AppVersion', Core\Log::LEVEL_DEBUG, array("currencies" => Models\Json::encode($AppVersion)));
	}


	/**
	 * Init SDK Settings -> AppSettings
	 */
	public function initAppSettings(){
		$ApiParams = new Common\ApiParams();
		$AppSettingsAPI = new Processors\AppSettings($ApiParams);
		$AppSettings = $AppSettingsAPI->processRequest();
		$AppSettings = $AppSettings[0];
		Core\Settings::setBulk($AppSettings, "AppSettings");
	}



	/**
	 * Init Base SDK settings ==> SDK::$MerchantVatRateType and SDK::$baseInfo
	 * @param float $VatRate Base VAT Rate to use when SDK initialize.
	 * @param string $BaseCurrency Base Currency code to use when SDK initialize.
	 * @param string $BaseCountry Base Country code to use when SDK initialize.
	 * @param string $BaseCulture Base Culture code to use when SDK initialize.
	 */
	public function initBaseInfo($VatRate = null, $BaseCurrency = null, $BaseCountry = null, $BaseCulture = null){
		//@TODO: set validation for params:  $vatRate, $baseCurrency, $baseCountry, $baseCulture
		// override vatRate param with the new params
		if ($VatRate) {
			SDK\SDK::$MerchantVatRateType = new Common\VatRateType($VatRate, 'DEFAULT', '1');
		}
		// auto fill the CCC variables from Settings.
		if (!$BaseCurrency) {
			$BaseCurrency = Core\Settings::get('Base.Currency');
		}
		if (!$BaseCountry) {
			$BaseCountry = Core\Settings::get('Base.Country');
		}
		if (!$BaseCulture) {
			$BaseCulture = Core\Settings::get('Base.Culture');
		}
		SDK\SDK::$baseInfo = new Common\BaseInfo($BaseCountry, $BaseCurrency, $BaseCulture);
	}


}