<?php
class Globale_BrowsingLite_Helper_Data extends Mage_Core_Helper_Abstract {


	public function getClearCartUrl(){
		//get the frontName for Browsing module from the config.xml
		$FrontName = (string)Mage::getConfig()->getNode('frontend/routers/browsing/args/frontName');
		return Mage::getUrl("{$FrontName}/cart/clear", array('_secure' => true));
	}

}