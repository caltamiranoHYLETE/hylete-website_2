<?php
use GlobalE\SDK\Models;

/**
 * Manage the popup country/currency switcher javascript
 * Class Globale_Browsing_Block_SwitcherJS
 */
class Globale_Browsing_Block_ClientSDK extends Mage_Core_Block_Template {

    /**
     * Get the Switcher Javascript script from the SDK
     * @return string
     * @access public
     */
    public function loadClientSdkJS() {

    	/**@var $GlobaleSDK \GlobalE\SDK\SDK */
        $GlobaleSDK = Mage::registry('globale_sdk');

		$SwitcherInBlockingMode = (boolean)Mage::registry('globale_switcher_in_blocking_mode');
		$ClientSDKResponse = $GlobaleSDK->Browsing()->LoadClientSDK($SwitcherInBlockingMode);
		if($ClientSDKResponse->getSuccess()){
			return $ClientSDKResponse->getData();
		}

       return '<!-- No data from  getGESwitcherJs -->';
    }

	/**
	 * Get Base store currency code
	 * @return string
	 */
    public function getStoreBaseCurrencyCode(){
		return Mage::app()->getStore()->getBaseCurrencyCode();
	}

	/**
	 * Get Base URL of default site
	 * @return string
	 */
	public function getAdminBaseUrl(){
		return Mage::app()->getStore(0)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
	}

	/**
	 * Load RedirectSuffixList from setting and convert to JSON
	 * @return string
	 */
    public function loadRedirectSuffixList(){
    	$RedirectSuffixList = Mage::getModel('globale_browsing/redirect')->getFullSuffixesList();
		$JsRedirectSuffixList = json_encode($RedirectSuffixList);
		return $JsRedirectSuffixList;
	}

	/**
	 * Load settings of keep_original_uri_on_redirect
	 * @return bool
	 */
	public function loadKeepOriginalUri(){
		$KeepOriginalSetting = Mage::getModel('globale_base/settings')->getKeepOriginalUri();
		return (boolean)$KeepOriginalSetting;
	}
}