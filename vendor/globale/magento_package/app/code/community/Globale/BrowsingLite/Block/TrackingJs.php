<?php
class Globale_BrowsingLite_Block_TrackingJs extends Mage_Core_Block_Template {

	/**
	 * Show Tracking Script
	 * @return bool
	 */
	public function showScript(){

		$TrackingJs = Mage::getModel('globale_base/settings')->getTrackingJs();

		return !empty($TrackingJs) && Mage::getModel('globale_base/settings')->getEnableGemInclude()
			&& Mage::helper('core')->isModuleEnabled('Globale_Browsing') == false;
	}

	/**
	 * Src to tracking script controller
	 * @return string
	 */
	public function getSrc(){
		$FrontName = (string)Mage::getConfig()->getNode('frontend/routers/browsing/args/frontName');
		return Mage::getUrl("{$FrontName}/tracking/get");

	}

}