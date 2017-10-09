<?php
use GlobalE\SDK\SDK;
use GlobalE\SDK\Core;

class Globale_Base_Model_Initializer extends Mage_Core_Model_Abstract {

    /**
     * Initialize SDK and Magento primary settings
     */
    public function initializeSDK(){

        if(Mage::registry('globale_initialized') === true){
            return;
        }

        $this->initBaseSettings();

		$GlobaleSDK = $this->getSDKObject();
		if ($GlobaleSDK === null) {
			return;
		}

		$this->initCache();

        Mage::register('globale_initialized', true);

		$ExtensionVersion = (string)Mage::getConfig()->getNode('modules/Globale_Base/version');
		Core\Log::log('MagentoV1 version '.$ExtensionVersion, Core\Log::LEVEL_INFO);
    }


	/**
	 * Initialize SDK object, add it to register.
	 * @return SDK|null
	 */
    protected function getSDKObject(){

		try{
			if(Mage::registry('globale_sdk')){
				$GlobaleSDK = Mage::registry('globale_sdk');
			}else{
				$GlobaleSDK = new SDK();
				Mage::register('globale_sdk', $GlobaleSDK);
			}

		}catch (Exception $E){
			Mage::register('globale_user_supported', false);
			$GlobaleSDK = null;
		}

		return $GlobaleSDK;
	}








    /**
     * Initialize SDK and Magento primary settings for REST API
     * @return boolean if succeed
     */
    public function initializeSDKRestMode(){

        $this->initBaseSettings(true);

		$GlobaleSDK = $this->getSDKObject();

		if ($GlobaleSDK === null) {
			return false;
		}


        Mage::register('globale_initialized', true);
        Mage::register('globale_user_supported', false);
        Mage::register('globale_api', true);

        return true;

    }


    /**
     * initialize SDK settings
     * @param boolean $RestMode if call from API REST
     */
    protected function initBaseSettings($RestMode = false){
        /**@var $BaseSetting Globale_Base_Model_Settings */
        $BaseSetting = Mage::getModel('globale_base/settings');
        $BaseSetting->updateGlobaleSdkSettings($RestMode);
    }

    /**
     * Pass magento cache object to sdk, and update settings according to what is set in admin
     */
    protected function initCache(){
        Core\Cache::setExternalCacheObject(Mage::app()->getCache());
        $cacheEnabledInAdmin = Mage::app()->useCache('globale');
        Core\Settings::set('Cache.Enable', $cacheEnabledInAdmin);
        if($cacheEnabledInAdmin){
            Core\Settings::set('Cache.Type', 'Magento1');
        }
    }





}