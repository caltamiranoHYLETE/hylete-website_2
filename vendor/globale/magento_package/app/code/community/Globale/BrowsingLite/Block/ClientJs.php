<?php
class Globale_BrowsingLite_Block_ClientJs extends Mage_Core_Block_Template {

	/**
	 * Load JS function for GEM
	 * @return string
	 */
	public function loadClientJS(){

		if(Mage::helper('core')->isModuleEnabled('Globale_Browsing') == false
			&& Mage::getModel('globale_base/settings')->getEnableGemInclude()) {

			$MerchantId = Mage::getStoreConfig(Globale_Base_Model_Settings::MERCHANT_ID);
			$BaseUrl = Mage::getStoreConfig(Globale_Base_Model_Settings::GEM_BASE_URL);
			$Src = $BaseUrl . 'proxy/get/' . $MerchantId;

			return '(function () { 
                    var s = document.createElement(\'script\'); 
                    s.type = \'text/javascript\'; 
                    s.async = true; 
                    s.src = \'' . $Src . '\'; 
                    document.getElementsByTagName(\'head\')[0].appendChild(s); 
                })();
                ';
		}
		return '<!-- Global-e Magento Browsing Mode is active or include JS setting is off -->';
	}

}