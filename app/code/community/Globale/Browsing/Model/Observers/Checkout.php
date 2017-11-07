<?php
class Globale_Browsing_Model_Observers_Checkout {


	/**
	 * Process Redirect to GE checkout if rout exist in ext_checkout_rout_list setting for GE users
	 * Event ==> controller_action_predispatch
	 * @param Varien_Event_Observer $Observer
	 */
	public function redirectToGlobaleCheckout(Varien_Event_Observer $Observer){
		if(Mage::registry('globale_user_supported')){
			/**@var $RedirectModel Globale_Browsing_Model_Redirect */
			$RedirectModel = Mage::getModel('globale_browsing/redirect');
			$RedirectModel->redirectToGlobaleCheckout($Observer);
		}

	}
}