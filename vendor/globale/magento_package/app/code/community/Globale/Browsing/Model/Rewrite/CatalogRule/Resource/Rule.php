<?php
class Globale_Browsing_Model_Rewrite_CatalogRule_Resource_Rule extends Mage_CatalogRule_Model_Resource_Rule {

	public $_RulePrices;



	public function getRulePrices($date, $websiteId, $customerGroupId, $productIds){

		$this->_RulePrices = parent::getRulePrices($date, $websiteId, $customerGroupId, $productIds);

		Mage::dispatchEvent('globale_catalogRule_getRulePrices', array('catalog_rule_resource' => $this));

		return $this->_RulePrices;
	}





}