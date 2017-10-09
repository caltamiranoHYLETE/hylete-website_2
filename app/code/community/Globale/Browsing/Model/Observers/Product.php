<?php

/**
 * @desc Observer for convert price in the product page
 * Class Globale_Browsing_Model_Observers_ConvertPrices
 */
class Globale_Browsing_Model_Observers_Product {


    /**
     * Observer for convert product price
	 * Events => catalog_product_load_after, catalog_controller_product_view, sales_quote_item_set_product
     * @param Varien_Event_Observer $observer
     * @access public
     */
	public function updateProductPrices(Varien_Event_Observer $observer){
		/**@var $ProductModel Globale_Browsing_Model_Product */
		$ProductModel = Mage::getModel('globale_browsing/product');
		$ProductModel->updateProductPrices($observer);
	}


	/**
	 * Observer for convert product price collection
	 * Events => catalog_product_collection_load_after
	 * @param Varien_Event_Observer $observer
	 * @access public
	 */
	public function updateCollectionProductsPrices(Varien_Event_Observer $observer)	{
		/**@var $ProductModel Globale_Browsing_Model_Product */
		$ProductModel = Mage::getModel('globale_browsing/product');
		$ProductModel->updateCollectionProductsPrices($observer);
	}



	############  Catalog RulePrices ######


	/**
	 * Insert Product to Registry for updateCatalogRulePrices usage
	 * Events => sales_quote_item_set_product
	 * @param Varien_Event_Observer $observer
	 */
	public function insertProductToRegistry(Varien_Event_Observer $observer){
		/**@var $ProductModel Globale_Browsing_Model_Product */
		$ProductModel = Mage::getModel('globale_browsing/product');
		$ProductModel->insertProductToRegistry($observer);
	}


	/**
	 * Convert Catalog Rule prices by using the Global-e SDK in cart
	 * Events => globale_catalogRule_getRulePrices
	 * @param Varien_Event_Observer $observer
	 */
	public function updateCatalogRulePrices(Varien_Event_Observer $observer){
		/**@var $ProductModel Globale_Browsing_Model_Product */
		$ProductModel = Mage::getModel('globale_browsing/product');
		$ProductModel->updateCatalogRulePrices($observer);
	}

	/**
	 * Update Product View Config => set prices to zero
	 * Event => catalog_product_view_config
	 * @param Varien_Event_Observer $observer
	 */
	public function updateProductViewConfig(Varien_Event_Observer $observer){

		/**@var $ProductModel Globale_Browsing_Model_Product */
		$ProductModel = Mage::getModel('globale_browsing/product');
		$ProductModel->updateProductViewConfig($observer);
	}

	/**
	 * Beatify price amount of configurable price during calculating
	 * Event ==> catalog_product_type_configurable_price
	 * @param Varien_Event_Observer $observer
	 */
	public function beatifyProductConfigurablePrice(Varien_Event_Observer $observer){
		/**@var $ProductModel Globale_Browsing_Model_Product */
		$ProductModel = Mage::getModel('globale_browsing/product');
		$ProductModel->beatifyProductConfigurablePrice($observer);
	}


}