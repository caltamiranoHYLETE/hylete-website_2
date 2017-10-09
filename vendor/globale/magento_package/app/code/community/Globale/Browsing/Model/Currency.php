<?php

use GlobalE\SDK\SDK;
use GlobalE\SDK\Models;
use GlobalE\SDK\Models\Common\Response;
use GlobalE\SDK\API\Common\Response\Currency;

class Globale_Browsing_Model_Currency {

	const DEFAULT_MAX_DECIMAL_PLACES = 2;

	/**
	 * @param SDK $GlobaleSDK
	 * Initialize SDK Current Currency in Magento
	 */
	public function initCurrentCurrency(SDK $GlobaleSDK){

		//if SDK object not initialized OR User not operated - don't init switcher currency
		if(!$GlobaleSDK || Mage::registry('globale_user_supported') == false ){
			return;
		}

		// change store currency by the current currency from the SDK.
		$CurrentCurrencyResponse = $GlobaleSDK->Browsing()->GetCurrencies();
		if($CurrentCurrencyResponse->getSuccess()){

			/**@var $CurrentCurrencyResponse Response\Data */
			$CurrentCurrencyResponseData = $CurrentCurrencyResponse->getData();

			/**@var $CurrentCurrencyResponseData Models\Currency */
			$CurrentCurrency = $CurrentCurrencyResponseData->getCurrentCurrency();
			if (null !== $CurrentCurrency) {
				// if customer session active, otherwise no current currency will be found (like in admin)
				$this->initMaxDecimalPlaces($CurrentCurrency);
			}

			$Store = Mage::app()->getStore();
			$AllowedCurrency = $Store->getAvailableCurrencyCodes(true);
			if(!in_array($CurrentCurrency->Code,$AllowedCurrency)){
				$AllowedCurrency[] = $CurrentCurrency->Code;
				$Store->setData('available_currency_codes',$AllowedCurrency);
			}

			$Store->setCurrentCurrencyCode($CurrentCurrency->Code);
		}
	}


	/**
	 * Init Max Decimal Places to Magento registry
	 * @param Currency $CurrentCurrency
	 */
	protected function initMaxDecimalPlaces(Currency $CurrentCurrency){

		if (null !== $CurrentCurrency) {
			// if customer sessions is active
			if (Mage::registry('globale_max_decimal_places') === null) {
				if ($CurrentCurrency->getMaxDecimalPlaces() !== null) {
					$DecimalPlaces = $CurrentCurrency->getMaxDecimalPlaces();
				} else {
					$DecimalPlaces = self::DEFAULT_MAX_DECIMAL_PLACES;
				}
				Mage::register('globale_max_decimal_places', $DecimalPlaces);
			}
		}
	}

}