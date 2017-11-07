<?php

use GlobalE\SDK\SDK;
use GlobalE\SDK\Models;
use GlobalE\SDK\Models\Common\Response;

class Globale_Browsing_Model_Redirect extends Mage_Core_Model_Abstract {

	const NOT_ALLOW_REDIRECTS = 0;
	const REDIRECT_TYPE_301   = 1;
	const REDIRECT_TYPE_302   = 2;
	const BLOCKING_MODE_TYPE  = 3;


	/**
	 * Handle redirect according to Setting (Server side redirect / Switcher Blocking mode)
	 * @param SDK $GlobaleSDK
	 */
	public function handleRedirect(SDK $GlobaleSDK){
		$RedirectType = Mage::getModel('globale_base/settings')->getAllowServerRedirects();

		if ($RedirectType) {
			$RedirectUrl = $this->getRedirectUrl($GlobaleSDK);

			if($RedirectUrl){
				if ($RedirectType == self::BLOCKING_MODE_TYPE) {
					//Usage in Globale_Browsing_Block_ClientSDK::loadClientSdkJS
					Mage::register('globale_switcher_in_blocking_mode', true);

				} else {
					$RedirectResponseCode = $this->getRedirectResponseCode($RedirectType);
					if ($RedirectResponseCode) {
						$this->redirectToUrl($RedirectUrl, $RedirectResponseCode);
					}
				}
			}
		}
	}

