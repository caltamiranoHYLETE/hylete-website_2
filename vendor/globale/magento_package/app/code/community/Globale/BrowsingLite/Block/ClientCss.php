<?php
class Globale_BrowsingLite_Block_ClientCss extends Mage_Core_Block_Template {

	/**
	 * Build link href for CSS
	 * @return string
	 */
	public function getLinkRel(){

			$MerchantId = Mage::getStoreConfig(Globale_Base_Model_Settings::MERCHANT_ID);
			$BaseUrl = Mage::getStoreConfig(Globale_Base_Model_Settings::GEM_BASE_URL);
			$Href = $BaseUrl.'proxy/css/'.$MerchantId;

			return $Href;
	}

	/**
	 * Add or not to add link to CSS
	 * @return boolean
	 */
	public function useLinkRel(){
		return Mage::helper('core')->isModuleEnabled('Globale_Browsing') == false &&
			Mage::getModel('globale_base/settings')->getEnableGemInclude();
	}

}