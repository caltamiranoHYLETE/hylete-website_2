<?php
class Globale_DataProvider_Helper_Feed {

	/**
	 * Initialize Feed Mode
	 * @param string $Country
	 * @param string $Currency
	 */
	public function initFeedMode($Country, $Currency){
		$_GET['glCountry'] = $Country;
		$_GET['glCurrency'] = $Currency;

		/**@var $Initializer Globale_Base_Model_Initializer */
		$Initializer = Mage::getModel('globale_base/initializer');
		$Initializer->initializeSDK();

		/**@var $CurrencyModel Globale_Browsing_Model_Initializer */
		$InitializerModel = Mage::getModel('globale_browsing/initializer');
		$InitializerModel->initBrowsingSDK();

	}

}