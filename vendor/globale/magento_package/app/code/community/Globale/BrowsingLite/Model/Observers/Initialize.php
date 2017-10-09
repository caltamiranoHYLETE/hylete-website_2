<?php
class Globale_BrowsingLite_Model_Observers_Initialize {

	/**
	 * Add GlobalE_Gem_Data cookies
	 * Event --> customer_session_init frontend
	 * @param Varien_Event_Observer $Observer
	 */
	public function initCookieData(Varien_Event_Observer $Observer){

		/**@var Mage_Customer_Model_Session $CustomerSession */
		$CustomerSession = $Observer->getCustomerSession();
		// Process the action only if Globale_Browsing is off
		if(Mage::helper('core')->isModuleEnabled('Globale_Browsing') == false) {
			Mage::getModel('globale_browsinglite/initializer')->initCookieData($CustomerSession);
		}
	}
}