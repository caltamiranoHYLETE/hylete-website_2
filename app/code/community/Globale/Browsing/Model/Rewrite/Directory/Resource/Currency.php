<?php

/**
 * Class Globale_Browsing_Model_Resource_Currency
 * Rewrite original currency resource to use Global-e currency logic
 */
class Globale_Browsing_Model_Rewrite_Directory_Resource_Currency extends Mage_Directory_Model_Resource_Currency {
	/**
	 * @desc getRate for Global-e prices return 1
	 *
	 * @param Mage_Directory_Model_Currency|string $currencyFrom
	 * @param Mage_Directory_Model_Currency|string $currencyTo
	 * @return float|int
	 */
	public function getRate($currencyFrom, $currencyTo){
		$UserSupportedByGlobale = Mage::registry('globale_user_supported');
		if($UserSupportedByGlobale && !Mage::app()->getStore()->isAdmin()){
			return 1;
		}
		return parent::getRate($currencyFrom, $currencyTo);
	}


	/**
	 * @desc getAnyRate for Global-e prices return 1
	 *
	 * @param Mage_Directory_Model_Currency|string $currencyFrom
	 * @param Mage_Directory_Model_Currency|string $currencyTo
	 * @return float|int
	 */
	public function getAnyRate($currencyFrom, $currencyTo){
		$UserSupportedByGlobale = Mage::registry('globale_user_supported');
		if($UserSupportedByGlobale && !Mage::app()->getStore()->isAdmin()){
			return 1;
		}
		return parent::getAnyRate($currencyFrom, $currencyTo);
	}


	/**
	 * Rewrite allowed currencies by installed
	 *
	 * @param Mage_Directory_Model_Currency $model
	 * @param string $path
	 * @return array
	 */
	public function getConfigCurrencies($model, $path){
		$UserSupportedByGlobale = Mage::registry('globale_user_supported');

		if($UserSupportedByGlobale && !Mage::app()->getStore()->isAdmin() &&
			$path == Mage_Directory_Model_Currency::XML_PATH_CURRENCY_ALLOW ){

			$path = Mage_Core_Model_Locale::XML_PATH_ALLOW_CURRENCIES_INSTALLED;
		}
		return parent::getConfigCurrencies($model, $path);
	}


	/**
	 * @param string $code
	 * @param null $toCurrencies
	 * @return array
	 */
	protected function _getRatesByCode($code, $toCurrencies = null)	{
		$UserSupportedByGlobale = Mage::registry('globale_user_supported');

		if($UserSupportedByGlobale == false || Mage::app()->getStore()->isAdmin()){
			return parent::_getRatesByCode($code, $toCurrencies);
		}

		$result = array();
		foreach ($toCurrencies as $Currency){
			$result[$Currency] = 1;
		}
		return $result;
	}


}