	/**
	 * Handle redirect according to redirect type setting
	 * @param SDK $GlobaleSDK
	 * @return string | null $RedirectUrl
	 */
	protected function getRedirectUrl(SDK $GlobaleSDK)	{

		//redirect Non AJAX pages only
		if(Mage::app()->getRequest()->isAjax()){
			return null;
		}

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

			$FullSuffixesList = $this->getFullSuffixesList();

			$BaseStoreUrlFull = Mage::app()->getStore(0)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
			$BaseStoreUrl = preg_replace('/https?:\/\/(www\.)?/', '', rtrim($BaseStoreUrlFull, '/'));

			$CurrentUrlSuffix =  $this->getUrlStoreSuffix($CurrentUrl,$BaseStoreUrl,$FullSuffixesList);
			$CurrentCountrySiteSuffix =  $this->getUrlStoreSuffix($CurrentCountrySiteURL,$BaseStoreUrl,$FullSuffixesList);

			if (!empty($CurrentCountrySiteURL) && $CurrentCountrySiteSuffix !== null && $CurrentUrlSuffix != $CurrentCountrySiteSuffix ) {

				//Default redirect value is Homepage
				$Request = Mage::app()->getRequest();
				$RedirectUrl = $Request->getScheme() . '://' . $Request->getHttpHost().'/'.$CurrentCountrySiteSuffix;

				$KeepOriginalSetting = Mage::getModel('globale_base/settings')->getKeepOriginalUri();

				//If KEEP_ORIGINAL_URI setting is false - will redirect to Homepage (default redirect)
				//otherwise will build $RedirectUrl
				if($KeepOriginalSetting){


					if(!empty($CurrentUrlSuffix) && !empty($CurrentCountrySiteSuffix)){
						// from example--> site.com/eu/linen-blazer-590.html
						// to example--> site.com/us/linen-blazer-590.html
						$RedirectUrl = str_replace($BaseStoreUrl.'/'.$CurrentUrlSuffix, $BaseStoreUrl.'/'.$CurrentCountrySiteSuffix, $CurrentBaseUrl);

					}elseif (!empty($CurrentUrlSuffix) && empty($CurrentCountrySiteSuffix)){
						// from example--> site.com/eu/linen-blazer-590.html
						// to example--> site.com/linen-blazer-590.html
						$RedirectUrl = str_replace($BaseStoreUrl.'/'.$CurrentUrlSuffix, $BaseStoreUrl, $CurrentBaseUrl);

					}elseif(empty($CurrentUrlSuffix) && !empty($CurrentCountrySiteSuffix)){
						// from example--> site.com/linen-blazer-590.html
						// to example--> site.com/us/linen-blazer-590.html
						$RedirectUrl = str_replace($BaseStoreUrl, $BaseStoreUrl.'/'.$CurrentCountrySiteSuffix, $CurrentBaseUrl);
					}
				}

				return $RedirectUrl;
			}
		}
		return null;
	}


	/**
	 * @param $RedirectType
	 * @return int|null
	 */
	protected function getRedirectResponseCode($RedirectType){
		// Switch between redirects types
		switch ($RedirectType) {
			case self::REDIRECT_TYPE_301:
				$RedirectTypeValue = 301;
				break;

			case self::REDIRECT_TYPE_302:
				$RedirectTypeValue = 302;
				break;

			default:
				$RedirectTypeValue = null;
		}
		return $RedirectTypeValue;
	}

	/**
	 * Magento Redirect To URL with HttpCode
	 * @param $Url
	 * @param $HttpCode
	 */
	protected function redirectToUrl($Url,$HttpCode ){
		Mage::app()->getResponse()
			->setRedirect($Url,$HttpCode)
			->setHeader('Cache-Control', 'no-cache', true)
			->setHeader('Pragma:', 'no-cache', true);
	}


	/**
	 * Find suffix of Url according to SuffixList
	 * Example -> site.com/us/men/suits.html?... -> us
	 * @param $Url
	 * @param $BaseStoreUrl
	 * @param $FullSuffixesList
	 * @return string | null (if error)
	 */
	protected function getUrlStoreSuffix($Url,$BaseStoreUrl,$FullSuffixesList){

		$UrlString = str_replace($BaseStoreUrl,'',$Url);
		$UrlString = ltrim($UrlString,'/');

		$UrlParts = explode('/', $UrlString);

		// site.com/us --> after removing site.com/ ==>'us'  is a part 0
		$StoreCodePartOrder = 0;


		if(!isset($UrlParts[$StoreCodePartOrder])){ // homepage example -> site.com
			$CurrentUrlStoreCode = '';

		} elseif (in_array($UrlParts[$StoreCodePartOrder],$FullSuffixesList)){ // suffix in array
			$CurrentUrlStoreCode = $UrlParts[$StoreCodePartOrder];

		}elseif(in_array('',$FullSuffixesList)){ // site.com/men/linen-blazer-590.html
			$CurrentUrlStoreCode = '';

		}else {
			//error case - empty suffix doesn't exist in the list, but current suffix is unknown
			$CurrentUrlStoreCode = null;
		}
		return $CurrentUrlStoreCode;
	}

	/**
	 * Get the list of all possible suffixes of folder (Multi-sites)
	 * Example ==> site.com , site.com/us , site.com/eu ==> 'eu,us,'
	 * return Full Suffixes array
	 * @return array
	 */
	public function getFullSuffixesList(){

		$FullSuffixesList = array();
		//	'/en,/,'   then remove /
		// supported stores
		$FullSuffixesListString = Mage::getModel('globale_base/settings')->getSupportedStoreList();

		if (!empty($FullSuffixesListString)) {
			$FullSuffixesList = explode(',', $FullSuffixesListString);
			foreach ($FullSuffixesList AS &$Suffix){
				$Suffix = trim($Suffix);
				$Suffix = ltrim($Suffix,'/');
			}
		}

		return $FullSuffixesList;
	}



	/**
	 * Process Redirect to GE checkout if rout exist in ext_checkout_rout_list setting for GE users
	 * Event ==> controller_action_predispatch
	 * @param Varien_Event_Observer $Observer
	 */
	public function redirectToGlobaleCheckout(Varien_Event_Observer $Observer){

		$CheckoutRoutsList = Mage::getModel('globale_base/settings')->getNativeCheckoutRoutesList();

		if(Mage::registry('globale_user_supported') && !empty($CheckoutRoutsList)){

			/**@var $ControllerAction Mage_Core_Controller_Front_Action */
			$ControllerAction = $Observer->getEvent()->getControllerAction();
			$RouteName = $ControllerAction->getRequest()->getRouteName();
			$CheckoutRoutes = explode(',',$CheckoutRoutsList);

			if(in_array($RouteName,$CheckoutRoutes)){
				$GlobaleCheckoutPageURL = Mage::helper('globale_browsing/checkout')->getGlobaleCheckoutPageURL();

				if($GlobaleCheckoutPageURL){
					$this->redirectToUrl($GlobaleCheckoutPageURL, 302);
				}
			}
		}
	}
}