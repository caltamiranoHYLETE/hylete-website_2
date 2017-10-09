<?php

use GlobalE\SDK;

class Globale_FixedPrices_Model_Observers_Products {

	/**
	 * Event => globale_build_products_request_before
	 * @param Varien_Event_Observer $Observer
	 */
	public function loadProductsFixedPrices(Varien_Event_Observer $Observer){

		if(Mage::registry('globale_user_supported')){
            $Products = $Observer->getProducts();
            /**@var $ProductModel Globale_FixedPrices_Model_Product */
            $ProductModel = Mage::getModel('globale_fixedprices/product');
		    $ProductModel->loadProductsFixedPrices($Products);
		}
	}

	/**
	 * Event => globale_load_products_fixed_price
	 * @param Varien_Event_Observer $Observer
	 */
    public function updateProductFixedPrices(Varien_Event_Observer $Observer){

        if(Mage::registry('globale_user_supported')){
        	/**@var Mage_Catalog_Model_Product $Product */
            $Product = $Observer->getProduct();
            /**@var $ProductModel Globale_FixedPrices_Model_Product */
            $ProductModel = Mage::getModel('globale_fixedprices/product');
            $GlobaleFixedpriceProducts = Mage::registry('globale_fixedprice_products');
            if(!empty($GlobaleFixedpriceProducts[$Product->getSku()])){
                $ProductModel->insertFixedPriceToProduct($Product, $GlobaleFixedpriceProducts[$Product->getSku()]);
            }
        }
    }

}

