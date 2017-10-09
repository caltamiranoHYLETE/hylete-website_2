<?php

use GlobalE\SDK\SDK;
use GlobalE\SDK\Models;
use GlobalE\SDK\Models\Common\Response;

class Globale_Browsing_Model_Initializer
{
	const ALLOW_REDIRECTS_CONFIG = 'globale_settings/browsing_settings/allow_redirects';

	const NOT_ALLOW_REDIRECTS = 0;
	const REDIRECT_TYPE_301 = 1;
	const REDIRECT_TYPE_302 = 2;

	/**
	 *  Initialize SDK and Magento Browsing settings
	 *  Event ==> controller_front_init_before - frontend
	 */
	public function initBrowsingSDK()	{

		$GlobaleSDK = Mage::registry('globale_sdk');
		//if SDK object not initialized - return
		if (!$GlobaleSDK) {
			return;
		}

		$OnPageLoadSucceed = $this->OnPageLoad($GlobaleSDK);
		if ($OnPageLoadSucceed === false) {
			return;
		}

		$AllowRedirects = Mage::getStoreConfig(self::ALLOW_REDIRECTS_CONFIG);
		if ($AllowRedirects != self::NOT_ALLOW_REDIRECTS) {
			$this->HandleGlobaleRedirects($GlobaleSDK, $AllowRedirects);
		}


		$this->initIsUserSupportedByGlobale($GlobaleSDK);
		$this->initCurrentCurrency($GlobaleSDK);

       $this->disableListedModulesOutput();

	}


	/**
	 * call to SDK onPageLoad functionality
	 * @param SDK $GlobaleSDK
	 * @return bool
	 */
	public function OnPageLoad(SDK $GlobaleSDK)	{

		$LoadResponse = $GlobaleSDK->Browsing()->OnPageLoad();
		if (!$LoadResponse->getSuccess()) {
			Mage::register('globale_user_supported', false);
			return false;
		}
		return true;
	}

	/**
	 * Add globale_user_supported to Magento register
	 * @param SDK $GlobaleSDK
	 */
	protected function initIsUserSupportedByGlobale(SDK $GlobaleSDK) {

		$UserSupportedByGlobaleResponse = $GlobaleSDK->Browsing()->IsUserSupportedByGlobale();
		if ($UserSupportedByGlobaleResponse->getSuccess()) {
			/**@var $UserSupportedByGlobaleResponse Response\Data */
			$UserSupportedByGlobale = $UserSupportedByGlobaleResponse->getData();
			Mage::register('globale_user_supported', $UserSupportedByGlobale);
		}
	}


	/**
	 * Handle redirect according to redirect type setting
	 * @param SDK $GlobaleSDK
	 * @param $RedirectType
	 */
	public function HandleGlobaleRedirects(SDK $GlobaleSDK, $RedirectType)	{
		//redirecting based on SiteURL returned from Global-e Country API
		$CountryObj = $GlobaleSDK->Browsing()->GetCountries();
		if ($CountryObj->getSuccess()) {
			/**@var $CountryObj Response\Data */
			/**@var $CountryObjData Models\Country */
			$CountryObjData = $CountryObj->getData();
			$CurrentCountry = $CountryObjData->getCountry();
			$CurrentCountrySiteURL = preg_replace('/https?:\/\/(www\.)?/', '', rtrim($CurrentCountry->SiteURL, '/'));
			$CurrentBaseUrl = Mage::helper('core/url')->getCurrentUrl();
			$CurrentUrl = preg_replace('/https?:\/\/(www\.)?/', '', rtrim($CurrentBaseUrl, '/'));

			if (!empty($CurrentCountrySiteURL) && strpos($CurrentUrl, rtrim($CurrentCountrySiteURL, '/')) === false) {
				$CurrentUrlParts = explode('/', $CurrentUrl);
				$CurrentUrlStoreCode = $CurrentUrlParts[1];
				$CurrentCountryUrlParts = explode('/', $CurrentCountrySiteURL);
				$CurrentCountryUrlStoreCode = $CurrentCountryUrlParts[1];
				if (!empty($CurrentUrlStoreCode)) {
					$RedirectUrl = str_replace($CurrentUrlStoreCode, $CurrentCountryUrlStoreCode, $CurrentBaseUrl);
				} else {
					if (substr($CurrentBaseUrl, -1) == '/') {
						$RedirectUrl = $CurrentBaseUrl . $CurrentCountryUrlStoreCode . '/';
					} else {
						$RedirectUrl = $CurrentBaseUrl . '/' . $CurrentCountryUrlStoreCode . '/';
					}
				}


				// Switch between redirects types
				switch ($RedirectType) {
					case self::REDIRECT_TYPE_301:
						$RedirectTypeValue = 301;
						break;

					case self::REDIRECT_TYPE_302:
						$RedirectTypeValue = 302;
						break;

					default:
						//
						return;
				}

				header("Location: " . $RedirectUrl, TRUE, $RedirectTypeValue);
				exit();
			}
		}
	}

	/**
	 * initialize Magento Current Currency from SDK
	 * @param SDK $GlobaleSDK
	 **/
	protected function initCurrentCurrency($GlobaleSDK)
	{

		/**@var $Currency Globale_Browsing_Model_Currency */
		$Currency = Mage::getModel('globale_browsing/currency');
		$Currency->initCurrentCurrency($GlobaleSDK);
	}

	/**
	 * Disable module list output for modules selected in Global-e Settings and if(globale_user_supported == true)
	 */
	protected function disableListedModulesOutput(){
		if(Mage::registry('globale_user_supported')){
			/**@var $Setting Globale_Base_Model_Settings */
			$Setting = Mage::getModel('globale_base/settings');
			$Modules = explode(',', Mage::getStoreConfig($Setting::MODULES_DISABLE_OUTPUT));

			foreach ($Modules as $Module) {
				// Disable its output as well (which was already loaded)
				$OutputPath = "advanced/modules_disable_output/{$Module}";
				if (!Mage::getStoreConfig($OutputPath)) {
					Mage::app()->getStore()->setConfig($OutputPath, true);
				}
			}
		}
	}


}