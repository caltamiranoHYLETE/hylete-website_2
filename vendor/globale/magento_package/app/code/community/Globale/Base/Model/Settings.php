<?php

use GlobalE\SDK\Core;

/**
 * Get all settings from magento Global-e settings
 */
class Globale_Base_Model_Settings extends Mage_Core_Model_Abstract {

    const MERCHANT_ID                = 'globale_settings/api_settings/merchant_id';
    const MERCHANT_GUID              = 'globale_settings/api_settings/merchant_guid';
	const API_BASE_URL               = 'globale_settings/api_settings/api_base_url';
	const API_LOG_ENABLE             = 'globale_settings/api_settings/log_enabled';
	const API_LOG_PATH               = 'globale_settings/api_settings/log_path';
	const GEM_BASE_URL               = 'globale_settings/api_settings/gem_base_url';
	const CLIENT_BASE_URL            = 'globale_settings/browsing_settings/client_base_url';
    const REWRITE_CURRENCY_SWITCHER  = 'globale_settings/browsing_settings/currency_switcher';
    const MODULES_DISABLE_OUTPUT     = 'globale_settings/browsing_settings/modules_disable_output';
    const JS_ON_SUCCESS              = 'globale_settings/checkout_settings/js_on_success';
    const EXT_ORDER_ID               = 'globale_settings/checkout_settings/ext_order_id';

    const ENGLISH_STORE_ID           = 'globale_settings/api_settings/english_store_id';
    const IS_LOGIN_BEFORE_CHECKOUT   = 'globale_settings/browsing_settings/login_before_checkout';
    const INTERNATIONAL_PAYMENT_STATUS  = 'globale_settings/international_payment/order_status';

    const CATALOG_PRICE_AS_FIXED     = 'globale_settings/products_settings/catalog_price_as_fixed_prices';


    /**
     * Get Global-e API settings from magento configuration settings and save them in the SDK settings.
	 * @param boolean $RestMode - if call in api REST mode
     */
    public function updateGlobaleSdkSettings($RestMode) {

        Core\Settings::set('MerchantID', Mage::getStoreConfig(self::MERCHANT_ID));
        Core\Settings::set('MerchantGUID', Mage::getStoreConfig(self::MERCHANT_GUID));
        Core\Settings::set('API.BaseUrl', Mage::getStoreConfig(self::API_BASE_URL));
        Core\Settings::set('Frontend.BaseUrl', Mage::getStoreConfig(self::CLIENT_BASE_URL));
        Core\Settings::set('Base.Country', Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_COUNTRY ));
        Core\Settings::set('Log.Enable', (bool)Mage::getStoreConfig(self::API_LOG_ENABLE));
        Core\Settings::set('Log.Path', Mage::getBaseDir() . Mage::getStoreConfig(self::API_LOG_PATH));
        if(Mage::getStoreConfig(Mage_Core_Model_Cookie::XML_PATH_COOKIE_DOMAIN ) != ''){
            Core\Settings::set('Cookies.Domain', Mage::getStoreConfig(Mage_Core_Model_Cookie::XML_PATH_COOKIE_DOMAIN));
        }
        if(Mage::getStoreConfig(Mage_Core_Model_Cookie::XML_PATH_COOKIE_PATH ) != ''){
            Core\Settings::set('Cookies.Path', Mage::getStoreConfig(Mage_Core_Model_Cookie::XML_PATH_COOKIE_PATH));
        }
		Core\Settings::set('Base.Currency', Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE));

		//WebStoreCode Details
		Core\Settings::set('EnvDetails.WebStoreCode', Mage::app()->getStore()->getCode());

		$UnsecuredDefaultBaseUrl = (string)Mage::getConfig()->getNode(Mage_Core_Model_Store::XML_PATH_UNSECURE_BASE_URL, Mage_Core_Model_Store::DEFAULT_CODE);
		Core\Settings::set('EnvDetails.WebStoreInstanceCode', $UnsecuredDefaultBaseUrl);

        if($RestMode){
			Core\Settings::set('Base.Culture', Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE));

		}else{
			Core\Settings::set('Base.Culture', Mage::app()->getLocale()->getLocaleCode());
		}
    }

    public function getJsOnSuccess(){
        return Mage::getStoreConfig(self::JS_ON_SUCCESS);
    }

    public function useExtOrderId(){
        return Mage::getStoreConfig(self::EXT_ORDER_ID);
    }

	/**
	 * Get english_store_id
	 * @return int | null
	 */
    public function getEnglishStoreId(){
		return Mage::getStoreConfig(self::ENGLISH_STORE_ID);

	}

	/**
	 * get CatalogPriceAsFixedPrice flag
	 * @return int | null
	 */
    public function getCatalogPriceAsFixedPrice(){
    	return Mage::getStoreConfig( self::CATALOG_PRICE_AS_FIXED);
	}

    /**
     * Get settings configuration, if the customer
     * has to pass the login page before checkout
     * @return bool
     */
    public function getIsAllowLoginBeforeCheckout() {
        return Mage::getStoreConfig(self::IS_LOGIN_BEFORE_CHECKOUT);
    }

    /**
     * Get settings configuration, get order payment status
     * @return string
     */
    public function getInternationalPaymentStatus() {
        return Mage::getStoreConfig(self::INTERNATIONAL_PAYMENT_STATUS);
    }

}