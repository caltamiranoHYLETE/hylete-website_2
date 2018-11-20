<?php
class Globale_BrowsingLite_Model_Observers_Initialize {

	/**
	 * Add GlobalE_Gem_Data cookies
	 * Event --> customer_session_init frontend
	 * @param Varien_Event_Observer $Observer
	 */
	public function initCookieData(Varien_Event_Observer $Observer){

		$Customer = null;

		if ($Observer->getCustomerSession() && $Observer->getCustomerSession()->getCustomer()) {
			// trigerred by `customer_session_init`
			/** @var Mage_Customer_Model_Customer $Customer */
			$Customer = $Observer->getCustomerSession()->getCustomer();
		} elseif (Mage::getSingleton('checkout/session')) {
			// trigerred by `checkout_cart_save_after`
			$Customer = Mage::getSingleton('checkout/session')->getQuote()->getCustomer();
		}

		if(Mage::helper('core')->isModuleEnabled('Globale_Browsing') == true) {
			Mage::getModel('globale_browsinglite/initializer')->initGemCookieData($Customer);
		}
	}
}