<?php
abstract class Globale_Browsing_Model_Products_Abstract {

	/**
	 * Change Prices for Additional Product items per product type
	 * @param Mage_Catalog_Model_Product $Product
	 */
	abstract public function changeAdditionalProductPrices(Mage_Catalog_Model_Product $Product);

}