<?php
	class Windsorcircle_Export_Model_Observer {
		
		public function __construct() {
			
		}

		/**
	     * Saving product type related data
	     *
	     * @return Mage_Catalog_Model_Product
	     */
		public function afterProductSave($observer) {
			$event = $observer->getEvent();
			$product = $event->getProduct();
			$oldProd = Mage::getModel('catalog/product')->load($product->getId())->getOrigData();
			$productId = $product->getId();
			if(isset($oldProd['entity_id']) && $oldProd['entity_id'] !== NULL) {
				$updatedProdFolder = Mage::getBaseDir('media') . DS . 'windsorcircle_export';
				$updatedProd = $updatedProdFolder . DS . 'updated.txt';
				if(!file_exists($updatedProdFolder)) { mkdir($updatedProdFolder); }
				$updatedFile = fopen($updatedProd, 'a');
				fputs($updatedFile,"!".$productId."\n");
				fclose($updatedFile);
			}
		}
		
		/**
	     * Init indexing process after product delete commit
	     *
	     */
	    public function afterProductDelete($observer)
	    {
	    	$event = $observer->getEvent();
	    	$product = $event->getProduct();
	    	$updatedProdFolder = Mage::getBaseDir('media') . DS . 'windsorcircle_export';
			$updatedProd = $updatedProdFolder . DS . 'updated.txt';
			if (!file_exists($updatedProdFolder)) { mkdir($updatedProdFolder); }
			$updatedFile = fopen($updatedProd, 'a');
			fputs($updatedFile,"-".$product->getId()."\n");
			fclose($updatedFile);
	    }
	}
