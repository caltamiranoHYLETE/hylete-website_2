<?php
class Globale_BrowsingLite_Model_Initializer extends Mage_Core_Model_Abstract {

	const COOKIE_NAME_GEM_DATA = 'GlobalE_Gem_Data';

	/**
	 * Update GlobalE_Gem_Data cookie
	 * @param Mage_Customer_Model_Session $CustomerSession
	 */
	public function initCookieData(Mage_Customer_Model_Session $CustomerSession){
				//Collect data
		$UserId = $CustomerSession->getId();
		$UserId = !empty($UserId) ? $UserId : 0;

		// prepare Gem Data
		$CartID = Mage::getSingleton('checkout/session')->getQuoteId();
		$CartID = !empty($CartID) ? $CartID : 0;

		$StoreCode = Mage::app()->getStore()->getCode();
		$PreferedCulture = Mage::app()->getLocale()->getLocaleCode();

		$DataArray = array(
			'CartID' => $CartID,
			'UserId' => $UserId,
			'PreferedCulture' => $PreferedCulture,
			'StoreCode' => $StoreCode
		);

		$GemData = Mage::helper('core')->jsonEncode($DataArray);

		// check if cookie exists
		$ExistingCookieValue = Mage::getModel('core/cookie')->get(self::COOKIE_NAME_GEM_DATA);

		if($ExistingCookieValue != $GemData){
			// update cookie
			Mage::getSingleton('core/cookie')->set(self::COOKIE_NAME_GEM_DATA, $GemData, 0, '/', null, null, false );
		}
	}
}