<?php
use GlobalE\SDK\SDK;
use GlobalE\SDK\Models\Common\Response;

class Globale_Browsing_Model_Initializer
{
	/**
	 *  Initialize SDK and Magento Browsing settings
	 *  Event ==> controller_front_init_before - frontend
	 */
	public function initBrowsingSDK()	{

		$GlobaleSDK = Mage::registry('globale_sdk');
		//if SDK object not initialized - return
		if (!$GlobaleSDK) {
			return;
		}

		$OnPageLoadSucceed = $this->OnPageLoad($GlobaleSDK);
		if ($OnPageLoadSucceed === false) {
			return;
		}


		/**@var $RedirectModel Globale_Browsing_Model_Redirect */
		$RedirectModel = Mage::getModel('globale_browsing/redirect');
		$RedirectModel->handleRedirect($GlobaleSDK);



		$this->initIsUserSupportedByGlobale($GlobaleSDK);
		$this->initCurrentCurrency($GlobaleSDK);

       $this->disableListedModulesOutput();

	}


	/**
	 * call to SDK onPageLoad functionality
	 * @param SDK $GlobaleSDK
	 * @return bool
	 */
	public function OnPageLoad(SDK $GlobaleSDK)	{

		$LoadResponse = $GlobaleSDK->Browsing()->OnPageLoad();
		if (!$LoadResponse->getSuccess()) {
			Mage::register('globale_user_supported', false);
			return false;
		}
		return true;
	}

	/**
	 * Add globale_user_supported to Magento register
	 * @param SDK $GlobaleSDK
	 */
	protected function initIsUserSupportedByGlobale(SDK $GlobaleSDK) {

		$UserSupportedByGlobaleResponse = $GlobaleSDK->Browsing()->IsUserSupportedByGlobale();
		if ($UserSupportedByGlobaleResponse->getSuccess()) {
			/**@var $UserSupportedByGlobaleResponse Response\Data */
			$UserSupportedByGlobale = $UserSupportedByGlobaleResponse->getData();
			Mage::register('globale_user_supported', $UserSupportedByGlobale);
		}
	}

	/**
	 * initialize Magento Current Currency from SDK
	 * @param SDK $GlobaleSDK
	 **/
	protected function initCurrentCurrency($GlobaleSDK)
	{

		/**@var $Currency Globale_Browsing_Model_Currency */
		$Currency = Mage::getModel('globale_browsing/currency');
		$Currency->initCurrentCurrency($GlobaleSDK);
	}

	/**
	 * Disable module list output for modules selected in Global-e Settings and if(globale_user_supported == true)
	 */
	protected function disableListedModulesOutput(){
		if(Mage::registry('globale_user_supported')){
			/**@var $Setting Globale_Base_Model_Settings */
			$Setting = Mage::getModel('globale_base/settings');
			$Modules = explode(',', Mage::getStoreConfig($Setting::MODULES_DISABLE_OUTPUT));

			foreach ($Modules as $Module) {
				// Disable its output as well (which was already loaded)
				$OutputPath = "advanced/modules_disable_output/{$Module}";
				if (!Mage::getStoreConfig($OutputPath)) {
					Mage::app()->getStore()->setConfig($OutputPath, true);
				}
			}
		}
	}


}