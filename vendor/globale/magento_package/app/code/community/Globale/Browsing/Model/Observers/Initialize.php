<?php
class Globale_Browsing_Model_Observers_Initialize {


	/**
	 * Initialize SDK and Magento Browsing settings
	 * Event ==> controller_front_init_before - frontend
	 */
	public function initBrowsingSDK(){
		/**@var $CurrencyModel Globale_Browsing_Model_Initializer */
		$InitializerModel = Mage::getModel('globale_browsing/initializer');
		$InitializerModel->initBrowsingSDK();
	}

}