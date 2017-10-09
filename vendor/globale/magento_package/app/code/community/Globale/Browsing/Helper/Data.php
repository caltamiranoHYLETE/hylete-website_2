<?php

use GlobalE\SDK;

class Globale_Browsing_Helper_Data extends Mage_Core_Helper_Abstract {

	/**
	 * Beauty Amount according to Global-e rules
	 * @param $Amount
	 * @param $UseRounding
	 * @return mixed
	 */
	public function getBeautyAmount($Amount,$UseRounding){

		$BeautyAmount = $Amount;
		/**@var $GlobaleSDK SDK\SDK */
		$GlobaleSDK = Mage::registry('globale_sdk');
		$BeautyAmountResponse = $GlobaleSDK->Browsing()->GetBeautyAmount($Amount,$UseRounding);

		if($BeautyAmountResponse->getSuccess()){
			$BeautyAmount = $BeautyAmountResponse->getData();
		}
		return $BeautyAmount;
	}
}