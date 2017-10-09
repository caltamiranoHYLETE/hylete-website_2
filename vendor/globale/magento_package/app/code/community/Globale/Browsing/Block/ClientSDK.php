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

		$ClientSDKResponse = $GlobaleSDK->Browsing()->LoadClientSDK();
		if($ClientSDKResponse->getSuccess()){
			return $ClientSDKResponse->getData();
		}

       return '<!-- No data from  getGESwitcherJs -->';
    }
}